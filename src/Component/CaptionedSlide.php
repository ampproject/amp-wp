<?php
/**
 * Class CaptionedSlide
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Component;

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
	 * @var DOMNode
	 */
	private $caption_node;

	/**
	 * Constructs the class.
	 *
	 * @param DOMNode $slide_node   The slide node, possibly an image.
	 * @param DOMNode $caption_node The caption node.
	 */
	public function __construct( DOMNode $slide_node, DOMNode $caption_node ) {
		$this->slide_node   = $slide_node;
		$this->caption_node = $caption_node;
	}

	/**
	 * Gets the caption node.
	 *
	 * @return DOMNode
	 */
	public function get_caption_node() {
		return $this->caption_node;
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
