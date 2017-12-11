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
	 * Script slug.
	 *
	 * @var string AMP HTML tag to use in place of HTML's <iframe> tag.
	 *
	 * @since 0.2
	 */
	private static $script_slug = 'amp-iframe';

	/**
	 * Script src.
	 *
	 * @var string URL to AMP Project's IFrame element's JavaScript file found at cdn.ampproject.org
	 *
	 * @since 0.2
	 */
	private static $script_src = 'https://cdn.ampproject.org/v0/amp-iframe-0.1.js';

	/**
	 * Default args.
	 *
	 * @var array
	 */
	protected $DEFAULT_ARGS = array(
		'add_placeholder' => false,
	);

	/**
	 * Return one element array containing AMP HTML iframe tag and respective Javascript URL
	 *
	 * HTML tags and Javascript URLs found at cdn.ampproject.org
	 *
	 * @since 0.2
	 *
	 * @return string[] Returns AMP HTML iframe tag as array key and Javascript URL as array value,
	 *                  respectively. Will return an empty array if sanitization has yet to be run
	 *                  or if it did not find any HTML iframe elements to convert to AMP equivalents.
	 */
	public function get_scripts() {
		if ( ! $this->did_convert_elements ) {
			return array();
		}
		return array( self::$script_slug => self::$script_src );
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
			$old_attributes = AMP_DOM_Utils::get_node_attributes_as_assoc_array( $node );

			$new_attributes = $this->filter_attributes( $old_attributes );

			/**
			 * If the src doesn't exist, remove the node. Either it never
			 * existed or was invalidated while filtering attributes above.
			 *
			 * @todo: add a filter to allow for a fallback element in this instance.
			 * @see: https://github.com/ampproject/amphtml/issues/2261
			 */
			if ( empty( $new_attributes['src'] ) ) {
				$node->parentNode->removeChild( $node );
				continue;
			}

			$this->did_convert_elements = true;

			$new_attributes = $this->enforce_fixed_height( $new_attributes );
			$new_attributes = $this->enforce_sizes_attribute( $new_attributes );

			$new_node = AMP_DOM_Utils::create_node( $this->dom, 'amp-iframe', $new_attributes );

			if ( true === $this->args['add_placeholder'] ) {
				$placeholder_node = $this->build_placeholder( $new_attributes );
				$new_node->appendChild( $placeholder_node );
			}

			$parent_node = $node->parentNode;
			if ( 'p' !== strtolower( $parent_node->tagName ) ) {
				$parent_node->replaceChild( $new_node, $node );
			} else {
				// AMP does not like iframes in <p> tags.
				$parent_node->removeChild( $node );
				$parent_node->parentNode->insertBefore( $new_node, $parent_node->nextSibling );

				if ( AMP_DOM_Utils::is_node_empty( $parent_node ) ) {
					$parent_node->parentNode->removeChild( $parent_node );
				}
			}
		}
	}

	/**
	 * "Filter" HTML attributes for <amp-iframe> elements.
	 *
	 * @since 0.2
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
	 *      @type int $frameborder <iframe> `frameborder` attribute - Filter to '0' or '1'; default to '0'
	 *      @type bool $allowfullscreen <iframe> `allowfullscreen` attribute - Convert 'false' to empty string ''
	 *      @type bool $allowtransparency <iframe> `allowtransparency` attribute - Convert 'false' to empty string ''
	 * }
	 * @return array Returns HTML attributes; removes any not specifically declared above from input.
	 */
	private function filter_attributes( $attributes ) {
		$out = array();

		foreach ( $attributes as $name => $value ) {
			switch ( $name ) {
				case 'sandbox':
				case 'class':
				case 'sizes':
					$out[ $name ] = $value;
					break;

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
		$placeholder_node = AMP_DOM_Utils::create_node( $this->dom, 'div', array(
			'placeholder' => '',
			'class' => 'amp-wp-iframe-placeholder',
		) );

		return $placeholder_node;
	}
}
