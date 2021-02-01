<?php
/**
 * Interface ServiceContainer.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Infrastructure;

use AmpProject\AmpWP\Exception\InvalidService;
use ArrayAccess;
use Countable;
use Traversable;

/**
 * The service container collects all services to manage them.
 *
 * This is based on PSR-11 and should extend that one if Composer dependencies
 * are being used. Relying on a standardized interface like PSR-11 means you'll
 * be able to easily swap out the implementation for something else later on.
 *
 * @see https://www.php-fig.org/psr/psr-11/
 * @since 2.0
 * @internal
 */
interface ServiceContainer extends Traversable, Countable, ArrayAccess {

	/**
	 * Find a service of the container by its identifier and return it.
	 *
	 * @param string $id Identifier of the service to look for.
	 *
	 * @throws InvalidService If the service could not be found.
	 *
	 * @return Service Service that was requested.
	 */
	public function get( $id );

	/**
	 * Check whether the container can return a service for the given
	 * identifier.
	 *
	 * @param string $id Identifier of the service to look for.
	 *
	 * @return bool
	 */
	public function has( $id );

	/**
	 * Put a service into the container for later retrieval.
	 *
	 * @param string  $id      Identifier of the service to put into the
	 *                         container.
	 * @param Service $service Service to put into the container.
	 */
	public function put( $id, Service $service );
}
