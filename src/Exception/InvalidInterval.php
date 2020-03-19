<?php
/**
 * Class InvalidInterval.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Exception;

use InvalidArgumentException;

/**
 * Exception thrown when an invalid interval was used for a background task.
 *
 * @package AmpProject\AmpWP
 */
final class InvalidInterval extends InvalidArgumentException implements AmpWPException {


	/**
	 * Instantiate the exception for a string that was not an existing interval name.
	 *
	 * @param mixed $value String that was supposed to be a name of an existing interval.
	 *
	 * @return self
	 */
	public static function for_invalid_type( $value ) {
		$type = is_object( $value ) ? get_class( $value ) : gettype( $value );

		$message = "Trying to register an event with an invalid interval of type '{$type}'. The interval needs to be either a string matching one of the default interval names, or a positive integer as the duration in seconds.";

		return new self( $message );
	}

	/**
	 * Instantiate the exception for a string that was not an existing interval name.
	 *
	 * @param int $duration String that was supposed to be a name of an existing interval.
	 *
	 * @return self
	 */
	public static function for_invalid_duration( $duration ) {
		$message = "Trying to register an event with an invalid duration {$duration}. The duration needs to be an integer greater than zero.";

		return new self( $message );
	}

	/**
	 * Instantiate the exception for a string that was not an existing interval name.
	 *
	 * @param string $name String that was supposed to be a name of an existing interval.
	 *
	 * @return self
	 */
	public static function for_unknown_name( $name ) {
		$message = "Trying to register an event with an unknown interval '{$name}'. The name should be one of 'hourly', 'twicedaily' or 'daily'.";

		return new self( $message );
	}
}
