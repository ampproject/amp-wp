<?php

namespace AmpProject\AmpWP\Tests\Instrumentation;

use AmpProject\AmpWP\Instrumentation\EventWithDuration;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

final class EventWithDurationTest extends TestCase {

	public function data_get_header_string() {
		return [
			[ 'event-1', '', null, 0.0, 'event-1;dur="0.0"' ],
			[ 'event-2', 'Event N°2', null, 3.14, 'event-2;desc="Event N°2";dur="3.1"' ],
			[ 'event-3', 'Event N°3', [], 12345.67890, 'event-3;desc="Event N°3";dur="12345.7"' ],
			[ 'event-4', 'Event N°4', [ 'some_label' => 'some_value' ], 0.567, 'event-4;desc="Event N°4";some_label="some_value";dur="0.6"' ],
		];
	}

	/**
	 * Test the header string that the event with duration object produces.
	 *
	 * @dataProvider data_get_header_string()
	 *
	 * @covers       \AmpProject\AmpWP\Instrumentation\EventWithDuration::get_name()
	 * @covers       \AmpProject\AmpWP\Instrumentation\EventWithDuration::get_description()
	 * @covers       \AmpProject\AmpWP\Instrumentation\EventWithDuration::get_duration()
	 * @covers       \AmpProject\AmpWP\Instrumentation\EventWithDuration::get_header_string()
	 *
	 * @param string     $name        Event name.
	 * @param string     $description Event description.
	 * @param array|null $properties  Associative array of properties
	 *                                or null to not use additional properties.
	 * @param float      $duration    Event duration.
	 * @param string     $expected    Expected header string.
	 */
	public function test_get_header_string( $name, $description, $properties, $duration, $expected ) {
		$event = new EventWithDuration( $name, $description, $properties, $duration );

		$this->assertEquals( $name, $event->get_name() );
		$this->assertEquals( $description, $event->get_description() );
		$this->assertEquals( $duration, $event->get_duration() );
		$this->assertEquals( $expected, $event->get_header_string() );
	}

	/**
	 * Test setting the duration directly.
	 *
	 * @covers \AmpProject\AmpWP\Instrumentation\EventWithDuration::set_duration()
	 * @covers \AmpProject\AmpWP\Instrumentation\EventWithDuration::get_header_string()
	 */
	public function test_set_duration() {
		$event = new EventWithDuration( 'my_event' );
		$event->set_duration( 12345.67890 );
		$this->assertEquals( 12345.67890, $event->get_duration() );
		$this->assertEquals( 'my_event;dur="12345.7"', $event->get_header_string() );
	}
}
