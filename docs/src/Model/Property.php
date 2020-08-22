<?php
/**
 * Class Property.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Documentation\Model;

/**
 * Documentation reference object representing a property.
 *
 * @property string   $name
 * @property int      $line
 * @property int      $end_line
 * @property mixed    $default
 * @property bool     $static
 * @property string   $visibility
 * @property DocBlock $doc
 */
final class Property implements Leaf {

	use LeafConstruction;
	use HasDocBlock;

	/**
	 * Get an associative array of known keys.
	 *
	 * @return array
	 */
	protected function get_known_keys() {
		return [
			'name'       => '',
			'line'       => '',
			'end_line'   => '',
			'default'    => 0,
			'static'     => false,
			'visibility' => '',
			'doc'        => new DocBlock( [] ),
		];
	}
}
