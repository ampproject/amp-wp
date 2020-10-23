<?php
/**
 * Interface Conditional.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Infrastructure;

/**
 * Something that can be instantiated conditionally.
 *
 * A class marked as being conditionally can be asked whether it should be
 * instantiated through a static method. An example would be a service that is
 * only available on the admin backend.
 *
 * This allows for a more systematic and automated optimization of how the
 * different parts of the plugin are enabled or disabled.
 *
 * @since 2.0
 * @internal
 */
interface Conditional {

	/**
	 * Check whether the conditional object is currently needed.
	 *
	 * @return bool Whether the conditional object is needed.
	 */
	public static function is_needed();
}
