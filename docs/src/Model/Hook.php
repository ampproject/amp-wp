<?php
/**
 * Class Hook.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Documentation\Model;

/**
 * Documentation reference object representing an action or filter.
 *
 * @property string     $name
 * @property int        $line
 * @property int        $end_line
 * @property string     $type
 * @property Argument[] $arguments
 * @property DocBlock   $doc
 */
final class Hook implements Leaf {

	use LeafConstruction;
	use HasDocBlock;
	use HasArguments;
	use HasCodeLinks;

	/**
	 * Get an associative array of known keys.
	 *
	 * @return array
	 */
	protected function get_known_keys() {
		return [
			'name'      => '',
			'line'      => 0,
			'end_line'  => 0,
			'type'      => '',
			'arguments' => [],
			'doc'       => new DocBlock( [] ),
		];
	}

	/**
	 * Get the filename to use for the hook.
	 *
	 * @return string Filename to use.
	 */
	public function get_filename() {
		return $this->name;
	}
}
