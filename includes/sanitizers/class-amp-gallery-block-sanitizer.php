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

			$attributes      = AMP_DOM_Utils::get_node_attributes_as_assoc_array( $node );
			$is_amp_lightbox = isset( $attributes['data-amp-lightbox'] ) && true === filter_var( $attributes['data-amp-lightbox'], FILTER_VALIDATE_BOOLEAN );
			$is_amp_carousel = isset( $attributes['data-amp-carousel'] ) && true === filter_var( $attributes['data-amp-carousel'], FILTER_VALIDATE_BOOLEAN );

			// We are only looking for <ul> elements which have amp-carousel / amp-lightbox true.
			if ( ! $is_amp_carousel && ! $is_amp_lightbox ) {
				continue;
			}

			// If lightbox is set, we should add lightbox feature to the gallery images.
			if ( $is_amp_lightbox ) {
				$this->add_lightbox_attributes_to_image_nodes( $node );
				$this->maybe_add_amp_image_lightbox_node();
			}

			// If amp-carousel is not set, nothing else to do here.
			if ( ! $is_amp_carousel ) {
				continue;
			}

			$num_images = 0;

			// If it's not AMP lightbox, look for links first.
			if ( ! $is_amp_lightbox ) {
				$images     = $node->getElementsByTagName( 'a' );
				$num_images = $images->length;
			}

			if ( 0 === $num_images ) {

				// If not linking to anything then look for <amp-img>.
				$images     = $node->getElementsByTagName( 'amp-img' );
				$num_images = $images->length;
			}

			if ( 0 === $num_images ) {
				continue;
			}

			$carousel_height     = $this->get_carousel_height( $node );
			$carousel_attributes = array(
				'height' => $carousel_height,
				'type'   => 'slides',
				'layout' => 'fixed-height',
			);
			$amp_carousel        = AMP_DOM_Utils::create_node( $this->dom, 'amp-carousel', $carousel_attributes );

			for ( $j = $num_images - 1; $j >= 0; $j-- ) {
				$amp_carousel->appendChild( $images->item( $j ) );
			}

			$node->parentNode->replaceChild( $amp_carousel, $node );
		}
		$this->did_convert_elements = true;
	}

	/**
	 * Get carousel height by containing images.
	 *
	 * @param DOMNode $node Node <ul>.
	 * @return int
	 */
	protected function get_carousel_height( $node ) {
		$images     = $node->getElementsByTagName( 'amp-img' );
		$num_images = $images->length;
		$height     = false;
		if ( 0 === $num_images ) {
			return self::FALLBACK_HEIGHT;
		}
		for ( $i = $num_images - 1; $i >= 0; $i-- ) {
			$image        = $images->item( $i );
			$image_height = $image->getAttribute( 'height' );
			if ( ! $image_height || ! is_numeric( $image_height ) ) {
				continue;
			}
			if ( ! $height ) {
				$height = $image_height;
			} elseif ( $height > $image_height ) {
				$height = $image_height;
			}
		}

		return false === $height ? self::FALLBACK_HEIGHT : $height;
	}

	/**
	 * Set lightbox related attributes to <amp-img> within gallery.
	 *
	 * @param DOMNode $node <ul> node.
	 */
	protected function add_lightbox_attributes_to_image_nodes( $node ) {
		$images     = $node->getElementsByTagName( 'amp-img' );
		$num_images = $images->length;
		if ( 0 === $num_images ) {
			return;
		}
		$attributes = array(
			'data-amp-lightbox' => '',
			'on'                => 'tap:' . self::AMP_IMAGE_LIGHTBOX_ID,
			'role'              => 'button',
		);

		for ( $j = $num_images - 1; $j >= 0; $j-- ) {
			$image_node = $images->item( $j );
			foreach ( $attributes as $att => $value ) {
				$image_node->setAttribute( $att, $value );
			}
		}
	}
}
