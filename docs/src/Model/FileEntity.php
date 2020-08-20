<?php
/**
 * Class FileEntity.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Documentation\Model;

/**
 * Entity object representing a file.
 *
 * @property DocBlockEntity   $file
 * @property string           $path
 * @property int              $root
 * @property ClassEntity[]    $classes
 * @property UseEntity[]      $uses
 * @property FunctionEntity[] $functions
 * @property IncludeEntity[]  $includes
 */
final class FileEntity {

	use LeafConstruction;

	/**
	 * Get an associative array of known keys.
	 *
	 * @return string[]
	 */
	protected function get_known_keys() {
		return [
			'file',
			'path',
			'root',
			'classes',
			'uses',
			'functions',
			'includes',
		];
	}
}
