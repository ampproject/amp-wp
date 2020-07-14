<?php

namespace AmpProject\AmpWP\Tests\Instrumentation;

use AmpProject\AmpWP\Exception\InvalidEventProperties;
use AmpProject\AmpWP\Instrumentation\Event;
use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;
use PHPUnit\Framework\TestCase;
use stdClass;

final class EventTest extends TestCase {

	use PrivateAccess;

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

	public function data_add_properties_typing() {
		return [
			// Type of $properties collection.
			'null type'                  => [ null, InvalidEventProperties::class, 'but is of type NULL' ],
			'bool type'                  => [ true, InvalidEventProperties::class, 'but is of type bool' ],
			'string type'                => [ 'a string', InvalidEventProperties::class, 'but is of type string' ],
			'int type'                   => [ 42, InvalidEventProperties::class, 'but is of type int' ],
			'float type'                 => [ 3.14, InvalidEventProperties::class, 'but is of type double' ],
			'object type'                => [ new stdClass(), InvalidEventProperties::class, 'but is of type stdClass' ],
			'key-value object type'      => [ (object) [ 'key' => 'value' ], InvalidEventProperties::class, 'but is of type stdClass' ],
			'array with single value'    => [ [ 'key' => 'value' ], false ],
			'array with multiple values' => [
				[
					'key_1' => 'value_1',
					'key_2' => 'value_2',
				],
				false,
			],
			'empty array'                => [ [], false ],

			// Type of $properties element key.
			'no key'                     => [ [ 'value' ], InvalidEventProperties::class, 'but found an element key of type integer' ],
			'bool key'                   => [ [ true => 'value' ], InvalidEventProperties::class, 'but found an element key of type integer' ],
			'string key'                 => [ [ 'key' => 'value' ], false ],
			'int key'                    => [ [ 42 => 'value' ], InvalidEventProperties::class, 'but found an element key of type int' ],
			'float key'                  => [ [ 3.14 => 'value' ], InvalidEventProperties::class, 'but found an element key of type integer' ],

			// Type of $properties element value.
			'null value'                 => [ [ 'key' => null ], InvalidEventProperties::class, 'but found an element value of type NULL' ],
			'bool value'                 => [ [ 'key' => true ], false ],
			'string value'               => [ [ 'key' => 'a string' ], false ],
			'int value'                  => [ [ 'key' => 42 ], false ],
			'float value'                => [ [ 'key' => 3.14 ], false ],
			'object value'               => [ [ 'key' => new stdClass() ], InvalidEventProperties::class, 'but found an element value of type stdClass' ],
			'key-value object value'     => [ [ 'key' => (object) [ 'key' => 'value' ] ], InvalidEventProperties::class, 'but found an element value of type stdClass' ],
		];
	}

	/**
	 * Test whether add_properties() correctly asserts the typing.
	 *
	 * @dataProvider data_add_properties_typing()
	 *
	 * @param mixed $properties Properties to add to the event.
	 * @param string|false $exception Exception class to expect, or false if no exceptions should be thrown.
	 * @param string|false $message Optional. Exception message to expect, or false if it shouldn't be checked.
	 */
	public function test_add_properties_typing( $properties, $exception, $message = false ) {
		$event = new Event( 'test' );

		if ( $exception ) {
			$this->expectException( $exception );
			if ( $message ) {
				$this->expectExceptionMessage( $message );
			}
		}

		$event->add_properties( $properties );

		$this->assertEquals( $properties, $this->get_private_property( $event, 'properties' ) );
	}
}
