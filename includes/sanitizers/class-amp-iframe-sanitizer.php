<?php
/**
 * Class AMP_Iframe_Sanitizer
 *
 * @package AMP
 */

/**
 * Class AMP_Iframe_Sanitizer
 *
 * Converts <iframe> tags to <amp-iframe>
 */
class AMP_Iframe_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Value used for height attribute when $attributes['height'] is empty.
	 *
	 * @since 0.2
	 *
	 * @const int
	 */
	const FALLBACK_HEIGHT = 400;

	/**
	 * Default values for sandboxing IFrame.
	 *
	 * @since 0.2
	 *
	 * @const int
	 */
	const SANDBOX_DEFAULTS = 'allow-scripts allow-same-origin';

	/**
	 * Tag.
	 *
	 * @var string HTML <iframe> tag to identify and replace with AMP version.
	 *
	 * @since 0.2
	 */
	public static $tag = 'iframe';

	/**
	 * Default args.
	 *
	 * @var array
	 */
	protected $DEFAULT_ARGS = array(
		'add_placeholder' => false,
	);

	/**
	 * Get mapping of HTML selectors to the AMP component selectors which they may be converted into.
	 *
	 * @return array Mapping.
	 */
	public function get_selector_conversion_mapping() {
		return array(
			'iframe' => array(
				'amp-iframe',
			),
		);
	}

	/**
	 * Sanitize the <iframe> elements from the HTML contained in this instance's DOMDocument.
	 *
	 * @since 0.2
	 */
	public function sanitize() {
		$nodes     = $this->dom->getElementsByTagName( self::$tag );
		$num_nodes = $nodes->length;
		if ( 0 === $num_nodes ) {
			return;
		}

		for ( $i = $num_nodes - 1; $i >= 0; $i-- ) {
			$node           = $nodes->item( $i );
			$normalized_attributes = $this->normalize_attributes( AMP_DOM_Utils::get_node_attributes_as_assoc_array( $node ) );

			/**
			 * If the src doesn't exist, remove the node. Either it never
			 * existed or was invalidated while filtering attributes above.
			 *
			 * @todo: add a filter to allow for a fallback element in this instance.
			 * @see: https://github.com/ampproject/amphtml/issues/2261
			 */
			if ( empty( $normalized_attributes['src'] ) ) {
				$this->remove_invalid_child( $node );
				continue;
			}

			$this->did_convert_elements = true;
			$normalized_attributes      = $this->set_layout( $normalized_attributes );
			if ( empty( $normalized_attributes['layout'] ) && ! empty( $normalized_attributes['width'] ) && ! empty( $normalized_attributes['height'] ) ) {
				$normalized_attributes['layout'] = 'intrinsic';
				$this->add_or_append_attribute( $normalized_attributes, 'class', 'amp-wp-enforced-sizes' );
			}

			$new_node = AMP_DOM_Utils::create_node( $this->dom, 'amp-iframe', $normalized_attributes );

			if ( true === $this->args['add_placeholder'] ) {
				$placeholder_node = $this->build_placeholder( $normalized_attributes );
				$new_node->appendChild( $placeholder_node );
			}

			$node->parentNode->replaceChild( $new_node, $node );
		}
	}

	/**
	 * Normalize HTML attributes for <amp-iframe> elements.
	 *
	 *
	 * @param string[] $attributes {
	 *      Attributes.
	 *
	 *      @type string $src IFrame URL - Empty if HTTPS required per $this->args['require_https_src']
	 *      @type int $width <iframe> width attribute - Set to numeric value if px or %
	 *      @type int $height <iframe> width attribute - Set to numeric value if px or %
	 *      @type string $sandbox <iframe> `sandbox` attribute - Pass along if found; default to value of self::SANDBOX_DEFAULTS
	 *      @type string $class <iframe> `class` attribute - Pass along if found
	 *      @type string $sizes <iframe> `sizes` attribute - Pass along if found
	 *      @type string $id <iframe> `id` attribute - Pass along if found
	 *      @type int $frameborder <iframe> `frameborder` attribute - Filter to '0' or '1'; default to '0'
	 *      @type bool $allowfullscreen <iframe> `allowfullscreen` attribute - Convert 'false' to empty string ''
	 *      @type bool $allowtransparency <iframe> `allowtransparency` attribute - Convert 'false' to empty string ''
	 * }
	 * @return array Returns HTML attributes; normalizes src, dimensions, frameborder, sandox, allowtransparency and allowfullscreen
	 */
	private function normalize_attributes( $attributes ) {
		$out = array();

		foreach ( $attributes as $name => $value ) {
			switch ( $name ) {
				case 'src':
					$out[ $name ] = $this->maybe_enforce_https_src( $value, true );
					break;

				case 'width':
				case 'height':
					$out[ $name ] = $this->sanitize_dimension( $value, $name );
					break;

				case 'frameborder':
					if ( '0' !== $value && '1' !== $value ) {
						$value = '0';
					}
					$out[ $name ] = $value;
					break;

				case 'allowfullscreen':
				case 'allowtransparency':
					if ( 'false' !== $value ) {
						$out[ $name ] = '';
					}
					break;

				default:
					$out[ $name ] = $value;
					break;
			}
		}

		if ( ! isset( $out['sandbox'] ) ) {
			$out['sandbox'] = self::SANDBOX_DEFAULTS;
		}

		return $out;
	}

	/**
	 * Builds a DOMElement to use as a placeholder for an <iframe>.
	 *
	 * Important: The element returned must not be block-level (e.g. div) as the PHP DOM parser
	 * will move it out from inside any containing paragraph. So this is why a span is used.
	 *
	 * @since 0.2
	 *
	 * @param string[] $parent_attributes {
	 *      Attributes.
	 *
	 *      @type string $placeholder AMP HTML <amp-iframe> `placeholder` attribute; default to 'amp-wp-iframe-placeholder'
	 *      @type string $class AMP HTML <amp-iframe> `class` attribute; default to 'amp-wp-iframe-placeholder'
	 * }
	 * @return DOMElement|false
	 */
	private function build_placeholder( $parent_attributes ) {
		$placeholder_node = AMP_DOM_Utils::create_node( $this->dom, 'span', array(
			'placeholder' => '',
			'class'       => 'amp-wp-iframe-placeholder',
		) );

		return $placeholder_node;
	}

}
