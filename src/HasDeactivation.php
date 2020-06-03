<?php
/**
 * Interface HasDeactivation.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP;

/**
 * An service that needs to run logic during plugin deactivation.
 *
 * @package AmpProject\AmpWP
 */
interface HasDeactivation {

	/**
	 * Run deactivation logic.
	 *
	 * This should be hooked up to the WordPress deactivation hook.
	 *
	 * @param bool $network_wide Whether the deactivation was done network-wide.
	 * @return void
	 */
	public function deactivate( $network_wide );
}
