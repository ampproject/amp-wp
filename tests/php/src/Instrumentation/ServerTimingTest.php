<?php

namespace AmpProject\AmpWP\Tests\Instrumentation;

use AMP_HTTP;
use AmpProject\AmpWP\Instrumentation\Event;
use AmpProject\AmpWP\Instrumentation\EventWithDuration;
use AmpProject\AmpWP\Instrumentation\ServerTiming;
use AmpProject\AmpWP\QueryVar;
use AmpProject\AmpWP\Tests\DependencyInjectedTestCase;
use AmpProject\AmpWP\Tests\Helpers\AssertContainsCompatibility;
use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;

final class ServerTimingTest extends DependencyInjectedTestCase {

	use AssertContainsCompatibility;
	use PrivateAccess;

	/**
	 * @covers \AmpProject\AmpWP\Instrumentation\ServerTiming::register()
	 */
	public function test_it_can_be_hooked_into() {
		$server_timing = $this->injector->make( ServerTiming::class );
		$server_timing->register();
		$this->assertEquals( 10, has_action( 'amp_server_timing_start', [ $server_timing, 'start' ] ) );
		$this->assertEquals( 10, has_action( 'amp_server_timing_stop', [ $server_timing, 'stop' ] ) );
		$this->assertEquals( 10, has_action( 'amp_server_timing_log', [ $server_timing, 'log' ] ) );
		$this->assertEquals( 10, has_action( 'amp_server_timing_send', [ $server_timing, 'send' ] ) );
	}

	/**
	 * @covers \AmpProject\AmpWP\Instrumentation\ServerTiming::start()
	 * @covers \AmpProject\AmpWP\Instrumentation\ServerTiming::stop()
	 */
	public function test_it_can_record_events_with_duration_directly() {
		$server_timing = $this->injector->make( ServerTiming::class );
		$server_timing->start( 'event-1', 'Event N°1' );
		usleep( 100 * 1000 ); // 100ms.
		$server_timing->stop( 'event-1' );

		$events = $this->get_private_property( $server_timing, 'events' );
		$event  = $events['event-1'];

		$this->assertInstanceof( EventWithDuration::class, $event );
		$this->assertEquals( 'event-1', $event->get_name() );
		$this->assertEquals( 'Event N°1', $event->get_description() );
		$this->assertGreaterThan( 0.1, $event->get_duration() );
	}

	/**
	 * @covers \AmpProject\AmpWP\Instrumentation\ServerTiming::register()
	 * @covers \AmpProject\AmpWP\Instrumentation\ServerTiming::start()
	 * @covers \AmpProject\AmpWP\Instrumentation\ServerTiming::stop()
	 */
	public function test_it_can_record_events_with_duration_via_actions() {
		$server_timing = $this->injector->make( ServerTiming::class );
		$server_timing->register();
		do_action( 'amp_server_timing_start', 'event-2', 'Event N°2' );
		usleep( 100 * 1000 ); // 100ms.
		do_action( 'amp_server_timing_stop', 'event-2' );

		$events = $this->get_private_property( $server_timing, 'events' );
		$event  = $events['event-2'];

		$this->assertInstanceof( EventWithDuration::class, $event );
		$this->assertEquals( 'event-2', $event->get_name() );
		$this->assertEquals( 'Event N°2', $event->get_description() );
		$this->assertGreaterThan( 0.1, $event->get_duration() );
	}

	/**
	 * @covers \AmpProject\AmpWP\Instrumentation\ServerTiming::log()
	 */
	public function test_it_can_record_events_without_duration_directly() {
		$server_timing = $this->injector->make( ServerTiming::class );
		$server_timing->log( 'event-3', 'Event N°3' );

		$events = $this->get_private_property( $server_timing, 'events' );
		$event  = $events['event-3'];

		$this->assertInstanceof( Event::class, $event );
		$this->assertNotInstanceof( EventWithDuration::class, $event );
		$this->assertEquals( 'event-3', $event->get_name() );
		$this->assertEquals( 'Event N°3', $event->get_description() );
	}

