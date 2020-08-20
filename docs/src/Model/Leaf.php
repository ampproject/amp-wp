<?php
/**
 * Interface Leaf.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Documentation\Model;

/**
 * An individual leaf in a tree structure.
 *
 * Each leaf can have a parent within the same tree structure.
 */
interface Leaf {

	/**
	 * Get the parent leaf object of the current leaf.
	 *
	 * @return Leaf|null Parent leaf, or null if none.
	 */
	public function get_parent();
}
