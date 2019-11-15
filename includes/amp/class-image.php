<?php
/**
 * Class Image
 *
 * @package AMP
 */

namespace AMP;

/**
 * Class Image
 *
 * @internal
 * @since 1.5.0
 */
class Image {

	/**
	 * The image node.
	 *
	 * @var \DOMElement
	 */
	protected $image_node;

	/**
	 * Constructs the class.
	 *
	 * @param \DOMElement $image_node The image node.
	 */
	public function __construct( \DOMElement $image_node ) {
		$this->image_node = $image_node;
	}

	/**
	 * Gets the image.
	 *
	 * @return \DOMElement
	 */
	public function get_image_node() {
		return $this->image_node;
	}
}
