<?php
/**
 * Exception InvalidEventProperties.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Exception;

use InvalidArgumentException;

/**
 * Exception thrown when an invalid properties are added to an Event.
 *
 * @since 2.0
 * @internal
 */
final class InvalidEventProperties
	extends InvalidArgumentException
	implements AmpWpException {

	/**
	 * Create a new instance of the exception for a properties value that has
	 * the wrong type.
	 *
	 * @param mixed $properties Properties value that has the wrong type.
	 *
	 * @return self
	 */
	public static function from_invalid_type( $properties ) {
		$type = is_object( $properties )
			? get_class( $properties )
			: gettype( $properties );

		$message = sprintf(
			'The properties argument for adding properties to an event needs to be an array, but is of type %s',
			$type
		);

		return new self( $message );
	}

	/**
	 * Create a new instance of the exception for a properties value that has
	 * the wrong key type for one or more of its elements.
	 *
	 * @param mixed $property Property element that has the wrong type.
	 *
	 * @return self
	 */
	public static function from_invalid_element_key_type( $property ) {
		$type = is_object( $property )
			? get_class( $property )
			: gettype( $property );

		$message = sprintf(
			'Each property element key for adding properties to an event needs to of type string, but found an element key of type %s',
			$type
		);

		return new self( $message );
	}

	/**
	 * Create a new instance of the exception for a properties value that has
	 * the wrong value type for one or more of its elements.
	 *
	 * @param mixed $property Property element that has the wrong type.
	 *
	 * @return self
	 */
	public static function from_invalid_element_value_type( $property ) {
		$type = is_object( $property )
			? get_class( $property )
			: gettype( $property );

		$message = sprintf(
			'Each property element value for adding properties to an event needs to be a scalar value, but found an element value of type %s',
			$type
		);

		return new self( $message );
	}
}
