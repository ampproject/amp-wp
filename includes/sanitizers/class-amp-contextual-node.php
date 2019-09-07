<?php
/**
 * Class AMP_Contextual_Node
 *
 * @package AMP
 */

/**
 * The AMP_Contextual_Node class is used to store operations in a node stack to
 * alter the current context as it is stored in the AMP_Context instance.
 *
 * This is meant to be used to wrap child nodes in the linearized stack so that
 * we can track when we enter or leave a certain context.
 */
final class AMP_Contextual_Node extends DOMNode {

	const OPERATION_ENTER  = 'enter';
	const OPERATION_LEAVE  = 'leave';
	const OPERATION_TOGGLE = 'toggle';

	/**
	 * Context that this node refers to.
	 *
	 * @var string
	 */
	private $context;

	/**
	 * Operation to perform on the context.
	 *
	 * @var string
	 */
	private $operation;

	/**
	 * Associative array of state to go with the operation.
	 *
	 * The exact nature of this state depends on the actual context to modify.
	 *
	 * @var array
	 */
	private $state;

	/**
	 * Instantiate a AMP_Contextual_Node object.
	 *
	 * @param string $context   Context that this node refers to.
	 * @param string $operation Operation to perform on the context.
	 * @param array  $state     Associative array of state to go with the
	 *                          operation.
	 */
	public function __construct( $context, $operation, $state = [] ) {
		$this->context   = $context;
		$this->operation = $operation;
		$this->state     = $state;
	}

	/**
	 * Get the context this node refers to.
	 *
	 * @return string Context.
	 */
	public function get_context() {
		return $this->context;
	}

	/**
	 * Get the operation to perform on the context.
	 *
	 * @return string Operation to perform.
	 */
	public function get_operation() {
		return $this->operation;
	}

	/**
	 * Get the state associated with the operation.
	 *
	 * @return array Associative array of state.
	 */
	public function get_state() {
		return $this->state;
	}
}
