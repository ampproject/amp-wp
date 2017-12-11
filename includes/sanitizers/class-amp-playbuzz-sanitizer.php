<?php
/**
 * Class AMP_Playbuzz_Sanitizer
 *
 * @package AMP
 */

/**
 * Class AMP_Playbuzz_Sanitizer
 *
 * Converts Playbuzz embed to <amp-playbuzz>
 *
 * @see https://www.playbuzz.com/
 */
class AMP_Playbuzz_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Tag.
	 *
	 * @var string HTML tag to identify and replace with AMP version.
	 * @since 0.2
	 */
	public static $tag = 'div';

	/**
	 * PlayBuzz class.
	 *
	 * @var string CSS class to identify Playbuzz <div> to replace with AMP version.
	 *
	 * @since 0.2
	 */
	public static $pb_class = 'pb_feed';

	/**
	 * Script slug.
	 *
	 * @var string AMP HTML audio tag to use in place of HTML's 'audio' tag.
	 *
	 * @since 0.2
	 */
	private static $script_slug = 'amp-playbuzz';

	/**
	 * Script src.
	 *
	 * @var string URL to AMP Project's Playbuzz element javascript file found at cdn.ampproject.org
	 *
	 * @since 0.2
	 */
	private static $script_src = 'https://cdn.ampproject.org/v0/amp-playbuzz-0.1.js';

	/**
	 * Hardcoded height to set for Playbuzz elements.
	 *
	 * @var string
	 *
	 * @since 0.2
	 */
	private static $height = '500';

	/**
	 * Return one element array containing AMP HTML audio tag and respective Javascript URL
	 *
	 * HTML tags and Javascript URLs found at cdn.ampproject.org
	 *
	 * @since 0.2
	 *
	 * @return string[] Returns AMP Playbuzz tag as array key and Javascript URL as array value,
	 *                  respectively. Will return an empty array if sanitization has yet to be run
	 *                  or if it did not find any HTML Playbuzz elements to convert to AMP equivalents.
	 */
	public function get_scripts() {
		if ( ! $this->did_convert_elements ) {
			return array();
		}
		return array( self::$script_slug => self::$script_src );
	}


	/**
	 * Sanitize the Playbuzz elements from the HTML contained in this instance's DOMDocument.
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
			$node = $nodes->item( $i );

			if ( self::$pb_class !== $node->getAttribute( 'class' ) ) {
				continue;
			}

			$old_attributes = AMP_DOM_Utils::get_node_attributes_as_assoc_array( $node );

			$new_attributes = $this->filter_attributes( $old_attributes );

			if ( ! isset( $new_attributes['data-item'] ) && ! isset( $new_attributes['src'] ) ) {
				continue;
			}

			$new_node = AMP_DOM_Utils::create_node( $this->dom, self::$script_slug, $new_attributes );

			$node->parentNode->replaceChild( $new_node, $node );

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
	 *      @type string $data-item Playbuzz <div> attribute - Pass along if found and not empty.
	 *      @type string $data-game Playbuzz <div> attribute - Assign to its value to $attributes['src'] if found and not empty.
	 *      @type string $data-game-info Playbuzz <div> attribute - Assign to its value to $attributes['data-item-info'] if found.
	 *      @type string $data-shares Playbuzz <div> attribute - Assign to its value to $attributes['data-share-buttons'] if found.
	 *      @type string $data-comments Playbuzz <div> attribute - Pass along if found.
	 *      @type int $height Playbuzz <div> attribute - Set to hardcoded value of 500.
	 * }
	 * @return array Returns HTML attributes; removes any not specifically declared above from input.
	 */
	private function filter_attributes( $attributes ) {
		$out = array();

		foreach ( $attributes as $name => $value ) {
			switch ( $name ) {
				case 'data-item':
					if ( ! empty( $value ) ) {
						$out['data-item'] = $value;
					}
					break;

				case 'data-game':
					if ( ! empty( $value ) ) {
						$out['src'] = $value;
					}
					break;

				case 'data-game-info':
					$out['data-item-info'] = $value;
					break;

				case 'data-shares':
					$out['data-share-buttons'] = $value;
					break;

				case 'data-comments':
					$out['data-comments'] = $value;
					break;

				default:
					break;
			}
		}

		$out['height'] = self::$height;

		return $out;
	}
}
