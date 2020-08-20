<?php
/**
 * Class Class_.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Documentation\Model;

/**
 * Documentation reference object representing a class.
 *
 * @property string     $name
 * @property string     $namespace
 * @property int        $line
 * @property int        $end_line
 * @property bool       $final
 * @property bool       $abstract
 * @property string     $extends
 * @property string[]   $implements
 * @property Property[] $properties
 * @property Method[]   $methods
 * @property DocBlock   $doc
 */
final class Class_ {

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
			'line',
			'end_line',
			'final',
			'abstract',
			'extends',
			'implements',
			'properties',
			'methods',
			'doc',
		];
	}

	/**
	 * Process the properties entry.
	 *
	 * @param array $value Array of property entries.
	 */
	private function process_properties( $value ) {
		$this->properties = [];

		foreach ( $value as $property ) {
			$this->properties[ $property[ 'name' ] ] = new Property( $value, $this );
		}
	}

	/**
	 * Process the methods entry.
	 *
	 * @param array $value Array of method entries.
	 */
	private function process_methods( $value ) {
		$this->methods = [];

		foreach ( $value as $method ) {
			$this->methods[ $method[ 'name' ] ] = new Method( $value, $this );
		}
	}

	/**
	 * Process a doc-block entry.
	 *
	 * @param array $value Associative array of the doc-block data.
	 */
	private function process_doc( $value ) {
		$this->doc = new DocBlock( $value, $this );
	}
}
