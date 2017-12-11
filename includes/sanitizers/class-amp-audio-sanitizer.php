<?php
/**
 * Class AMP_Audio_Sanitizer
 *
 * @package AMP
 */

/**
 * Class AMP_Audio_Sanitizer
 *
 * Converts <audio> tags to <amp-audio>
 */
class AMP_Audio_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Tag.
	 *
	 * @var string HTML audio tag to identify and replace with AMP version.
	 * @since 0.2
	 */
	public static $tag = 'audio';

	/**
	 * Script slug.
	 *
	 * @var string AMP HTML audio tag to use in place of HTML's 'audio' tag.
	 *
	 * @since 0.2
	 */
	private static $script_slug = 'amp-audio';

	/**
	 * Script src.
	 *
	 * @var string URL to AMP Project's Audio element javascript file found at cdn.ampproject.org
	 *
	 * @since 0.2
	 */
	private static $script_src = 'https://cdn.ampproject.org/v0/amp-audio-0.1.js';

	/**
	 * Return one element array containing AMP HTML audio tag and respective Javascript URL
	 *
	 * HTML tags and Javascript URLs found at cdn.ampproject.org
	 *
	 * @since 0.2
	 *
	 * @return string[] Returns AMP HTML audio tag as array key and Javascript URL as array value,
	 *                  respectively. Will return an empty array if sanitization has yet to be run
	 *                  or if it did not find any HTML audio elements to convert to AMP equivalents.
	 */
	public function get_scripts() {
		if ( ! $this->did_convert_elements ) {
			return array();
		}
		return array( self::$script_slug => self::$script_src );
	}

	/**
	 * Sanitize the <audio> elements from the HTML contained in this instance's DOMDocument.
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

			$new_node = AMP_DOM_Utils::create_node( $this->dom, 'amp-audio', $new_attributes );

			foreach ( $node->childNodes as $child_node ) {

				/**
				 * Child node.
				 *
				 * @todo: Fix when `source` has no closing tag as DOMDocument does not handle well.
				 *
				 * @var DOMNode $child_node
				 */

				$new_child_node = $child_node->cloneNode( true );
				if ( ! $new_child_node instanceof DOMElement ) {
					continue;
				}

				$old_child_attributes = AMP_DOM_Utils::get_node_attributes_as_assoc_array( $new_child_node );
				$new_child_attributes = $this->filter_attributes( $old_child_attributes );

				if ( empty( $new_child_attributes['src'] ) ) {
					continue;
				}
				if ( 'source' !== $new_child_node->tagName ) {
					continue;
				}

				// The textContent is invalid for `source` nodes.
				$new_child_node->textContent = null;

				// Only append source tags with a valid src attribute.
				$new_node->appendChild( $new_child_node );

			}

			/**
			 * If the node has at least one valid source, replace the old node with it.
			 * Otherwise, just remove the node.
			 *
			 * @todo: Add a fallback handler.
			 * @see: https://github.com/ampproject/amphtml/issues/2261
			 */
			if ( 0 === $new_node->childNodes->length && empty( $new_attributes['src'] ) ) {
				$node->parentNode->removeChild( $node );
			} else {
				$node->parentNode->replaceChild( $new_node, $node );
			}

			$this->did_convert_elements = true;
		}
	}

	/**
	 * "Filter" HTML attributes for <amp-audio> elements.
	 *
	 * @since 0.2
	 *
	 * @param string[] $attributes {
	 *      Attributes.
	 *
	 *      @type string $src Audio URL - Empty if HTTPS required per $this->args['require_https_src']
	 *      @type int $width <audio> attribute - Set to numeric value if px or %
	 *      @type int $height <audio> attribute - Set to numeric value if px or %
	 *      @type string $class <audio> attribute - Pass along if found
	 *      @type bool $loop <audio> attribute - Convert 'false' to empty string ''
	 *      @type bool $muted <audio> attribute - Convert 'false' to empty string ''
	 *      @type bool $autoplay <audio> attribute - Convert 'false' to empty string ''
	 * }
	 * @return array Returns HTML attributes; removes any not specifically declared above from input.
	 */
	private function filter_attributes( $attributes ) {
		$out = array();

		foreach ( $attributes as $name => $value ) {
			switch ( $name ) {
				case 'src':
					$out[ $name ] = $this->maybe_enforce_https_src( $value );
					break;

				case 'width':
				case 'height':
					$out[ $name ] = $this->sanitize_dimension( $value, $name );
					break;

				case 'class':
					$out[ $name ] = $value;
					break;
				case 'loop':
				case 'muted':
				case 'autoplay':
					if ( 'false' !== $value ) {
						$out[ $name ] = '';
					}
					break;

				default:
					break;
			}
		}

		return $out;
	}
}
