<?php
/**
 * Class Services.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP;

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
	 * Get a particular service out of the service container.
	 *
	 * @param string $service Service ID to retrieve.
	 * @return Service
	 */
	public static function get( $service ) {
		return self::get_container()->get( $service );
	}

	/**
	 * Get an instance of the service container.
	 *
	 * @return ServiceContainer
	 */
	public static function get_container() {
		static $container = null;

		if ( null === $container ) {
			$container = AmpWpPluginFactory::create()->get_container();
		}

		return $container;
	}
}
