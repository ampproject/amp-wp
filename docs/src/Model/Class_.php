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
final class Class_ implements Leaf {

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
			$this->properties[ $property[ 'name' ] ] = new Property( $property, $this );
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
			$this->methods[] = new Method( $method, $this );
		}
	}

	/**
	 * Get the fully qualified class name of the class.
	 *
	 * @return string Fully qualified class name.
	 */
	public function get_fqcn() {
		if ( empty( $this->namespace ) || 'global' === $this->namespace ) {
			return $this->name;
		}

		return "{$this->namespace}\\{$this->name}";
	}

	/**
	 * Get the filename to use for the class.
	 *
	 * @return string Filename to use.
	 */
	public function get_filename() {
		$relative_class_name = str_replace(
			'AmpProject\\AmpWP\\',
			'',
			$this->get_fqcn()
		);

		return str_replace( '\\', '/', $relative_class_name );
	}
}
