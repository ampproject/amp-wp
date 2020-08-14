<?php
/**
 * Class MethodEntity.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Documentation\Templating;

/**
 * Entity object representing a method.
 *
 * @property string           $name
 * @property string           $namespace
 * @property AliasEntity[]    $aliases
 * @property int              $line
 * @property int              $end_line
 * @property bool             $final
 * @property bool             $abstract
 * @property bool             $static
 * @property string           $visibility
 * @property ArgumentEntity[] $arguments
 * @property DocBlockEntity   $doc
 * @property UseEntity[]      $uses
 */
final class MethodEntity {

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
			'aliases',
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
	 * Process a doc-block entry.
	 *
	 * @param array $value Associative array of the doc-block.
	 * @return DocBlockEntity Doc-block entity object.
	 */
	private function process_doc( $value ) {
		return new DocBlockEntity( $value );
	}
}
