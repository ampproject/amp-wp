<?php
/**
 * Interface Deactivateable.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Infrastructure;

/**
 * Something that can be deactivated.
 *
 * By tagging a service with this interface, the system will automatically hook
 * it up to the WordPress deactivation hook.
 *
 * This way, we can just add the simple interface marker and not worry about how
 * to wire up the code to reach that part during the static deactivation hook.
 *
 * @since 2.0
 * @internal
 */
interface Deactivateable {

	/**
	 * Deactivate the service.
	 *
	 * @param bool $network_wide Whether the deactivation was done network-wide.
	 * @return void
	 */
	public function deactivate( $network_wide );
}
