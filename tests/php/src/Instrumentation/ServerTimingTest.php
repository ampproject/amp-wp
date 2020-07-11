<?php

namespace AmpProject\AmpWP\Tests\Instrumentation;

use AMP_HTTP;
use AmpProject\AmpWP\Instrumentation\Event;
use AmpProject\AmpWP\Instrumentation\EventWithDuration;
use AmpProject\AmpWP\Instrumentation\ServerTiming;
use AmpProject\AmpWP\Instrumentation\StopWatch;
use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;
use WP_UnitTestCase;

final class ServerTimingTest extends WP_UnitTestCase {

	use PrivateAccess;

	/**
	 * @var ServerTiming
	 */
	private $server_timing;

	public function setUp() {
		$this->server_timing = new ServerTiming( new StopWatch() );
		$this->server_timing->register();
	}

	/**
	 * @covers \AmpProject\AmpWP\Instrumentation\ServerTiming::register()
	 */
	public function test_it_can_be_hooked_into() {
		$this->assertEquals( 10, has_action( 'amp_server_timing_start' ), [ $this->server_timing, 'start' ] );
		$this->assertEquals( 10, has_action( 'amp_server_timing_stop' ), [ $this->server_timing, 'stop' ] );
		$this->assertEquals( 10, has_action( 'amp_server_timing_log' ), [ $this->server_timing, 'log' ] );
		$this->assertEquals( 10, has_action( 'amp_server_timing_send' ), [ $this->server_timing, 'send' ] );
	}

	public function test_it_can_record_events_with_duration_directly() {
		$this->server_timing->start( 'event-1', 'Event N°1' );
		usleep( 100 * 1000 ); // 100ms.
		$this->server_timing->stop( 'event-1' );

		$events = $this->get_private_property( $this->server_timing, 'events' );
		$event  = $events['event-1'];

		$this->assertInstanceof( EventWithDuration::class, $event );
		$this->assertEquals( 'event-1', $event->get_name() );
		$this->assertEquals( 'Event N°1', $event->get_description() );
		$this->assertGreaterThan( 0.1, $event->get_duration() );
	}

	public function test_it_can_record_events_with_duration_via_actions() {
		do_action( 'amp_server_timing_start', 'event-2', 'Event N°2' );
		usleep( 100 * 1000 ); // 100ms.
		do_action( 'amp_server_timing_stop', 'event-2' );

		$events = $this->get_private_property( $this->server_timing, 'events' );
		$event  = $events['event-2'];

		$this->assertInstanceof( EventWithDuration::class, $event );
		$this->assertEquals( 'event-2', $event->get_name() );
		$this->assertEquals( 'Event N°2', $event->get_description() );
		$this->assertGreaterThan( 0.1, $event->get_duration() );
	}

	public function test_it_can_record_events_without_duration_directly() {
		$this->server_timing->log( 'event-3', 'Event N°3' );

		$events = $this->get_private_property( $this->server_timing, 'events' );
		$event  = $events['event-3'];

		$this->assertInstanceof( Event::class, $event );
		$this->assertNotInstanceof( EventWithDuration::class, $event );
		$this->assertEquals( 'event-3', $event->get_name() );
		$this->assertEquals( 'Event N°3', $event->get_description() );
	}

	public function test_it_can_record_events_without_duration_via_actions() {
		do_action( 'amp_server_timing_log', 'event-4', 'Event N°4' );

		$events = $this->get_private_property( $this->server_timing, 'events' );
		$event  = $events['event-4'];

		$this->assertInstanceof( Event::class, $event );
		$this->assertNotInstanceof( EventWithDuration::class, $event );
		$this->assertEquals( 'event-4', $event->get_name() );
		$this->assertEquals( 'Event N°4', $event->get_description() );
	}

	public function test_it_can_forward_additional_properties_directly() {
		$this->server_timing->start(
			'event-5',
			'Event N°5',
			[
				'prop-1' => 'val-1',
				'prop-2' => 'val-2',
			]
		);
		$this->server_timing->stop( 'event-5' );
		$this->server_timing->log(
			'event-6',
			'Event N°6',
			[
				'prop-3' => 'val-3',
				'prop-4' => 'val-4',
			]
		);

		$events  = $this->get_private_property( $this->server_timing, 'events' );
		$event_5 = $events['event-5'];
		$event_6 = $events['event-6'];

		$this->assertEquals(
			[
				'prop-1' => 'val-1',
				'prop-2' => 'val-2',
			],
			$this->get_private_property( $event_5, 'properties' )
		);
		$this->assertEquals(
			[
				'prop-3' => 'val-3',
				'prop-4' => 'val-4',
			],
			$this->get_private_property( $event_6, 'properties' )
		);
	}

	public function test_it_can_forward_additional_properties_via_actions() {
		do_action(
			'amp_server_timing_start',
			'event-7',
			'Event N°7',
			[
				'prop-5' => 'val-5',
				'prop-6' => 'val-6',
			]
		);
		do_action( 'amp_server_timing_stop', 'event-7' );
		do_action(
			'amp_server_timing_log',
			'event-8',
			'Event N°8',
			[
				'prop-7' => 'val-7',
				'prop-8' => 'val-8',
			]
		);

		$events  = $this->get_private_property( $this->server_timing, 'events' );
		$event_7 = $events['event-7'];
		$event_8 = $events['event-8'];

		$this->assertEquals(
			[
				'prop-5' => 'val-5',
				'prop-6' => 'val-6',
			],
			$this->get_private_property( $event_7, 'properties' )
		);
		$this->assertEquals(
			[
				'prop-7' => 'val-7',
				'prop-8' => 'val-8',
			],
			$this->get_private_property( $event_8, 'properties' )
		);
	}

	public function test_it_can_return_a_header_string() {
		do_action(
			'amp_server_timing_start',
			'event-9',
			'Event N°9',
			[
				'prop-9'  => 'val-9',
				'prop-10' => 'val-10',
			]
		);
		do_action( 'amp_server_timing_stop', 'event-9' );

		$events = $this->get_private_property( $this->server_timing, 'events' );
		$event  = $events['event-9'];
		$event->set_duration( 3.14 );

		$this->assertEquals( 'event-9;desc="Event N°9";prop-9="val-9";prop-10="val-10";dur="3.1"', $this->server_timing->get_header_string() );
	}

	public function test_it_can_send_headers_directly() {
		$this->server_timing->start( 'event-10', 'Event N°10' );
		$this->server_timing->stop( 'event-10' );

		$events = $this->get_private_property( $this->server_timing, 'events' );
		$event  = $events['event-10'];
		$event->set_duration( 12345.67890 );

		$this->server_timing->send();

		$this->assertContains(
			[
				'name'        => 'Server-Timing',
				'value'       => 'event-10;desc="Event N°10";dur="12345.7"',
				'replace'     => true,
				'status_code' => null,
			],
			AMP_HTTP::$headers_sent
		);
	}

	public function test_it_can_send_headers_via_action() {
		do_action( 'amp_server_timing_start', 'event-11', 'Event N°11' );
		do_action( 'amp_server_timing_stop', 'event-11' );

		$events = $this->get_private_property( $this->server_timing, 'events' );
		$event  = $events['event-11'];
		$event->set_duration( 3.14 );

		do_action( 'amp_server_timing_send' );

		$this->assertContains(
			[
				'name'        => 'Server-Timing',
				'value'       => 'event-11;desc="Event N°11";dur="3.1"',
				'replace'     => true,
				'status_code' => null,
			],
			AMP_HTTP::$headers_sent
		);
	}
}