	/**
	 * @covers \AmpProject\AmpWP\Instrumentation\ServerTiming::register()
	 * @covers \AmpProject\AmpWP\Instrumentation\ServerTiming::log()
	 */
	public function test_it_can_record_events_without_duration_via_actions() {
		$server_timing = $this->injector->make( ServerTiming::class );
		$server_timing->register();
		do_action( 'amp_server_timing_log', 'event-4', 'Event N°4' );

		$events = $this->get_private_property( $server_timing, 'events' );
		$event  = $events['event-4'];

		$this->assertInstanceof( Event::class, $event );
		$this->assertNotInstanceof( EventWithDuration::class, $event );
		$this->assertEquals( 'event-4', $event->get_name() );
		$this->assertEquals( 'Event N°4', $event->get_description() );
	}

	/**
	 * @covers \AmpProject\AmpWP\Instrumentation\ServerTiming::start()
	 * @covers \AmpProject\AmpWP\Instrumentation\ServerTiming::stop()
	 * @covers \AmpProject\AmpWP\Instrumentation\ServerTiming::log()
	 */
	public function test_it_can_forward_additional_properties_directly() {
		$server_timing = $this->injector->make( ServerTiming::class );
		$server_timing->start(
			'event-5',
			'Event N°5',
			[
				'prop-1' => 'val-1',
				'prop-2' => 'val-2',
			]
		);
		$server_timing->stop( 'event-5' );
		$server_timing->log(
			'event-6',
			'Event N°6',
			[
				'prop-3' => 'val-3',
				'prop-4' => 'val-4',
			]
		);

		$events  = $this->get_private_property( $server_timing, 'events' );
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

	/**
	 * @covers \AmpProject\AmpWP\Instrumentation\ServerTiming::register()
	 * @covers \AmpProject\AmpWP\Instrumentation\ServerTiming::start()
	 * @covers \AmpProject\AmpWP\Instrumentation\ServerTiming::stop()
	 * @covers \AmpProject\AmpWP\Instrumentation\ServerTiming::log()
	 */
	public function test_it_can_forward_additional_properties_via_actions() {
		$server_timing = $this->injector->make( ServerTiming::class );
		$server_timing->register();
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

		$events  = $this->get_private_property( $server_timing, 'events' );
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

	/**
	 * @covers \AmpProject\AmpWP\Instrumentation\ServerTiming::register()
	 * @covers \AmpProject\AmpWP\Instrumentation\ServerTiming::start()
	 * @covers \AmpProject\AmpWP\Instrumentation\ServerTiming::stop()
	 * @covers \AmpProject\AmpWP\Instrumentation\ServerTiming::get_header_string()
	 */
	public function test_it_can_return_a_header_string() {
		$server_timing = $this->injector->make( ServerTiming::class );
		$server_timing->register();
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

		$events = $this->get_private_property( $server_timing, 'events' );
		$event  = $events['event-9'];
		$event->set_duration( 3.14 );

		$this->assertStringContains( 'event-9;desc="Event N°9";prop-9="val-9";prop-10="val-10";dur="3.1"', $server_timing->get_header_string() );
	}

	/**
	 * @covers \AmpProject\AmpWP\Instrumentation\ServerTiming::start()
	 * @covers \AmpProject\AmpWP\Instrumentation\ServerTiming::stop()
	 * @covers \AmpProject\AmpWP\Instrumentation\ServerTiming::get_header_string()
	 * @covers \AmpProject\AmpWP\Instrumentation\ServerTiming::send()
	 */
	public function test_it_can_send_headers_directly() {
		$server_timing = $this->injector->make( ServerTiming::class );

		$server_timing->start( 'event-10', 'Event N°10' );
		$server_timing->stop( 'event-10' );

		$events = $this->get_private_property( $server_timing, 'events' );
		$event  = $events['event-10'];
		$event->set_duration( 12345.67890 );

		$server_timing->send();

		$this->assertStringContainsString(
			[
				'name'        => 'Server-Timing',
				'value'       => 'event-10;desc="Event N°10";dur="12345.7"',
				'replace'     => true,
				'status_code' => null,
			],
			AMP_HTTP::$headers_sent
		);
	}

	/**
	 * @covers \AmpProject\AmpWP\Instrumentation\ServerTiming::register()
	 * @covers \AmpProject\AmpWP\Instrumentation\ServerTiming::start()
	 * @covers \AmpProject\AmpWP\Instrumentation\ServerTiming::stop()
	 * @covers \AmpProject\AmpWP\Instrumentation\ServerTiming::get_header_string()
	 * @covers \AmpProject\AmpWP\Instrumentation\ServerTiming::send()
	 */
	public function test_it_can_send_headers_via_action() {
		$server_timing = $this->injector->make( ServerTiming::class );
		$server_timing->register();

		do_action( 'amp_server_timing_start', 'event-11', 'Event N°11' );
		do_action( 'amp_server_timing_stop', 'event-11' );

		$events = $this->get_private_property( $server_timing, 'events' );
		$event  = $events['event-11'];
		$event->set_duration( 3.14 );

		do_action( 'amp_server_timing_send' );

		$this->assertStringContainsString(
			[
				'name'        => 'Server-Timing',
				'value'       => 'event-11;desc="Event N°11";dur="3.1"',
				'replace'     => true,
				'status_code' => null,
			],
			AMP_HTTP::$headers_sent
		);
	}

	public function test_it_sends_restricted_output_by_default() {
		$server_timing = $this->injector->make( ServerTiming::class );
		$this->assertFalse(
			$this->get_private_property(
				$server_timing,
				'verbose'
			)
		);
	}

	public function test_it_sends_restricted_output_with_query_var_but_not_logged_in() {
		$_GET[ QueryVar::VERBOSE_SERVER_TIMING ] = '1';
		$server_timing                           = $this->injector->make( ServerTiming::class );
		$this->assertFalse(
			$this->get_private_property(
				$server_timing,
				'verbose'
			)
		);
	}

	public function test_it_sends_restricted_output_when_logged_in_but_no_query_var() {
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$server_timing = $this->injector->make( ServerTiming::class );
		$this->assertFalse(
			$this->get_private_property(
				$server_timing,
				'verbose'
			)
		);
	}

	public function test_it_sends_restricted_output_with_query_var_and_logged_in() {
		$_GET[ QueryVar::VERBOSE_SERVER_TIMING ] = '1';
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'subscriber' ] ) );
		$server_timing = $this->injector->make( ServerTiming::class );
		$this->assertFalse(
			$this->get_private_property(
				$server_timing,
				'verbose'
			)
		);
	}

	public function test_it_sends_verbose_output_with_query_var_and_logged_in() {
		$_GET[ QueryVar::VERBOSE_SERVER_TIMING ] = '1';
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$server_timing = $this->injector->make( ServerTiming::class );
		$this->assertTrue(
			$this->get_private_property(
				$server_timing,
				'verbose'
			)
		);
	}

	public function test_it_restricts_output_in_non_verbose_mode() {
		$server_timing = $this->injector->make( ServerTiming::class );

		$server_timing->start( 'main-event', 'Main Event', [], false );
		$server_timing->stop( 'main-event' );
		$server_timing->start( 'verbose-event', 'Verbose Event', [], true );
		$server_timing->stop( 'verbose-event' );

		$events     = $this->get_private_property( $server_timing, 'events' );
		$main_event = $events['main-event'];
		$main_event->set_duration( 1.2 );
		$this->assertStringNotContainsString( 'verbose-event', $events );

		$server_timing->send();

		$this->assertStringContainsString(
			[
				'name'        => 'Server-Timing',
				'value'       => 'main-event;desc="Main Event";dur="1.2"',
				'replace'     => true,
				'status_code' => null,
			],
			AMP_HTTP::$headers_sent
		);
	}

	public function test_it_doesnt_restrict_output_in_verbose_mode() {
		$server_timing = $this->injector->make( ServerTiming::class );
		$this->set_private_property( $server_timing, 'verbose', true );

		$server_timing->start( 'main-event', 'Main Event', [], false );
		$server_timing->stop( 'main-event' );
		$server_timing->start( 'verbose-event', 'Verbose Event', [], true );
		$server_timing->stop( 'verbose-event' );

		$events     = $this->get_private_property( $server_timing, 'events' );
		$main_event = $events['main-event'];
		$main_event->set_duration( 1.2 );
		$verbose_event = $events['verbose-event'];
		$verbose_event->set_duration( 3.4 );

		$server_timing->send();

		$this->assertStringContainsString(
			[
				'name'        => 'Server-Timing',
				'value'       => 'main-event;desc="Main Event";dur="1.2",verbose-event;desc="Verbose Event";dur="3.4"',
				'replace'     => true,
				'status_code' => null,
			],
			AMP_HTTP::$headers_sent
		);
	}
}
