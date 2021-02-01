<?php
/**
 * Exception InvalidStopwatchEvent.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Exception;

use InvalidArgumentException;

/**
 * Exception thrown when an invalid stopwatch name was requested.
 *
 * @since 2.0
 * @internal
 */
final class InvalidStopwatchEvent
	extends InvalidArgumentException
	implements AmpWpException {

	/**
	 * Create a new instance of the exception for a stopwatch event name that is
	 * not recognized but requested to be stopped.
	 *
	 * @param string $name Name of the event that was requested to be stopped.
	 *
	 * @return self
	 */
	public static function from_name_to_stop( $name ) {
		$message = \sprintf(
			'The stopwatch event "%s" is not recognized and cannot be stopped.',
			$name
		);

		return new self( $message );
	}
}
