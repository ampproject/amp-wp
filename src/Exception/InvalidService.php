<?php
/**
 * Exception InvalidService.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Exception;

use InvalidArgumentException;

/**
 * Exception thrown when an invalid service was requested.
 *
 * @since 2.0
 * @internal
 */
final class InvalidService
	extends InvalidArgumentException
	implements AmpWpException {

	/**
	 * Create a new instance of the exception for a service class name that is
	 * not recognized.
	 *
	 * @param string|object $service Class name of the service that was not
	 *                               recognized.
	 *
	 * @return self
	 */
	public static function from_service( $service ) {
		$message = \sprintf(
			'The service "%s" is not recognized and cannot be registered.',
			\is_object( $service )
				? \get_class( $service )
				: (string) $service
		);

		return new self( $message );
	}

	/**
	 * Create a new instance of the exception for a service identifier that is
	 * not recognized.
	 *
	 * @param string $service_id Identifier of the service that is not being
	 *                           recognized.
	 *
	 * @return self
	 */
	public static function from_service_id( $service_id ) {
		$message = \sprintf(
			'The service ID "%s" is not recognized and cannot be retrieved.',
			$service_id
		);

		return new self( $message );
	}
}
