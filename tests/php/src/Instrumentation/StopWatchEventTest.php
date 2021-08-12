<?php

namespace AmpProject\AmpWP\Tests\Instrumentation;

use AmpProject\AmpWP\Instrumentation\StopWatchEvent;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

final class StopWatchEventTest extends TestCase {

	public function test_it_can_measure_time() {
		$stop_watch_event = new StopWatchEvent();
		$this->assertEquals( 0.0, $stop_watch_event->get_duration() );
		usleep( 100 * 1000 ); // 100ms
		$stop_watch_event->stop();
		$this->assertGreaterThan( 0.1, $stop_watch_event->get_duration() );
	}
}
