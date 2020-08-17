<?php
/**
 * Final class FallbackInstantiator.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Infrastructure\Injector;

use AmpProject\AmpWP\Infrastructure\Instantiator;

/**
 * Fallback instantiator to use in case none was provided.
 *
 * @since 2.0
 * @internal
 */
final class FallbackInstantiator implements Instantiator {

	/**
	 * Make an object instance out of an interface or class.
	 *
	 * @param string $class        Class to make an object instance out of.
	 * @param array  $dependencies Optional. Dependencies of the class.
	 * @return object Instantiated object.
	 */
	public function instantiate( $class, $dependencies = [] ) {
		return new $class( ...$dependencies );
	}
}
