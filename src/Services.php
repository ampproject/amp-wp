<?php
/**
 * Interface BackgroundTask.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP;

use AmpProject\AmpWP\BackgroundTask\MonitorCssTransientCaching;

/**
 * Centralized service handler to simplify management of registration, activation and deactivation hooks.
 *
 * @package AmpProject\AmpWP
 */
final class Services {

	/**
	 * List of known services.
	 *
	 * @var string[]
	 */
	const ALL = [
		MonitorCssTransientCaching::class,
	];

	/**
	 * Array of service object instances.
	 *
	 * @var Service[]
	 */
	private static $instances = [];

	/**
	 * Register the services with the system.
	 *
	 * @return void
	 */
	public static function register() {
		self::maybe_instantiate();

		foreach ( self::$instances as $service ) {
			$service->register();
		}
	}

	/**
	 * Run activation logic.
	 *
	 * This should be hooked up to the WordPress deactivation hook.
	 *
	 * @return void
	 */
	public static function activate() {
		self::maybe_instantiate();

		foreach ( self::$instances as $service ) {
			if ( ! $service instanceof HasActivation ) {
				continue;
			}

			$service->activate();
		}
	}

	/**
	 * Run deactivation logic.
	 *
	 * This should be hooked up to the WordPress deactivation hook.
	 *
	 * @return void
	 */
	public static function deactivate() {
		self::maybe_instantiate();

		foreach ( self::$instances as $service ) {
			if ( ! $service instanceof HasDeactivation ) {
				continue;
			}

			$service->deactivate();
		}
	}

	/**
	 * Instantiate the service objects if that has not been done yet.
	 *
	 * @return void
	 */
	private static function maybe_instantiate() {
		if ( ! empty( self::$instances ) ) {
			return;
		}

		foreach ( self::ALL as $service_class ) {
			self::$instances[ $service_class ] = new $service_class();
		}
	}
}
