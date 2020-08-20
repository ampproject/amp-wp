<?php
/**
 * Class Method.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Documentation\Model;

/**
 * Documentation reference object representing a method.
 *
 * @property string     $name
 * @property string     $namespace
 * @property Alias_[]   $aliases
 * @property int        $line
 * @property int        $end_line
 * @property bool       $final
 * @property bool       $abstract
 * @property bool       $static
 * @property string     $visibility
 * @property Argument[] $arguments
 * @property DocBlock   $doc
 * @property Usage[]    $uses
 */
final class Method {

	use LeafConstruction;

	/**
	 * Get an associative array of known keys.
	 *
	 * @return string[]
	 */
	protected function get_known_keys() {
		return [
			'name',
			'namespace',
			// 'aliases',
			'line',
			'end_line',
			'final',
			'abstract',
			'static',
			'visibility',
			'arguments',
			'doc',
			'uses',
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
			$this->arguments[ $argument[ 'name' ] ] = new Argument( $value, $this );
		}
	}

	/**
	 * Process a doc-block entry.
	 *
	 * @param array $value Associative array of the doc-block.
	 */
	private function process_doc( $value ) {
		$this->doc = new DocBlock( $value, $this );
	}

	/**
	 * Process the uses entry.
	 *
	 * @param array $value Array of usage entries.
	 */
	private function process_uses( $value ) {
		$this->uses = [];

		foreach ( $value as $use ) {
			$this->uses[ $use[ 'name' ] ] = new Usage( $value, $this );
		}
	}
}
