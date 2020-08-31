<?php
/**
 * Class Tag.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Documentation\Model;

/**
 * Documentation reference object representing a doc-block annotation.
 *
 * @property string   $name
 * @property string   $content
 * @property string[] $types
 * @property string   $variable
 */
final class Tag implements Leaf {

	use LeafConstruction;

	/**
	 * Get an associative array of known keys.
	 *
	 * @return array
	 */
	protected function get_known_keys() {
		return [
			'name'     => '',
			'content'  => '',
			'types'    => [],
			'variable' => '',
		];
	}
}
