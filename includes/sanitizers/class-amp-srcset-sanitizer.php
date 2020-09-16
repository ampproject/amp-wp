<?php
/**
 * Class AMP_Srcset_Sanitizer.
 *
 * @package AMP
 */

/**
 * Class AMP_Srcset_Sanitizer
 *
 * Sanitizes the `srcset` attribute of elements.
 *
 * @internal
 * @since 2.0.2
 */
class AMP_Srcset_Sanitizer extends AMP_Base_Sanitizer {

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

		$attr_rules = AMP_Allowed_Tags_Generated::get_allowed_tag( $attribute->ownerElement->nodeName );
		$attr_spec  = isset( $attr_rules[ AMP_Rule_Spec::ATTR_SPEC_LIST ] ) ? $attr_rules[ AMP_Rule_Spec::ATTR_SPEC_LIST ] : [];

		// Regex below is adapted from the JS validator. See https://github.com/ampproject/amphtml/blob/5fcb29a41d06867b25ed6aca69b4aeaf96456c8c/validator/js/engine/parse-srcset.js#L72-L81.
		$matched = preg_match_all( '/\s*(?:,\s*)?(?<url>[^,\s]\S*[^,\s])\s*(?<dimension>[\d]+.?[\d]*[wx])?\s*(?:(?<comma>,)\s*)?/', $srcset, $matches );

		if ( ! $matched ) {
			// Bail and raise a validation error if no image candidates were found.
			$validation_error = [
				'code' => AMP_Tag_And_Attribute_Sanitizer::INVALID_ATTR_VALUE,
			];

			if ( $this->remove_invalid_attribute( $attribute->ownerElement, $attribute, $validation_error, $attr_spec ) ) {
				return;
			}
		}

		$dimension_count = count( $matches['dimension'] );
		$commas_count    = count(
			array_filter(
				$matches['comma'],
				static function ( $comma ) {
					return ',' === trim( $comma );
				}
			)
		);

		// Bail and raise a validation error if the number of dimensions does not match the number os URLs, or there
		// are not enough commas to separate the image candidates.
		if ( count( $matches['url'] ) !== $dimension_count || ( $dimension_count - 1 ) !== $commas_count ) {
			$validation_error = [
				'code' => AMP_Tag_And_Attribute_Sanitizer::INVALID_ATTR_VALUE,
			];

			if ( $this->remove_invalid_attribute( $attribute->ownerElement, $attribute, $validation_error, $attr_spec ) ) {
				return;
			}
		}

		// Bail if there are no duplicate image candidates.
		// Note: array_flip() is used as a faster alternative to array_unique(). See https://stackoverflow.com/a/8321709/93579.
		if ( count( array_flip( $matches['dimension'] ) ) === $dimension_count ) {
			return;
		}

		$image_candidates = [];

		foreach ( $matches['dimension'] as $index => $dimension ) {
			if ( empty( trim( $dimension ) ) ) {
				$dimension = '1x';
			}

			if ( isset( $image_candidates[ $dimension ] ) ) {
				// Bail if there are duplicate dimensions that have different URLs. In such cases a validation
				// error will be raised.
				if ( $matches['url'][ $index ] !== $image_candidates[ $dimension ] ) {
					$validation_error = [
						'code'                => AMP_Tag_And_Attribute_Sanitizer::DUPLICATE_DIMENSION,
						'duplicate_dimension' => $dimension,
					];

					if ( $this->remove_invalid_attribute( $attribute->ownerElement, $attribute, $validation_error, $attr_spec ) ) {
						return;
					}
				}

				continue;
			}

			$image_candidates[ $dimension ] = $matches['url'][ $index ];
		}

		// Reform the srcset with the filtered image candidates.
		$new_srcset = implode(
			', ',
			array_map(
				static function ( $dimension ) use ( $image_candidates ) {
					return "{$image_candidates[ $dimension ]} {$dimension}";
				},
				array_keys( $image_candidates )
			)
		);

		$attribute->value = $new_srcset;
	}
}
