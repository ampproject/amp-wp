<?php
/**
 * Class CaptionedSlide
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Component;

use DOMElement;

/**
 * Class CaptionedSlide
 *
 * @internal
 * @since 1.5.0
 */
final class CaptionedSlide implements HasCaption {

	/**
	 * The slide node, possibly an image.
	 *
	 * @var DOMElement
	 */
	private $slide_element;

	/**
	 * The caption node.
	 *
	 * @var DOMElement
	 */
	private $caption_element;

	/**
	 * Constructs the class.
	 *
	 * @param DOMElement $slide_element   The slide node, possibly an image.
	 * @param DOMElement $caption_element The caption element.
	 */
	public function __construct( DOMElement $slide_element, DOMElement $caption_element ) {
		$this->slide_element   = $slide_element;
		$this->caption_element = $caption_element;
	}

	/**
	 * Gets the caption element.
	 *
	 * @return DOMElement
	 */
	public function get_caption_element() {
		return $this->caption_element;
	}

	/**
	 * Gets the slide element.
	 *
	 * @return DOMElement
	 */
	public function get_slide_element() {
		return $this->slide_element;
	}
}
