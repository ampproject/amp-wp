<?php
/**
 * Class CaptionedImage
 *
 * @package AMP
 */

namespace Amp\AmpWP\Component;

use DOMElement;

/**
 * Class CaptionedImage
 *
 * @internal
 * @since 1.5.0
 */
final class CaptionedImage extends Image implements HasCaption {

	/**
	 * The caption text.
	 *
	 * @var string
	 */
	private $caption;

	/**
	 * Constructs the class.
	 *
	 * @param DOMElement $image_node The image node.
	 * @param string     $caption    The caption text.
	 */
	public function __construct( DOMElement $image_node, $caption ) {
		parent::__construct( $image_node );
		$this->caption = $caption;
	}

	/**
	 * Gets the caption text.
	 *
	 * @return string The caption text.
	 */
	public function get_caption() {
		return $this->caption;
	}
}
