<?php
/**
 * Final class AmpWpPluginFactory.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP;

use AmpProject\AmpWP\Infrastructure\Plugin;

/**
 * The plugin factory is responsible for instantiating the plugin and returning
 * that instance.
 *
 * It can decide whether to return a shared or a fresh instance as needed.
 *
 * To read more about why this is preferable to a Singleton,
 *
 * @see https://www.alainschlesser.com/singletons-shared-instances/
 * @since 2.0
 * @internal
 */
final class AmpWpPluginFactory {

	/**
	 * Create and return an instance of the plugin.
	 *
	 * This always returns a shared instance. This way, outside code can always
	 * get access to the object instance of the plugin.
	 *
	 * @return Plugin Plugin instance.
	 */
	public static function create() {
		static $plugin = null;

		if ( null === $plugin ) {
			$plugin = new AmpWpPlugin();
		}

		return $plugin;
	}
}
