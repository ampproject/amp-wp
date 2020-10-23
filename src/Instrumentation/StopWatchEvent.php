<?php
/**
 * Class StopWatchEvent.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Instrumentation;

/**
 * Record the timing of a single event.
 *
 * @package AmpProject\AmpWP
 * @since 2.0
 * @internal
 */
final class StopWatchEvent {

	/**
	 * Start time in milliseconds.
	 *
	 * @var float
	 */
	private $start;

	/**
	 * End time in milliseconds.
	 *
	 * @var float|null
	 */
	private $end;

	/**
	 * StopWatchEvent constructor.
	 */
	public function __construct() {
		$this->start = $this->get_now();
	}

	/**
	 * Stop the event.
	 */
	public function stop() {
		$this->end = $this->get_now();
	}

	/**
	 * Get the duration of the event in milliseconds.
	 *
	 * @return float Duration in milliseconds.
	 */
	public function get_duration() {
		if ( null === $this->end ) {
			return 0.0;
		}

		return $this->end - $this->start;
	}

	/**
	 * Get the current time in milliseconds.
	 *
	 * @return float Current time in milliseconds.
	 */
	private function get_now() {
		return microtime( true ) * 1000;
	}
}
