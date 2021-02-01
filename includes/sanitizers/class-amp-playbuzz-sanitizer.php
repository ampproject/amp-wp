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
 * @internal
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
	 * Hardcoded height to set for Playbuzz elements.
	 *
	 * @var string
	 *
	 * @since 0.2
	 */
	private static $height = '500';

	/**
	 * Get mapping of HTML selectors to the AMP component selectors which they may be converted into.
	 *
	 * @return array Mapping.
	 */
	public function get_selector_conversion_mapping() {
		return [
			'div.pb_feed' => [ 'amp-playbuzz.pb_feed' ],
		];
	}

	/**
	 * Sanitize the Playbuzz elements from the HTML contained in this instance's Dom\Document.
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

			$new_node = AMP_DOM_Utils::create_node( $this->dom, 'amp-playbuzz', $new_attributes );

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
		$out = [];

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

				case 'data-shares':
					$out['data-share-buttons'] = $value;
					break;

				case 'data-game-info':
				case 'data-comments':
				case 'class':
					$out[ $name ] = $value;
					break;

				default:
					break;
			}
		}

		$out['height'] = self::$height;

		return $out;
	}
}
