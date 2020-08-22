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
	 * Process the arguments entry.
	 *
	 * @param array $value Array of argument entries.
	 */
	private function process_arguments( $value ) {
		$this->arguments = [];

		foreach ( $value as $argument ) {
			$this->arguments[] = new Argument( $argument, $this );
		}
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
	 * Check if a description is available.
	 *
	 * @return bool Whether a description is available.
	 */
	public function has_description() {
		return true;
	}

	/**
	 * Get the description of the hook.
	 *
	 * @return string Description of the hook.
	 */
	public function get_description() {
		return 'TODO';
	}
}
