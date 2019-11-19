<?php
/**
 * Class Captioned_Image
 *
 * @package AMP
 */

namespace Amp\AmpWP\Component;

/**
 * Class Captioned_Image
 *
 * @internal
 * @since 1.5.0
 */
final class Captioned_Image extends Image implements Has_Caption {

	/**
	 * The caption text.
	 *
	 * @var string
	 */
	private $caption;

	/**
	 * Constructs the class.
	 *
	 * @param \DOMElement $image_node The image node.
	 * @param string      $caption    The caption text.
	 */
	public function __construct( \DOMElement $image_node, $caption ) {
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
