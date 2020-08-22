<?php
/**
 * Trait HasArguments
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Documentation\Model;

trait HasArguments {

	/**
	 * Arguments for the element.
	 *
	 * @var Argument[]
	 */
	public $arguments = [];

	/**
	 * Process the arguments entry.
	 *
	 * @param array $value Array of argument entries.
	 */
	private function process_arguments( $value ) {
		foreach ( $value as $argument ) {
			$this->arguments[] = new Argument( $argument, $this );
		}
	}

	/**
	 * Check if arugments are available.
	 *
	 * @return bool Whether arguments are available.
	 */
	public function has_arguments() {
		return count( $this->arguments ) > 0;
	}

	/**
	 * Get the collection of arguments.
	 *
	 * @return Argument[] Collection of arguments.
	 */
	public function get_arguments() {
		return $this->arguments;
	}
}
