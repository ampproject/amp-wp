<?php
/**
 * Interface HasActivation.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP;

/**
 * An service that needs to run logic during plugin activation.
 *
 * @package AmpProject\AmpWP
 */
interface HasActivation {

	/**
	 * Run activation logic.
	 *
	 * This should be hooked up to the WordPress activation hook.
	 *
	 * @param bool $network_wide Whether the activation was done network-wide.
	 * @return void
	 */
	public function activate( $network_wide );
}
