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
	 * @return array
	 */
	protected function get_known_keys() {
		return [
			'name' => '',
			'default' => 0,
			'type' => '',
		];
	}

	/**
	 * Check if a description is available.
	 *
	 * @return bool Whether a description is available.
	 */
	public function has_description() {
		return true;
	}

	/**
	 * Get the description of the argument.
	 *
	 * @return string Description of the argument.
	 */
	public function get_description() {
		return 'TODO';
	}
}
