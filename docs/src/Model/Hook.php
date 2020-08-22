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

	/**
	 * Get an associative array of known keys.
	 *
	 * @return string[]
	 */
	protected function get_known_keys() {
		return [
			'name',
			'line',
			'end_line',
			'type',
			'arguments',
			'doc',
		];
	}

	/**
	 * Process the arguments entry.
	 *
	 * @param array $value Array of argument entries.
	 */
	private function process_arguments( $value ) {
		$this->arguments = [];

		foreach ( $value as $argument ) {
			$this->arguments[ $argument[ 'name' ] ] = new Argument( $argument, $this );
		}
	}
}
