<?php
/**
 * Class ClassEntity.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Documentation\Templating;

/**
 * Entity object representing a class.
 *
 * @property string           $name
 * @property string           $namespace
 * @property int              $line
 * @property int              $end_line
 * @property bool             $final
 * @property bool             $abstract
 * @property string           $extends
 * @property string[]         $implements
 * @property PropertyEntity[] $properties
 * @property MethodEntity[]   $methods
 * @property DocBlockEntity   $doc
 */
final class ClassEntity {

	use EntityConstruction;

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
			$this->properties[ $property[ 'name' ] ] = new PropertyEntity( $value );
		}
	}

	/**
	 * Process a doc-block entry.
	 *
	 * @param array $value Associative array of the doc-block.
	 * @return DocBlockEntity Doc-block entity object.
	 */
	private function process_doc( $value ) {
		return new DocBlockEntity( $value );
	}
}
