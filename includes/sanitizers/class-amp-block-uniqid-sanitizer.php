<?php
/**
 * Class AMP_Block_Uniqid_Sanitizer.
 *
 * @package AMP
 */

use AmpProject\Dom\Element;
use AmpProject\Html\Attribute;
use AmpProject\Html\Tag;
use AmpProject\Dom\Document;

/**
 * Work around use of uniqid() in blocks which breaks parsed CSS caching.
 *
 * @link https://github.com/ampproject/amp-wp/issues/6925
 * @link https://github.com/WordPress/gutenberg/issues/38889
 *
 * @since 2.2.2
 * @internal
 */
class AMP_Block_Uniqid_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Prefixes of class names that should be transformed.
	 *
	 * @var string[]
	 */
	const KEY_PREFIXES = [
		'wp-container-',
		'wp-duotone-',
		'wp-duotone-filter-',
		'wp-elements-',
	];

	/**
	 * Class name pattern.
	 *
	 * @var string
	 */
	private $key_pattern;

	/**
	 * The mapping between uniqid- and wp_unique_id-based class names.
	 *
	 * @var array
	 */
	private $key_mapping = [];

	/**
	 * @param Document $dom  DOM.
	 * @param array    $args Args.
	 */
	public function __construct( $dom, $args = [] ) {
		parent::__construct( $dom, $args );

		$this->key_pattern = sprintf(
			'/\b(?P<prefix>%s)(?P<uniqid>[0-9a-f]{13})\b/',
			implode(
				'|',
				self::KEY_PREFIXES
			)
		);
	}

	/**
	 * Sanitize.
	 */
	public function sanitize() {
		$elements = $this->dom->xpath->query(
			sprintf(
				'//*[ %s ]',
				implode(
					' or ',
					array_map(
						static function ( $class_name_prefix ) {
							return sprintf(
								'contains( @class, "%s" )',
								$class_name_prefix
							);
						},
						self::KEY_PREFIXES
					)
				)
			)
		);

		$replaced_count = 0;
		foreach ( $elements as $element ) {
			if ( $this->transform_element_with_class_attribute( $element ) ) {
				$replaced_count++;
			}
		}

		if ( $replaced_count > 0 ) {
			$this->transform_styles();
		}
	}

	/**
	 * Transform element with class.
	 *
	 * @param Element $element Element.
	 */
	public function transform_element_with_class_attribute( Element $element ) {
		$class_name = $element->getAttribute( Attribute::CLASS_ );

		$count = 0;

		$new_class_name = preg_replace_callback(
			$this->key_pattern,
			function ( $matches ) {
				$old_key = $matches[0];

				if ( ! isset( $this->key_mapping[ $old_key ] ) ) {
					$this->key_mapping[ $old_key ] = self::unique_id( $matches['prefix'] );
				}
				$new_key = $this->key_mapping[ $old_key ];

				if ( in_array( $matches['prefix'], [ 'wp-duotone-', 'wp-duotone-filter-' ], true ) ) {
					$this->transform_duotone_filter( $old_key, $new_key );
				}

				return $new_key;
			},
			$class_name,
			-1,
			$count
		);
		if ( 0 === $count ) {
			return false;
		} else {
			$element->setAttribute( Attribute::CLASS_, $new_class_name );
			return true;
		}
	}

	/**
	 * Transform duotone filter by updating its ID.
	 *
	 * @param string $old_key Old identifier.
	 * @param string $new_key New identifier.
	 *
	 * @return void
	 */
	public function transform_duotone_filter( $old_key, $new_key ) {
		$svg_filter = $this->dom->getElementById( $old_key );
		if ( $svg_filter instanceof Element && Tag::FILTER === $svg_filter->tagName ) {
			$svg_filter->setAttribute( Attribute::ID, $new_key );
		}
	}

	/**
	 * Transform styles.
	 */
	public function transform_styles() {
		$styles = $this->dom->xpath->query(
			sprintf(
				'//style[ %s ]',
				implode(
					' or ',
					array_map(
						static function ( $key_prefix ) {
							return sprintf(
								'contains( text(), "%s" )',
								$key_prefix
							);
						},
						self::KEY_PREFIXES
					)
				)
			)
		);

		foreach ( $styles as $style ) {
			$style->textContent = str_replace(
				array_keys( $this->key_mapping ),
				array_values( $this->key_mapping ),
				$style->textContent
			);
		}
	}

	/**
	 * Gets unique ID.
	 *
	 * This is a polyfill for WordPress <5.0.3.
	 *
	 * @see wp_unique_id()
	 *
	 * @param string $prefix Prefix for the returned ID.
	 * @return string Unique ID.
	 */
	private static function unique_id( $prefix = '' ) {
		if ( function_exists( 'wp_unique_id' ) ) {
			return wp_unique_id( $prefix );
		} else {
			// @codeCoverageIgnoreStart
			static $id_counter = 0;
			return $prefix . (string) ++$id_counter;
			// @codeCoverageIgnoreEnd
		}
	}
}
