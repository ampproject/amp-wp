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
	 * @since 1.0
	 *
	 * @var string Ul tag to identify wrapper around gallery block.
	 */
	public static $tag = 'ul';

	/**
	 * Expected class of the wrapper around the gallery block.
	 *
	 * @since 1.0
	 *
	 * @var string
	 */
	public static $class = 'wp-block-gallery';

	/**
	 * Array of flags used to control sanitization.
	 *
	 * @var array {
	 *      @type int  $content_max_width Max width of content.
	 *      @type bool $carousel_required Whether carousels are required. This is used when amp theme support is not present, for back-compat.
	 * }
	 */
	protected $args;

	/**
	 * Default args.
	 *
	 * @var array
	 */
	protected $DEFAULT_ARGS = array(
		'carousel_required' => false,
	);

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

			// We're looking for <ul> elements that have at least one child and the proper class.
			if ( 0 === count( $node->childNodes ) || false === strpos( $node->getAttribute( 'class' ), self::$class ) ) {
				continue;
			}

			$attributes      = AMP_DOM_Utils::get_node_attributes_as_assoc_array( $node );
			$is_amp_lightbox = isset( $attributes['data-amp-lightbox'] ) && true === filter_var( $attributes['data-amp-lightbox'], FILTER_VALIDATE_BOOLEAN );
			$is_amp_carousel = ! empty( $this->args['carousel_required'] ) || ( isset( $attributes['data-amp-carousel'] ) && true === filter_var( $attributes['data-amp-carousel'], FILTER_VALIDATE_BOOLEAN ) );

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

			$images = array();

			// If it's not AMP lightbox, look for links first.
			if ( ! $is_amp_lightbox ) {
				foreach ( $node->getElementsByTagName( 'a' ) as $element ) {
					$images[] = $element;
				}
			}

			// If not linking to anything then look for <amp-img>.
			if ( empty( $images ) ) {
				foreach ( $node->getElementsByTagName( 'amp-img' ) as $element ) {
					$images[] = $element;
				}
			}

			// Skip if no images found.
			if ( empty( $images ) ) {
				continue;
			}

			$amp_carousel = AMP_DOM_Utils::create_node(
				$this->dom,
				'amp-carousel',
				array(
					'height' => $this->get_carousel_height( $node ),
					'type'   => 'slides',
					'layout' => 'fixed-height',
				)
			);
			foreach ( $images as $image ) {
				$amp_carousel->appendChild( $image );
			}

			$node->parentNode->replaceChild( $amp_carousel, $node );
		}
		$this->did_convert_elements = true;
	}

	/**
	 * Get carousel height by containing images.
	 *
	 * @param DOMElement $element The UL element.
	 * @return int Height.
	 */
	protected function get_carousel_height( $element ) {
		$images     = $element->getElementsByTagName( 'amp-img' );
		$num_images = $images->length;
		$max_height = 0;
		$max_width  = 0;
		if ( 0 === $num_images ) {
			return self::FALLBACK_HEIGHT;
		}
		foreach ( $images as $image ) {
			/**
			 * Image.
			 *
			 * @var DOMElement $image
			 */
			$image_height = $image->getAttribute( 'height' );
			if ( is_numeric( $image_height ) ) {
				$max_height = max( $max_height, $image_height );
			}
			$image_width = $image->getAttribute( 'height' );
			if ( is_numeric( $image_width ) ) {
				$max_width = max( $max_width, $image_width );
			}
		}

		if ( ! empty( $this->args['content_max_width'] ) && $max_height > 0 && $max_width > $this->args['content_max_width'] ) {
			$max_height = ( $max_width * $this->args['content_max_width'] ) / $max_height;
		}

		return ! $max_height ? self::FALLBACK_HEIGHT : $max_height;
	}

	/**
	 * Set lightbox related attributes to <amp-img> within gallery.
	 *
	 * @param DOMElement $element The UL element.
	 */
	protected function add_lightbox_attributes_to_image_nodes( $element ) {
		$images     = $element->getElementsByTagName( 'amp-img' );
		$num_images = $images->length;
		if ( 0 === $num_images ) {
			return;
		}
		$attributes = array(
			'data-amp-lightbox' => '',
			'on'                => 'tap:' . self::AMP_IMAGE_LIGHTBOX_ID,
			'role'              => 'button',
			'tabindex'          => 0,
		);

		for ( $j = $num_images - 1; $j >= 0; $j-- ) {
			$image_node = $images->item( $j );
			foreach ( $attributes as $att => $value ) {
				$image_node->setAttribute( $att, $value );
			}
		}
	}
}
