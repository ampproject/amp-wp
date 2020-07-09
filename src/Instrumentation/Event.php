<?php
/**
 * Class EventWithDuration.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Instrumentation;

/**
 * A server-timing event.
 *
 * @package AmpProject\AmpWP
 */
class Event {

	/**
	 * Event name.
	 *
	 * @var string
	 */
	private $name;

	/**
	 * Event description.
	 *
	 * @var string|null
	 */
	private $description;

	/**
	 * Additional properties of the event.
	 *
	 * @var string[]
	 */
	private $properties;

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
	 */
	public function add_properties( $properties ) {
		$this->properties = array_merge(
			(array) $properties,
			$this->properties
		);
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
					addslashes( $property ),
					$value
				);
			} else {
				$property_strings[] = sprintf(
					';%s="%s"',
					addslashes( $property ),
					addslashes( $value )
				);
			}
		}

		$event_string = addslashes( $this->get_name() );

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
