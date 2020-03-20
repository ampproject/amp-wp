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
	 * @return void
	 */
	public function deactivate();
}
