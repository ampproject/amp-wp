<?php
/**
 * Trait EntityConstruction;
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Documentation\Templating;

trait EntityConstruction {

	/**
	 * EntityConstruction constructor.
	 *
	 * @param array $data Associative array of data to process.
	 */
	public function __construct( $data ) {
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
}
