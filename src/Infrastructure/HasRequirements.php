<?php
/**
 * Interface HasRequirements.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Infrastructure;

/**
 * Something that requires other services to be registered before it can be registered.
 *
 * A class marked as having requirements can return the list of services it requires
 * to be available before it can be registered.
 *
 * @since 2.2
 * @internal
 */
interface HasRequirements {

	/**
	 * Get the list of service IDs required for this service to be registered.
	 *
	 * @return string[] List of required services.
	 */
	public static function get_requirements();
}
