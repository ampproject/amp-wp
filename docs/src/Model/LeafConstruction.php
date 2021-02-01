<?php
/**
 * Trait LeafConstruction.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Documentation\Model;

use RuntimeException;

trait LeafConstruction {

	/**
	 * Store a reference to the parent leaf.
	 *
	 * @var Leaf|null Parent leaf, or null if none.
	 */
	protected $parent;

	/**
	 * LeafConstruction constructor.
	 *
	 * @param array     $data   Associative array of data to process.
	 * @param Leaf|null $parent Parent leaf, or null if none.
	 */
	public function __construct( $data, $parent = null ) {
		$this->parent = $parent;
		foreach ( $this->get_known_keys() as $key => $default ) {
			$this->process_key( $key, $data );
		}
	}

	/**
	 * Get an associative array of known keys.
	 *
	 * @return array
	 */
	abstract protected function get_known_keys();

	/**
	 * Process an individual key.
	 *
	 * @param string $key  Key to process.
	 * @param array  $data Associative array of data.
	 */
	protected function process_key( $key, $data ) {
		if ( ! is_array( $data ) || ! array_key_exists( $key, $data ) ) {
			return;
		}

		$value  = $data[ $key ];
		$method = "process_{$key}";

		if ( method_exists( $this, $method ) ) {
			$this->$method( $value );
		} else {
			$this->$key = $value;
		}
	}

	/**
	 * Get the parent leaf object of the current leaf.
	 *
	 * @return Leaf|null Parent leaf, or null if none.
	 */
	public function get_parent() {
		return $this->parent;
	}

	/**
	 * Magic getter to return default values when needed.
	 *
	 * @param string $property Property that was requested.
	 * @return mixed Default value for the requested property.
	 * @throws RuntimeException If the property is not known.
	 */
	public function __get( $property ) {
		$known_keys = $this->get_known_keys();

		if ( array_key_exists( $property, $known_keys ) ) {
			return $known_keys[ $property ];
		}

		throw new RuntimeException(
			"Tried to fetch unknown property {$property}."
		);
	}
}
