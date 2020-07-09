<?php

namespace AmpProject\AmpWP\Tests\Instrumentation;

use AmpProject\AmpWP\Instrumentation\Event;
use PHPUnit\Framework\TestCase;

final class EventTest extends TestCase {

	public function data_get_header_string() {
		return [
			[ 'event-1', '', null, 'event-1' ],
			[ 'event-2', 'Event N°2', null, 'event-2;desc="Event N°2"' ],
			[ 'event-3', 'Event N°3', [], 'event-3;desc="Event N°3"' ],
			[ 'event-4', 'Event N°4', [ 'some_label' => 'some_value' ], 'event-4;desc="Event N°4";some_label="some_value"' ],
			[ 'event-5', 'Event N°5', [ 'float_label' => 3.14 ], 'event-5;desc="Event N°5";float_label="3.1"' ],
			[
				'event-6',
				'Event N°6',
				[
					'first_label'  => 'first_value',
					'second_label' => 3.14,
				],
				'event-6;desc="Event N°6";first_label="first_value";second_label="3.1"',
			],
		];
	}

	/**
	 * Test the header string that the event object produces.
	 *
	 * @dataProvider data_get_header_string()
	 *
	 * @covers       \AmpProject\AmpWP\Instrumentation\Event::get_name()
	 * @covers       \AmpProject\AmpWP\Instrumentation\Event::get_description()
	 * @covers       \AmpProject\AmpWP\Instrumentation\Event::add_properties()
	 * @covers       \AmpProject\AmpWP\Instrumentation\Event::get_header_string()
	 *
	 * @param string     $name        Event name.
	 * @param string     $description Event description.
	 * @param array|null $properties  Optional. Associative array of properties
	 *                                or null to not use additional properties.
	 * @param string     $expected    Expected header string.
	 */
	public function test_get_header_string( $name, $description, $properties, $expected ) {
		$event = new Event( $name, $description );

		if ( null !== $properties ) {
			$event->add_properties( $properties );
		}

		$this->assertEquals( $name, $event->get_name() );
		$this->assertEquals( $description, $event->get_description() );
		$this->assertEquals( $expected, $event->get_header_string() );
	}
}
