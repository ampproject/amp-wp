<?php
/**
 * Class AMP_Carousel.
 *
 * @package AMP
 */

/**
 * Class AMP_Carousel
 *
 * Gets the markup for an <amp-carousel>.
 *
 * @since 1.4.1
 */
class AMP_Carousel {

	/**
	 * Value used for width of amp-carousel.
	 *
	 * @var int
	 */
	const FALLBACK_WIDTH = 600;

	/**
	 * Value used for height of amp-carousel.
	 *
	 * @var int
	 */
	const FALLBACK_HEIGHT = 480;

	/**
	 * An object representation of the DOM.
	 *
	 * @var DOMDocument
	 */
	public $dom;

	/**
	 * Instantiates the class.
	 *
	 * @param DOMDocument $dom The dom to use to create a carousel.
	 */
	public function __construct( $dom ) {
		$this->dom = $dom;
	}

	/**
	 * Creates and gets an <amp-carousel> with the given images and captions.
	 *
	 * @param array[] $images_and_captions An array of arrays, with the image and its caption (if any).
	 * @return DOMElement A representation of the <amp-carousel>.
	 */
	public function create_and_get( $images_and_captions ) {
		$images = [];
		foreach ( $images_and_captions as [ $image, $caption ] ) {
			$images[] = $image;
		}

		list( $width, $height ) = $this->get_carousel_dimensions( $images );
		$amp_carousel           = AMP_DOM_Utils::create_node(
			$this->dom,
			'amp-carousel',
			[
				'width'  => $width,
				'height' => $height,
				'type'   => 'slides',
				'layout' => 'responsive',
			]
		);

		foreach ( $images_and_captions as [ $image, $caption ] ) {
			$slide = AMP_DOM_Utils::create_node(
				$this->dom,
				'div',
				[ 'class' => 'slide' ]
			);

			// Ensure the image fills the entire <amp-carousel>, so the possible caption looks right.
			if ( 'amp-img' === $image->tagName ) {
				$image->setAttribute( 'layout', 'fill' );
				$image->setAttribute( 'object-fit', 'cover' );
			} elseif ( isset( $image->firstChild->tagName ) && 'amp-img' === $image->firstChild->tagName ) {
				// If the <amp-img> is wrapped in an <a>.
				$image->firstChild->setAttribute( 'layout', 'fill' );
				$image->firstChild->setAttribute( 'object-fit', 'cover' );
			}

			$slide->appendChild( $image );

			// If there's a caption, wrap it in a <div> and <span>, and append it to the slide.
			if ( $caption ) {
				$caption_wrapper = AMP_DOM_Utils::create_node(
					$this->dom,
					'div',
					[ 'class' => 'amp-wp-gallery-caption' ]
				);
				$caption_span    = AMP_DOM_Utils::create_node( $this->dom, 'span', [] );
				$text_node       = $this->dom->createTextNode( $caption );

				$caption_span->appendChild( $text_node );
				$caption_wrapper->appendChild( $caption_span );
				$slide->appendChild( $caption_wrapper );
			}

			$amp_carousel->appendChild( $slide );
		}

		return $amp_carousel;
	}

	/**
	 * Gets the carousel height by the containing images.
	 *
	 * @param array[] $images The images and captions to get the dimensions from.
	 * @return array {
	 *     Dimensions.
	 *
	 *     @type int $width  Width.
	 *     @type int $height Height.
	 * }
	 */
	public function get_carousel_dimensions( $images ) {
		$max_aspect_ratio = 0;
		$carousel_width   = 0;
		$carousel_height  = 0;

		if ( 0 === count( $images ) ) {
			return [ self::FALLBACK_WIDTH, self::FALLBACK_HEIGHT ];
		}

		foreach ( $images as $image ) {
			// Account for an <amp-img> that's wrapped in an <a>.
			if ( 'amp-img' !== $image->tagName && isset( $image->firstChild->tagName ) && 'amp-img' === $image->firstChild->tagName ) {
				$image = $image->firstChild;
			}

			if ( ! is_numeric( $image->getAttribute( 'width' ) ) || ! is_numeric( $image->getAttribute( 'height' ) ) ) {
				continue;
			}
			$width  = (float) $image->getAttribute( 'width' );
			$height = (float) $image->getAttribute( 'height' );

			$this_aspect_ratio = $width / $height;
			if ( $this_aspect_ratio > $max_aspect_ratio ) {
				$max_aspect_ratio = $this_aspect_ratio;
				$carousel_width   = $width;
				$carousel_height  = $height;
			}
		}

		return [ $carousel_width, $carousel_height ];
	}
}
