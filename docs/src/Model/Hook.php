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

	/**
	 * Get the type of the hook with the first letter in uppercase.
	 *
	 * @return string Capitalized hook type.
	 */
	public function get_capitalized_type() {
		return ucfirst( $this->type );
	}

	/**
	 * Check whether the hook is an action.
	 *
	 * @return bool Whether the hook is an action.
	 */
	public function is_action() {
		return 'action' === $this->type;
	}

	/**
	 * Check whether the hook is a filter.
	 *
	 * @return bool Whether the hook is a filter.
	 */
	public function is_filter() {
		return 'filter' === $this->type;
	}
}
