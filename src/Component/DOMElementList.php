<?php
/**
 * Class DOMElementList
 *
 * @package Amp\AmpWP
 */

namespace Amp\AmpWP\Component;

use IteratorAggregate;
use Countable;
use DOMElement;
use ArrayIterator;

/**
 * Class DOMElementList
 *
 * @internal
 * @since 1.5.0
 */
final class DOMElementList implements IteratorAggregate, Countable {

	/**
	 * The elements, possibly with captions.
	 *
	 * @var DOMElement[]
	 */
	private $elements = [];

	/**
	 * Adds an image to the list.
	 *
	 * @param DOMElement $element The element to add, possibly an image.
	 * @param string     $caption The caption to add.
	 * @return self
	 */
	public function add( DOMElement $element, $caption = '' ) {
		$this->elements[] = empty( $caption ) ? $element->cloneNode() : new CaptionedSlide( $element->cloneNode(), $caption );
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
		return new ArrayIterator(
			array_map(
				function( $element ) {
					return $element instanceof DOMElement ? $element->cloneNode() : $element;
				},
				$this->elements
			)
		);
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
