<?php
/**
 * Interface Service.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP;

/**
 * A stand-alone and generic piece of logic.
 *
 * @package AmpProject\AmpWP
 */
interface Service {

	/**
	 * Register the service with the system.
	 *
	 * @return void
	 */
	public function register();
}
