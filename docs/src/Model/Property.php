<?php
/**
 * Class Property.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Documentation\Model;

/**
 * Documentation reference object representing a property.
 *
 * @property string   $name
 * @property int      $line
 * @property int      $end_line
 * @property mixed    $default
 * @property bool     $static
 * @property string   $visibility
 * @property DocBlock $doc
 */
final class Property {

	use LeafConstruction;

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
		$this->doc = new DocBlock( $value );
	}
}
