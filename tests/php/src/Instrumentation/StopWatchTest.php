<?php

namespace AmpProject\AmpWP\Tests\Instrumentation;

use AmpProject\AmpWP\Instrumentation\StopWatch;
use PHPUnit\Framework\TestCase;

final class StopWatchTest extends TestCase {

	public function test_it_can_measure_a_single_event() {
		$stop_watch = new StopWatch();
		$stop_watch->start( 'single' );
		usleep( 100 * 1000 ); // 100ms
		$stop_watch_event = $stop_watch->stop( 'single' );
		$this->assertGreaterThan( 0.1, $stop_watch_event->get_duration() );
	}

	public function test_it_can_measure_multiple_events() {
		$stop_watch = new StopWatch();
		$stop_watch->start( 'first' );
		$stop_watch->start( 'second' );
		$second_stop_watch_event = $stop_watch->stop( 'second' );
		usleep( 100 * 1000 ); // 100ms
		$first_stop_watch_event = $stop_watch->stop( 'first' );
		$this->assertGreaterThan( 0.1, $first_stop_watch_event->get_duration() );
		$this->assertLessThan( 0.1, $second_stop_watch_event->get_duration() );
	}
}
