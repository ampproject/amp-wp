<?php
/**
 * Trait EntityConstruction;
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Documentation\Model;

interface Entity {

	/**
	 * Get the parent entity object of the current entity.
	 *
	 * @return Entity|null Parent entity, or null if none.
	 */
	public function get_parent();
}
