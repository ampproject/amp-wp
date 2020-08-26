<?php
/**
 * Class Include_.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Documentation\Model;

/**
 * Documentation reference object representing an include or require statement.
 *
 * @property string $name
 * @property int    $line
 * @property string $type
 *
 * phpcs:disable PEAR.NamingConventions.ValidClassName.Invalid
 */
final class Include_ implements Leaf {

	use LeafConstruction;

	/**
	 * Get an associative array of known keys.
	 *
	 * @return array
	 */
	protected function get_known_keys() {
		return [
			'name' => '',
			'line' => 0,
			'type' => '',
		];
	}
}
