<?php
/**
 * Class StopWatch.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Instrumentation;

use AmpProject\AmpWP\Exception\InvalidStopwatchEvent;

/**
 * Record the timing of multiple events.
 *
 * @package AmpProject\AmpWP
 * @since 2.0
 * @internal
 */
final class StopWatch {

	/**
	 * Collection of named events that the stopwatch is tracking.
	 *
	 * @var StopWatchEvent[]
	 */
	private $events = [];

	/**
	 * Start a named event.
	 *
	 * @param string $name Name of the event to start.
	 */
	public function start( $name ) {
		$this->events[ $name ] = new StopWatchEvent();
	}

	/**
	 * Stop a named event.
	 *
	 * @param string $name Name of the event to stop.
	 * @return StopWatchEvent Completed stopwatch event.
	 * @throws InvalidStopwatchEvent If an unknown event name is provided.
	 */
	public function stop( $name ) {
		if ( ! array_key_exists( $name, $this->events ) ) {
			throw InvalidStopwatchEvent::from_name_to_stop( $name );
		}

		$event = $this->events[ $name ];
		$event->stop();

		return $event;
	}
}
