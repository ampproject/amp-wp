<?php
/**
 * Class AMP_Context
 *
 * @package AMP
 */

/**
 * The AMP_Context class is a container to store the current contextual
 * information in while traversing nodes. It is meant to be used in tandem with
 * the AMP_Contextual_Node to produce state-altering operations in the node
 * stack.
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
	 * @param string $key Key of the context to check.
	 * @return bool Whether the given context is active.
	 */
	public function is( $key ) {
		return array_key_exists( $key, $this->context ) && $this->context[ $key ];
	}

	/**
	 * Get the state of a given context.
	 *
	 * @param string $key Key of the context to check.
	 * @return array State of the given context.
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
	 * @param string $key   Context to enter.
	 * @param array  $state Optional. Associative array of state related to the
	 *                      tracked context.
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
	 * @param string $key   Context to toggle.
	 * @param array  $state Optional. Associative array of state related to the
	 *                      tracked context.
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
