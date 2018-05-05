<?php
/**
 * Class AMP_Gallery_Block_Sanitizer.
 *
 * @package AMP
 */

/**
 * Class AMP_Gallery_Block_Sanitizer
 *
 * Modifies gallery block to match the block's AMP-specific configuration.
 */
class AMP_Gallery_Block_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Value used for width of amp-carousel.
	 *
	 * @since 1.0
	 *
	 * @const int
	 */
	const FALLBACK_WIDTH = 600;

	/**
	 * Value used for height of amp-carousel.
	 *
	 * @since 1.0
	 *
	 * @const int
	 */
	const FALLBACK_HEIGHT = 480;

	/**
	 * Tag.
	 *
	 * @var string Ul tag to identify wrapper around gallery block.
	 * @since 1.0
	 */
	public static $tag = 'ul';

	/**
	 * Sanitize the gallery block contained by <ul> element where necessary.
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

			// We're looking for <ul> elements that at least one child.
			if ( 0 === count( $node->childNodes ) ) {
				continue;
			}

			$attributes = AMP_DOM_Utils::get_node_attributes_as_assoc_array( $node );

			// We are only looking for <ul> elements which have amp-carousel as class.
			if ( ! isset( $attributes['class'] ) || false === strpos( $attributes['class'], 'amp-carousel' ) ) {
				continue;
			}

			$images     = $node->getElementsByTagName( 'a' );
			$num_images = $images->length;

			if ( 0 === $num_images ) {
				continue;
			}

			$attributes   = array(
				'width'  => self::FALLBACK_WIDTH,
				'height' => self::FALLBACK_HEIGHT,
				'type'   => 'slides',
				'layout' => 'responsive',
			);
			$amp_carousel = AMP_DOM_Utils::create_node( $this->dom, 'amp-carousel', $attributes );

			for ( $j = $num_images - 1; $j >= 0; $j-- ) {
				$amp_carousel->appendChild( $images->item( $j ) );
			}

			$node->parentNode->replaceChild( $amp_carousel, $node );
			$this->did_convert_elements = true;
		}
	}
}
