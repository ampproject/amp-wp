<?php
/**
 * Class AMP_Context
 *
 * @package AMP
 */

final class AMP_Context {

	const WITHIN_TEMPLATE_TAG = 'within_template_tag';

	/**
	 * Currently active context.
	 *
	 * @var bool[]
	 */
	private $context = [];

	/**
	 * State associated with a given context.
	 *
	 * @var array[]
	 */
	private $state = [];

	/**
	 * Check whether a given context is active.
	 *
	 * @param $key
	 * @return bool
	 */
	public function is( $key ) {
		return array_key_exists( $key, $this->context ) && $this->context[ $key ];
	}

	/**
	 * Get the state of a given context.
	 *
	 * @param $key
	 * @return array
	 */
	public function get_state( $key ) {
		if ( ! array_key_exists( $key, $this->state ) ) {
			return [];
		}

		return $this->state[ $key ];
	}

	/**
	 * Enter a given context.
	 *
	 * @param string $key Context to enter.
	 */
	public function enter( $key, $state = [] ) {
		$this->context[ $key ] = true;
		$this->state[ $key ]   = (array) $state;
	}

	/**
	 * Leave a given context.
	 *
	 * @param string $key Context to leave.
	 */
	public function leave( $key ) {
		$this->context[ $key ] = false;
		unset( $this->state[ $key ] );
	}

	/**
	 * Toggle the state of a given context.
	 *
	 * @param string $key Context to toggle.
	 */
	public function toggle( $key, $state = [] ) {
		$this->context[ $key ] = ! $this->context[ $key ];
		if ( ! $this->context[ $key ] ) {
			unset( $this->state[ $key ] );
		} else {
			$this->state[ $key ] = (array) $state;
		}
	}
}
