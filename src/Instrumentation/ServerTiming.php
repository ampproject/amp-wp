<?php
/**
 * Class ServerTiming.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Instrumentation;

use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;

/**
 * Collect Server-Timing metrics.
 *
 * @package AmpProject\AmpWP
 */
final class ServerTiming implements Service, Registerable {

	/**
	 * Stop watch to use to recording the duration of events.
	 *
	 * @var StopWatch
	 */
	private $stopwatch;

	/**
	 * Tracked events.
	 *
	 * @var Event[]
	 */
	private $events = [];

	/**
	 * Durations for recorded events.
	 *
	 * @var float[]
	 */
	private $durations = [];

	/**
	 * ServerTiming constructor.
	 *
	 * @param StopWatch $stopwatch Stop watch to use to recording the duration
	 *                             of events.
	 */
	public function __construct( StopWatch $stopwatch ) {
		$this->stopwatch = $stopwatch;
	}

	/**
	 * Register the service.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'amp_server_timing_start', [ $this, 'start' ], 10, 3 );
		add_action( 'amp_server_timing_stop', [ $this, 'stop' ], 10, 1 );
		add_action( 'amp_server_timing_log', [ $this, 'log' ], 10, 3 );
	}

	/**
	 * Start recording an event.
	 *
	 * @param string      $event_name        Name of the event to record.
	 * @param string|null $event_description Optional. Description of the event
	 *                                       to record. Defaults to null.
	 * @param bool        $verbose_only      Optional. Whether to only show the
	 *                                       event in verbose mode. Defaults to
	 *                                       false.
	 */
	public function start( $event_name, $event_description = null, $verbose_only = false ) {
		$this->events[ $event_name ] = new EventWithDuration(
			$event_name,
			$event_description
		);

		$this->stopwatch->start( $event_name );
	}

	/**
	 * Stop recording an event.
	 *
	 * @param string $event_name Name of the event to stop the recording of.
	 */
	public function stop( $event_name ) {
		if ( ! array_key_exists( $event_name, $this->events ) ) {
			return;
		}

		$event = $this->stopwatch->stop( $event_name );

		if ( ! $event instanceof EventWithDuration ) {
			// TODO: throw exception.
		}

		$this->events[ $event_name ]->set_duration( $event->get_duration() );
	}

	/**
	 * Log an event that does not have a duration.
	 *
	 * @param string $event_name        Name of the event to log.
	 * @param string $event_description Description of the event to log.
	 * @param bool   $verbose_only      Whether to only show the event in
	 *                                  verbose mode.
	 */
	public function log( $event_name, $event_description = '', $verbose_only = false ) {
		$this->events[ $event_name ] = new Event(
			$event_name,
			$event_description
		);
	}

	public function get_header_string() {
		implode(
			', ',
			array_map(
				static function ( $event ) {
					return $event->get_header_string();
				},
				$this->events
			)
		);
	}
}
