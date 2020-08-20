<?php
/**
 * Class Argument.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Documentation\Model;

/**
 * Documentation reference object representing a function or method argument.
 *
 * @property string $name
 * @property mixed  $default
 * @property string $type
 */
final class Argument implements Leaf {

	use LeafConstruction;

	/**
	 * Get an associative array of known keys.
	 *
	 * @return string[]
	 */
	protected function get_known_keys() {
		return [
			'name',
			'default',
			'type',
		];
	}
}
