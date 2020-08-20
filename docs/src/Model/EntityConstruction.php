<?php
/**
 * Trait EntityConstruction;
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Documentation\Model;

trait EntityConstruction {

	/**
	 * Store a reference to the parent entity.
	 *
	 * @var Entity|null Parent entity, or null if none.
	 */
	protected $parent;

	/**
	 * EntityConstruction constructor.
	 *
	 * @param array       $data   Associative array of data to process.
	 * @param Entity|null $parent Parent entity, or null if none.
	 */
	public function __construct( $data, $parent = null ) {
		$this->parent = $parent;
		foreach ( $this->get_known_keys() as $key ) {
			$this->process_key( $key, $data );
		}
	}

	/**
	 * Get an associative array of known keys.
	 *
	 * @return string[]
	 */
	abstract protected function get_known_keys();

	/**
	 * Process an individual key.
	 *
	 * @param string $key  Key to process.
	 * @param array  $data Associative array of data.
	 */
	protected function process_key( $key, $data ) {
		if ( ! array_key_exists( $key, $data ) ) {
			return;
		}

		$value  = $data[ $key ];
		$method = "process_{$key}";

		if ( method_exists( $this, $method ) ) {
			$this->$key = $this->$method( $value );
		} else {
			$this->$key = $value;
		}
	}


	/**
	 * Get the parent entity object of the current entity.
	 *
	 * @return Entity|null Parent entity, or null if none.
	 */
	public function get_parent() {
		return $this->parent;
	}
}
