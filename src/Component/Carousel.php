<?php
/**
 * Class Carousel.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Component;

use AmpProject\Attribute;
use AmpProject\Dom\Document;
use AmpProject\AmpWP\Dom\ElementList;
use AmpProject\Tag;
use DOMElement;
use AMP_DOM_Utils;

/**
 * Class Carousel
 *
 * Gets the markup for an <amp-carousel>.
 *
 * @internal
 * @since 1.5.0
 */
final class Carousel {

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
	 * @var Document
	 */
	private $dom;

	/**
	 * The slides to add to the carousel, possibly images.
	 *
	 * @var ElementList
	 */
	private $slides;

	/**
	 * Instantiates the class.
	 *
	 * @param Document    $dom    The dom to use to create a carousel.
	 * @param ElementList $slides The slides from which to create a carousel.
	 */
	public function __construct( Document $dom, ElementList $slides ) {
		$this->dom    = $dom;
		$this->slides = $slides;
	}

	/**
	 * Gets the carousel element.
	 *
	 * @return DOMElement An <amp-carousel> with the slides.
	 */
	public function get_dom_element() {
		list( $width, $height ) = $this->get_dimensions();
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

		foreach ( $this->slides as $slide ) {
			$slide_node      = $slide instanceof CaptionedSlide ? $slide->get_slide_element() : $slide;
			$caption_element = $slide instanceof HasCaption ? $slide->get_caption_element() : null;
			$slide_container = AMP_DOM_Utils::create_node(
				$this->dom,
				Tag::FIGURE, // This cannot be a <div> because if the gallery is inside of a <p>, then the DOM will break.
				[ 'class' => 'slide' ]
			);

			// Ensure an image fills the entire <amp-carousel>, so the possible caption looks right.
			if ( $this->is_image_element( $slide_node ) ) {
				$slide_node->setAttribute( 'layout', 'fill' );
				$slide_node->setAttribute( 'object-fit', 'cover' );
			} elseif ( $slide_node->firstChild instanceof DOMElement && $this->is_image_element( $slide_node->firstChild ) ) {
				// If the <amp-img> is wrapped in an <a>.
				$slide_node->firstChild->setAttribute( 'layout', 'fill' );
				$slide_node->firstChild->setAttribute( 'object-fit', 'cover' );
			}

			$slide_container->appendChild( $slide_node );

			// If there's a caption, append it to the slide.
			if ( null !== $caption_element ) {
				// If the caption is not a <figcaption>, wrap it in one.
				if ( Tag::FIGCAPTION !== $caption_element->nodeName ) {
					$caption_content = $caption_element;
					$caption_element = AMP_DOM_Utils::create_node( $this->dom, Tag::FIGCAPTION, [] );
					$caption_element->appendChild( $caption_content );
				}

				$has_caption_class = AMP_DOM_Utils::has_class( $caption_element, 'amp-wp-gallery-caption' );

				/** @var DOMElement $caption_element */
				if ( ! $has_caption_class ) {
					$caption_element->setAttribute( Attribute::CLASS_, 'amp-wp-gallery-caption' );
				}

				$slide_container->appendChild( $caption_element );
			}

			$amp_carousel->appendChild( $slide_container );
		}

		return $amp_carousel;
	}

	/**
	 * Gets the carousel's width and height, based on its elements.
	 *
	 * This will return the width and height of the slide (possibly image) with the widest aspect ratio,
	 * not necessarily that with the biggest absolute width.
	 *
	 * @return array {
	 *     The carousel dimensions.
	 *
	 *     @type int $width  The width of the carousel, at index 0.
	 *     @type int $height The height of the carousel, at index 1.
	 * }
	 */
	private function get_dimensions() {
		if ( 0 === count( $this->slides ) ) {
			return [ self::FALLBACK_WIDTH, self::FALLBACK_HEIGHT ];
		}

		$max_aspect_ratio = 0;
		$carousel_width   = 0;
		$carousel_height  = 0;

		foreach ( $this->slides as $slide ) {
			$slide_node = $slide instanceof CaptionedSlide ? $slide->get_slide_element() : $slide;
			// Account for an <amp-img> that's wrapped in an <a>.
			if ( ! $this->is_image_element( $slide_node ) && $slide_node->firstChild instanceof DOMElement && $this->is_image_element( $slide_node->firstChild ) ) {
				$slide_node = $slide_node->firstChild;
			}

			if ( ! is_numeric( $slide_node->getAttribute( 'width' ) ) || ! is_numeric( $slide_node->getAttribute( 'height' ) ) ) {
				continue;
			}

			$width  = (float) $slide_node->getAttribute( 'width' );
			$height = (float) $slide_node->getAttribute( 'height' );

			if ( empty( $width ) || empty( $height ) ) {
				continue;
			}

			$this_aspect_ratio = $width / $height;
			if ( $this_aspect_ratio > $max_aspect_ratio ) {
				$max_aspect_ratio = $this_aspect_ratio;
				$carousel_width   = $width;
				$carousel_height  = $height;
			}
		}

		if ( empty( $carousel_width ) && empty( $carousel_height ) ) {
			return [ self::FALLBACK_WIDTH, self::FALLBACK_HEIGHT ];
		}

		return [ $carousel_width, $carousel_height ];
	}

	/**
	 * Determine whether an element is an image (either an <amp-img> or an <img>).
	 *
	 * @param DOMElement $element Element.
	 * @return bool If it is an image.
	 */
	private function is_image_element( DOMElement $element ) {
		return 'amp-img' === $element->tagName || 'img' === $element->tagName;
	}
}
