<?php
/**
 * Final class LazilyInstantiatedService.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Infrastructure\ServiceContainer;

use AmpProject\AmpWP\Exception\InvalidService;
use AmpProject\AmpWP\Infrastructure\Service;

/**
 * A service that only gets properly instantiated when it is actually being
 * retrieved from the container.
 *
 * @since 2.0
 * @internal
 */
final class LazilyInstantiatedService implements Service {

	/** @var callable */
	private $instantiation;

	/**
	 * Instantiate a LazilyInstantiatedService object.
	 *
	 * @param callable $instantiation Instantiation callable to use.
	 */
	public function __construct( callable $instantiation ) {
		$this->instantiation = $instantiation;
	}

	/**
	 * Do the actual service instantiation and return the real service.
	 *
	 * @throws InvalidService If the service could not be properly instantiated.
	 *
	 * @return Service Properly instantiated service.
	 */
	public function instantiate() {
		$instantiation = $this->instantiation; // Because uniform variable syntax not supported in PHP 5.6.
		$service       = $instantiation();

		if ( ! $service instanceof Service ) {
			throw InvalidService::from_service( $service );
		}

		return $service;
	}
}
