<?php
/**
 * Class FailedToRegisterBackgroundTask.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Exception;

use RuntimeException;

/**
 * Exception thrown when registration of a background task failed.
 *
 * @package AmpProject\AmpWP
 */
final class FailedToRegisterBackgroundTask extends RuntimeException implements AmpWPException {


	/**
	 * Instantiate the exception for an event that was supposed to be scheduled.
	 *
	 * @param string $event Event that was supposed to be scheduled.
	 *
	 * @return self
	 */
	public static function for_scheduled_event( $event ) {
		$message = "Failed to schedule the event '{$event}' with WordPress.";

		return new self( $message );
	}
}
