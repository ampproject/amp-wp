<?php
/**
 * Trait HasArguments.
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
	 * Check if arguments are available.
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

	/**
	 * Get the argument signatures.
	 *
	 * @return string[] Array of argument signatures.
	 */
	private function get_argument_names() {
		$arguments = [];

		foreach ( $this->arguments as $argument ) {
			$arguments[] = sprintf(
				'%s%s%s',
				$argument->type ? "{$this->map_alias( $argument->type )} " : '',
				$argument->name,
				$argument->default ? " = {$argument->default}" : ''
			);
		}

		return $arguments;
	}

	/**
	 * Get the alias for a fully qualified element.
	 *
	 * @param string $fully_qualified_element Fully qualified element to map an
	 *                                        alias to.
	 * @return string Alias, or fully qualified element if none found.
	 */
	private function map_alias( $fully_qualified_element ) {
		if ( empty( $fully_qualified_element ) ) {
			return '';
		}

		$key = array_search( $fully_qualified_element, $this->aliases, true );

		if ( false === $key ) {
			return $fully_qualified_element;
		}

		return $key;
	}
}
