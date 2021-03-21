<?php
/**
 * Final class InjectionChain.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Infrastructure\Injector;

use LogicException;

/**
 * The injection chain is similar to a trace, keeping track of what we have done
 * so far and at what depth within the auto-wiring we currently are.
 *
 * It is used to detect circular dependencies, and can also be dumped for
 * debugging information.
 *
 * @since 2.0
 * @internal
 */
final class InjectionChain {

	/** @var array<string> */
	private $chain = [];

	/** @var array<bool> */
	private $resolutions = [];

	/**
	 * Add class to injection chain.
	 *
	 * @param string $class Class to add to injection chain.
	 * @return self Modified injection chain.
	 */
	public function add_to_chain( $class ) {
		$new_chain          = clone $this;
		$new_chain->chain[] = $class;

		return $new_chain;
	}

	/**
	 * Add resolution for circular reference detection.
	 *
	 * @param string $resolution Resolution to add.
	 * @return self Modified injection chain.
	 */
	public function add_resolution( $resolution ) {
		$new_chain                             = clone $this;
		$new_chain->resolutions[ $resolution ] = true;

		return $new_chain;
	}

	/**
	 * Get the last class that was pushed to the injection chain.
	 *
	 * @return string Last class pushed to the injection chain.
	 * @throws LogicException If the injection chain is accessed too early.
	 */
	public function get_class() {
		if ( empty( $this->chain ) ) {
			throw new LogicException(
				'Access to injection chain before any resolution was made.'
			);
		}

		return \end( $this->chain ) ?: '';
	}

	/**
	 * Get the injection chain.
	 *
	 * @return array Chain of injections.
	 */
	public function get_chain() {
		return \array_reverse( $this->chain );
	}

	/**
	 * Check whether the injection chain already has a given resolution.
	 *
	 * @param string $resolution Resolution to check for.
	 * @return bool Whether the resolution was found.
	 */
	public function has_resolution( $resolution ) {
		return \array_key_exists( $resolution, $this->resolutions );
	}

	/**
	 * Check whether the injection chain already encountered a class.
	 *
	 * @param string $class Class to check.
	 * @return bool Whether the given class is already part of the chain.
	 */
	public function is_in_chain( $class ) {
		return in_array( $class, $this->chain, true );
	}
}
