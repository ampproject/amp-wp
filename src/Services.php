<?php
/**
 * Class Services.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP;

use AmpProject\AmpWP\Infrastructure\Injector;
use AmpProject\AmpWP\Infrastructure\Plugin;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Infrastructure\ServiceContainer;

/**
 * Convenience class to get easy access to the service container.
 *
 * Using this should always be the last resort.
 * Always prefer to use constructor injection instead.
 *
 * @package AmpProject\AmpWP
 * @since 2.0
 * @internal
 */
final class Services {

	/**
	 * Plugin object instance.
	 *
	 * @var Plugin
	 */
	private static $plugin;

	/**
	 * Service container object instance.
	 *
	 * @var ServiceContainer
	 */
	private static $container;

	/**
	 * Dependency injector object instance.
	 *
	 * @var Injector
	 */
	private static $injector;

	/**
	 * Get a particular service out of the service container.
	 *
	 * @param string $service Service ID to retrieve.
	 * @return Service
	 */
	public static function get( $service ) {
		return self::get_container()->get( $service );
	}

	/**
	 * Get an instance of the plugin.
	 *
	 * @return Plugin Plugin object instance.
	 */
	public static function get_plugin() {
		if ( null === self::$plugin ) {
			self::$plugin = AmpWpPluginFactory::create();
		}

		return self::$plugin;
	}

	/**
	 * Get an instance of the service container.
	 *
	 * @return ServiceContainer Service container object instance.
	 */
	public static function get_container() {
		if ( null === self::$container ) {
			self::$container = self::get_plugin()->get_container();
		}

		return self::$container;
	}

	/**
	 * Get an instance of the dependency injector.
	 *
	 * @return Injector Dependency injector object instance.
	 */
	public static function get_injector() {
		if ( null === self::$injector ) {
			self::$injector = self::get_container()->get( 'injector' );
		}

		return self::$injector;
	}
}
