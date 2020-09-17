<?php
/**
 * Class AMP_Srcset_Sanitizer.
 *
 * @package AMP
 */

/**
 * Sanitizes the `srcset` attribute of elements.
 *
 * @internal
 * @since 2.0.2
 */
class AMP_Srcset_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Pattern used used for finding image candidates defined within the `srcset` attribute.
	 * The pattern is adapted from the JS validator. See https://github.com/ampproject/amphtml/blob/5fcb29a41d06867b25ed6aca69b4aeaf96456c8c/validator/js/engine/parse-srcset.js#L72-L81.
	 *
	 * @var string
	 */
	const SRCSET_REGEX_PATTERN = '/\s*(?:,\s*)?(?<url>[^,\s]\S*[^,\s])\s*(?<dimension>[1-9]\d*[wx]|[1-9]\d*\.\d+x|0.\d*[1-9]\d*x)?\s*(?<comma>,)?\s*/';

	/**
	 * Sanitize the HTML contained in the DOMDocument received by the constructor
	 */
	public function sanitize() {
		$attribute_query = $this->dom->xpath->query( '//*/@srcset' );

		if ( 0 === $attribute_query->length ) {
			return;
		}

		foreach ( $attribute_query as $attribute ) {
			/** @var DOMAttr $attribute */
			if ( ! empty( $attribute->value ) ) {
				$this->sanitize_srcset_attribute( $attribute );
			}
		}
	}

	/**
	 * Parses the `srcset` attribute and validates each image candidate defined.
	 *
	 * Validation errors will be raised if the attribute value is malformed or contains image candidates
	 * with the same width or pixel density defined.
	 *
	 * @param DOMAttr $attribute Srcset attribute.
	 */
	private function sanitize_srcset_attribute( DOMAttr $attribute ) {
		$srcset = $attribute->value;

		// Bail and raise a validation error if no image candidates were found or the last matched group does not
		// match the end of the `srcset`.
		if (
			! preg_match_all( self::SRCSET_REGEX_PATTERN, $srcset, $matches )
			||
			end( $matches[0] ) !== substr( $srcset, -strlen( end( $matches[0] ) ) )
		) {

			$validation_error = [
				'code' => AMP_Tag_And_Attribute_Sanitizer::INVALID_ATTR_VALUE,
			];
			$this->remove_invalid_attribute( $attribute->ownerElement, $attribute, $validation_error );
			return;
		}

		$dimension_count = count( $matches['dimension'] );
		$commas_count    = count( array_filter( $matches['comma'] ) );

		// Bail and raise a validation error if the number of dimensions does not match the number of URLs, or there
		// are not enough commas to separate the image candidates.
		if ( count( $matches['url'] ) !== $dimension_count || ( $dimension_count - 1 ) !== $commas_count ) {
			$validation_error = [
				'code' => AMP_Tag_And_Attribute_Sanitizer::INVALID_ATTR_VALUE,
			];
			$this->remove_invalid_attribute( $attribute->ownerElement, $attribute, $validation_error );
			return;
		}

		// Bail if there are no duplicate image candidates.
		// Note: array_flip() is used as a faster alternative to array_unique(). See https://stackoverflow.com/a/8321709/93579.
		if ( count( array_flip( $matches['dimension'] ) ) === $dimension_count ) {
			return;
		}

		$image_candidates     = [];
		$duplicate_dimensions = [];

		foreach ( $matches['dimension'] as $index => $dimension ) {
			if ( empty( trim( $dimension ) ) ) {
				$dimension = '1x';
			}

			// Catch if there are duplicate dimensions that have different URLs. In such cases a validation error will be raised.
			if ( isset( $image_candidates[ $dimension ] ) && $matches['url'][ $index ] !== $image_candidates[ $dimension ] ) {
				$duplicate_dimensions[] = $dimension;
				continue;
			}

			$image_candidates[ $dimension ] = $matches['url'][ $index ];
		}

		// If there are duplicates, raise a validation error and stop short-circuit processing if the error is not removed.
		if ( ! empty( $duplicate_dimensions ) ) {
			$validation_error = [
				'code'                 => AMP_Tag_And_Attribute_Sanitizer::DUPLICATE_DIMENSIONS,
				'duplicate_dimensions' => $duplicate_dimensions,
			];
			if ( ! $this->should_sanitize_validation_error( $validation_error, [ 'node' => $attribute ] ) ) {
				return;
			}
		}

		// Otherwise, output the normalized/validated srcset value.
		$attribute->value = implode(
			', ',
			array_map(
				static function ( $dimension ) use ( $image_candidates ) {
					return "{$image_candidates[ $dimension ]} {$dimension}";
				},
				array_keys( $image_candidates )
			)
		);
	}
}
