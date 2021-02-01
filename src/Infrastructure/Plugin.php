<?php
/**
 * Interface Plugin
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Infrastructure;

/**
 * A plugin is basically nothing more than a convention on how manage the
 * lifecycle of a modular piece of code, so that you can:
 *  1. activate it,
 *  2. register it with the framework, and
 *  3. deactivate it again.
 *
 * This is what this interface represents, by assembling the separate,
 * segregated interfaces for each of these lifecycle actions.
 *
 * Additionally, we provide a means to get access to the plugin's container that
 * collects all the services it is made up of. This allows direct access to the
 * services to outside code if needed.
 *
 * @since 2.0
 * @internal
 */
interface Plugin extends Activateable, Deactivateable, Registerable {

	/**
	 * Get the service container that contains the services that make up the
	 * plugin.
	 *
	 * @return ServiceContainer Service container of the plugin.
	 */
	public function get_container();
}
