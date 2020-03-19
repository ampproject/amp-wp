<?php
/**
 * Interface BackgroundTask.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP;

/**
 * A task that is not executed immediately, but registered to run in the background outside of the current process.
 *
 * @package AmpProject\AmpWP
 */
interface BackgroundTask {

	/**
	 * Register the background task with the system.
	 *
	 * @return void
	 */
	public function register();
}
