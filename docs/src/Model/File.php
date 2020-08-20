<?php
/**
 * Class File.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Documentation\Model;

/**
 * Documentation reference object representing a file.
 *
 * @property DocBlock    $file
 * @property string      $path
 * @property int         $root
 * @property Class_[]    $classes
 * @property Usage[]     $uses
 * @property Function_[] $functions
 * @property Include_[]  $includes
 */
final class File {

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
