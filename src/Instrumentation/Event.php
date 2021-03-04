<?php
/**
 * Class Event.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Instrumentation;

use AmpProject\AmpWP\Exception\InvalidEventProperties;

/**
 * A server-timing event.
 *
 * @package AmpProject\AmpWP
 * @since 2.0
 * @internal
 */
class Event {

	/**
	 * Event name.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Event description.
	 *
	 * @var string|null
	 */
	protected $description;

	/**
	 * Additional properties of the event.
	 *
	 * @var string[]
	 */
	protected $properties;

	/**
	 * Event constructor.
	 *
	 * @param string        $name        Event name.
	 * @param string|null   $description Optional. Event description.
	 * @param string[]|null $properties  Optional. Additional properties for the
	 *                                   event.
	 */
	public function __construct( $name, $description = null, $properties = [] ) {
		$this->name        = $name;
		$this->description = $description;
		$this->properties  = (array) $properties;
	}

	/**
	 * Get the name of the event.
	 *
	 * @return string Event name.
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Get the description of the event.
	 *
	 * @return string Event description.
	 */
	public function get_description() {
		return $this->description ?: '';
	}

	/**
	 * Add additional properties to the event.
	 *
	 * @param string[] $properties Properties to add.
	 * @throws InvalidEventProperties When the type of $properties or its
	 *                                elements is off.
	 */
	public function add_properties( $properties ) {
		if ( ! is_array( $properties ) ) {
			throw InvalidEventProperties::from_invalid_type( $properties );
		}

		foreach ( $properties as $key => $value ) {
			if ( ! is_string( $key ) ) {
				throw InvalidEventProperties::from_invalid_element_key_type( $key );
			}

			if ( ! is_scalar( $value ) ) {
				throw InvalidEventProperties::from_invalid_element_value_type( $value );
			}

			$this->properties[ $key ] = $value;
		}
	}

	/**
	 * Sanitize key to use it for an HTTP header label (alphanumeric and dashes/underscores only).
	 *
	 * @param string $key Unsanitized key.
	 * @return string Sanitized key.
	 */
	private function sanitize_key( $key ) {
		return preg_replace( '/[^a-zA-Z0-9_-]+/', '_', $key );
	}

	/**
	 * Get the server timing header string.
	 *
	 * @return string Server timing header string representing this event.
	 */
	public function get_header_string() {
		$property_strings = [];

		foreach ( $this->properties as $property => $value ) {
			if ( is_float( $value ) ) {
				$property_strings[] = sprintf(
					';%s="%.1f"',
					$this->sanitize_key( $property ),
					$value
				);
			} else {
				$property_strings[] = sprintf(
					';%s="%s"',
					$this->sanitize_key( $property ),
					addslashes( $value )
				);
			}
		}

		$event_string = $this->sanitize_key( $this->get_name() );

		$description = $this->get_description();
		if ( ! empty( $description ) ) {
			$event_string = sprintf(
				'%s;desc="%s"',
				$event_string,
				addslashes( $description )
			);
		}

		return $event_string . implode( $property_strings );
	}
}
