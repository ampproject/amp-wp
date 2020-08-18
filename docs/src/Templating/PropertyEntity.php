<?php
/**
 * Class PropertyEntity.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Documentation\Templating;

/**
 * Entity object representing a property.
 *
 * @property string         $name
 * @property int            $line
 * @property int            $end_line
 * @property mixed          $default
 * @property bool           $static
 * @property string         $visibility
 * @property DocBlockEntity $doc
 */
final class PropertyEntity {

	use EntityConstruction;

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
			'default',
			'static',
			'visibility',
			'doc',
		];
	}

	/**
	 * Process a doc-block entry.
	 *
	 * @param array $value Associative array of the doc-block.
	 */
	private function process_doc( $value ) {
		$this->doc = new DocBlockEntity( $value );
	}
}
