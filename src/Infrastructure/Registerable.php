<?php
/**
 * Interface Registerable.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Infrastructure;

/**
 * Something that can be registered.
 *
 * For a clean code base, a class instantiation should never have side-effects,
 * only initialize the internals of the object so that it is ready to be used.
 *
 * This means, though, that the system does not have any knowledge of the
 * objects when they are merely instantiated.
 *
 * Registering such an object is the explicit act of making it known to the
 * overarching system.
 *
 * @since 2.0
 * @internal
 */
interface Registerable {

	/**
	 * Register the service.
	 *
	 * @return void
	 */
	public function register();
}
