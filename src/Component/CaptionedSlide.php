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
	 * The caption text.
	 *
	 * @var string
	 */
	private $caption;

	/**
	 * Constructs the class.
	 *
	 * @param DOMNode $slide_node The slide node, possibly an image.
	 * @param string  $caption    The caption text.
	 */
	public function __construct( DOMNode $slide_node, $caption ) {
		$this->slide_node = $slide_node;
		$this->caption    = $caption;
	}

	/**
	 * Gets the caption text.
	 *
	 * @return string The caption text.
	 */
	public function get_caption() {
		return $this->caption;
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
