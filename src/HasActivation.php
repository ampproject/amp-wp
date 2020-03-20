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
	 * @return void
	 */
	public function activate();
}
