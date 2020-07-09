<?php

namespace AmpProject\AmpWP\Tests\Instrumentation;

use AmpProject\AmpWP\Instrumentation\StopWatchEvent;
use PHPUnit\Framework\TestCase;

final class StopWatchEventTest extends TestCase {

	public function test_it_can_measure_time() {
		$stop_watch_event = new StopWatchEvent();
		$this->assertEquals( 0.0, $stop_watch_event->get_duration() );
		sleep( 1 );
		$stop_watch_event->stop();
		$this->assertGreaterThan( 1.0, $stop_watch_event->get_duration() );
	}
}
