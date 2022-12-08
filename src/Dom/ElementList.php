<?php
/**
 * Class ElementList
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Dom;

use AmpProject\AmpWP\Component\CaptionedSlide;
use IteratorAggregate;
use Countable;
use DOMElement;
use ArrayIterator;

/**
 * Class ElementList
 *
 * @internal
 * @since 1.5.0
 */
final class ElementList implements IteratorAggregate, Countable {

	/**
	 * The elements, possibly with captions.
	 *
	 * @var array
	 */
	private $elements = [];

	/**
	 * Adds an element to the list, possibly with a caption.
	 *
	 * @param DOMElement      $element The element to add, possibly an image.
	 * @param DOMElement|null $caption The caption for the element.
	 * @return ElementList A clone of this list, with the new element added.
	 */
	public function add( DOMElement $element, DOMElement $caption = null ): ElementList {
		$cloned_list             = clone $this;
		$cloned_list->elements[] = null === $caption ? $element : new CaptionedSlide( $element, $caption );
		return $cloned_list;
	}

	/**
	 * Gets an iterator with the elements.
	 *
	 * This together with the IteratorAggregate turns the object into a "Traversable",
	 * so you can just foreach over it and receive its elements in the correct type.
	 *
	 * @return ArrayIterator An iterator with the elements.
	 */
	public function getIterator(): ArrayIterator {
		return new ArrayIterator( $this->elements );
	}

	/**
	 * Gets the count of the elements.
	 *
	 * @return int The number of elements.
	 */
	public function count(): int {
		return count( $this->elements );
	}
}
