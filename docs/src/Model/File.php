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
 * @property string      $root
 * @property Class_[]    $classes
 * @property Usage[]     $uses
 * @property Function_[] $functions
 * @property Include_[]  $includes
 * @property Hook[]      $hooks
 */
final class File implements Leaf {

	use LeafConstruction;

	/**
	 * Get an associative array of known keys.
	 *
	 * @return array
	 */
	protected function get_known_keys() {
		return [
			'file'      => new DocBlock( [] ),
			'path'      => '',
			'root'      => '',
			'classes'   => [],
			'uses'      => [],
			'functions' => [],
			'includes'  => [],
			'hooks'     => [],
		];
	}

	/**
	 * Process the classes entry.
	 *
	 * @param array $value Array of class entries.
	 */
	private function process_classes( $value ) {
		$this->classes = [];

		foreach ( $value as $class ) {
			$this->classes[] = new Class_( $class, $this );
		}
	}

	/**
	 * Process the uses entry.
	 *
	 * @param array $value Array of usage entries.
	 */
	private function process_uses( $value ) {
		$this->uses = [];

		foreach ( $value as $use ) {
			$this->uses[] = new Usage( $use, $this );
		}
	}

	/**
	 * Process the functions entry.
	 *
	 * @param array $value Array of function entries.
	 */
	private function process_functions( $value ) {
		$this->functions = [];

		foreach ( $value as $function ) {
			$this->functions[] = new Function_( $function, $this );
		}
	}

	/**
	 * Process the includes entry.
	 *
	 * @param array $value Array of include entries.
	 */
	private function process_includes( $value ) {
		$this->includes = [];

		foreach ( $value as $include ) {
			$this->includes[] = new Include_( $include, $this );
		}
	}

	/**
	 * Process the hooks entry.
	 *
	 * @param array $value Array of hook entries.
	 */
	private function process_hooks( $value ) {
		$this->hooks = [];

		foreach ( $value as $hook ) {
			$this->hooks[] = new Hook( $hook, $this );
		}
	}
}
