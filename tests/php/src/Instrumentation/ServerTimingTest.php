<?php

namespace AmpProject\AmpWP\Tests\Instrumentation;

use AmpProject\AmpWP\Instrumentation\ServerTiming;
use AmpProject\AmpWP\Instrumentation\StopWatch;
use WP_UnitTestCase;

final class ServerTimingTest extends WP_UnitTestCase {

	/**
	 * @covers \AmpProject\AmpWP\Instrumentation\ServerTiming::register()
	 */
	public function test_it_can_be_hooked_into() {
		$mock_stop_watch = $this->createMock( StopWatch::class );
		$server_timing   = new ServerTiming( $mock_stop_watch );

		$server_timing->register();

		$this->assertEquals( 10, has_action( 'amp_server_timing_start' ), [ $server_timing, 'start' ] );
		$this->assertEquals( 10, has_action( 'amp_server_timing_stop' ), [ $server_timing, 'stop' ] );
		$this->assertEquals( 10, has_action( 'amp_server_timing_log' ), [ $server_timing, 'log' ] );
		$this->assertEquals( 10, has_action( 'amp_server_timing_send' ), [ $server_timing, 'send' ] );
	}
}
