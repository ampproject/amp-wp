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
		foreach ( self::instances() as $service ) {
			$service->register();
		}
	}

	/**
	 * Run activation logic.
	 *
	 * This should be hooked up to the WordPress activation hook.
	 *
	 * @todo Iterate over blogs on multisite to activate on all subsites by default here?
	 *
	 * @param bool $network_wide Whether the activation was done network-wide.
	 * @return void
	 */
	public static function activate( $network_wide ) {
		foreach ( self::instances() as $service ) {
			if ( ! $service instanceof HasActivation ) {
				continue;
			}

			$service->activate( $network_wide );
		}
	}

	/**
	 * Run deactivation logic.
	 *
	 * This should be hooked up to the WordPress deactivation hook.
	 *
	 * @todo Iterate over blogs on multisite to deactivate on all subsites by default here?
	 *
	 * @param bool $network_wide Whether the deactivation was done network-wide.
	 * @return void
	 */
	public static function deactivate( $network_wide ) {
		foreach ( self::instances() as $service ) {
			if ( ! $service instanceof HasDeactivation ) {
				continue;
			}

			$service->deactivate( $network_wide );
		}
	}

	/**
	 * Get the services.
	 *
	 * @return Service[] Services.
	 */
	private static function instances() {
		if ( empty( self::$instances ) ) {
			foreach ( self::ALL as $service_class ) {
				self::$instances[ $service_class ] = new $service_class();
			}
		}
		return self::$instances;
	}
}
