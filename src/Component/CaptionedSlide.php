<?php
/**
 * Class CaptionedSlide
 *
 * @package AMP
 */

namespace Amp\AmpWP\Component;

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
	 * @param DOMElement $image_node The slide node, possibly an image.
	 * @param string     $caption    The caption text.
	 */
	public function __construct( DOMElement $image_node, $caption ) {
		$this->slide_node = $image_node;
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
	 * @return DOMElement
	 */
	public function get_slide_node() {
		return $this->slide_node;
	}
}
