<?php
/**
 * Class Image_List
 *
 * @package AMP
 */

namespace Amp\AmpWP\Component;

use IteratorAggregate;
use Countable;
use DOMElement;
use ArrayIterator;

/**
 * Class Image_List
 *
 * @internal
 * @since 1.5.0
 */
final class Image_List implements IteratorAggregate, Countable {

	/**
	 * The captioned images.
	 *
	 * @var Captioned_Image[]
	 */
	private $elements = [];

	/**
	 * Adds an image to the list.
	 *
	 * @param DOMElement $image_node The image to add.
	 * @param string     $caption    The caption to add.
	 * @return self
	 */
	public function add( DOMElement $image_node, $caption = '' ) {
		$this->elements[] = empty( $caption ) ? new Image( $image_node ) : new Captioned_Image( $image_node, $caption );
		return $this;
	}

	/**
	 * Gets an iterator with the elements.
	 *
	 * This together with the IteratorAggregate turns the object into a "Traversable",
	 * so you can just foreach over it and receive its elements in the correct type.
	 *
	 * @return ArrayIterator An iterator with the elements.
	 */
	public function getIterator() {
		return new ArrayIterator( $this->elements );
	}

	/**
	 * Gets the count of the images.
	 *
	 * @return int The number of images.
	 */
	public function count() {
		return count( $this->elements );
	}
}
