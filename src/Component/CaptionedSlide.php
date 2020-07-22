<?php
/**
 * Class CaptionedSlide
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Component;

use DOMElement;
use DOMNode;

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
	 * @var DOMNode
	 */
	private $slide_node;

	/**
	 * The caption node.
	 *
	 * @var DOMElement
	 */
	private $caption_element;

	/**
	 * Constructs the class.
	 *
	 * @param DOMNode    $slide_node      The slide node, possibly an image.
	 * @param DOMElement $caption_element The caption element.
	 */
	public function __construct( DOMNode $slide_node, DOMElement $caption_element ) {
		$this->slide_node      = $slide_node;
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
	 * Gets the slide node.
	 *
	 * @return DOMNode
	 */
	public function get_slide_node() {
		return $this->slide_node;
	}
}
