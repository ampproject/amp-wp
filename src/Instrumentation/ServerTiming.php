<?php
/**
 * Class ServerTiming.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Instrumentation;

use AMP_HTTP;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;

/**
 * Collect Server-Timing metrics.
 *
 * @package AmpProject\AmpWP
 * @since 2.0
 * @internal
 */
final class ServerTiming implements Service, Registerable, Delayed {

	/**
	 * Stop watch to use to recording the duration of events.
	 *
	 * @var StopWatch
	 */
	private $stopwatch;

	/**
	 * Whether to track all events, or only the non-verbose ones.
	 *
	 * @var bool
	 */
	private $verbose;

	/**
	 * Tracked events.
	 *
	 * @var Event[]
	 */
	private $events = [];

	/**
	 * Get the action to use for registering the service.
	 *
	 * @return string Registration action to use.
	 */
	public static function get_registration_action() {
		// Delayed because we need to access is_user_logged_in().
		return 'init';
	}

	/**
	 * ServerTiming constructor.
	 *
	 * @param StopWatch $stopwatch Stop watch to use to recording the duration
	 *                             of events.
	 * @param bool      $verbose   Optional. Whether to track all events, or
	 *                             only the non-verbose ones.
	 */
	public function __construct( StopWatch $stopwatch, $verbose = false ) {
		$this->stopwatch = $stopwatch;
		$this->verbose   = $verbose;
	}

	/**
	 * Register the service.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'amp_server_timing_start', [ $this, 'start' ], 10, 4 );
		add_action( 'amp_server_timing_stop', [ $this, 'stop' ], 10, 1 );
		add_action( 'amp_server_timing_log', [ $this, 'log' ], 10, 4 );
		add_action( 'amp_server_timing_send', [ $this, 'send' ], 10, 0 );
	}

	/**
	 * Start recording an event.
	 *
	 * @param string      $event_name        Name of the event to record.
	 * @param string|null $event_description Optional. Description of the event
	 *                                       to record. Defaults to null.
	 * @param string[]    $properties        Optional. Additional properties to add
	 *                                       to the logged record.
	 * @param bool        $verbose_only      Optional. Whether to only show the
	 *                                       event in verbose mode. Defaults to
	 *                                       false.
	 */
	public function start( $event_name, $event_description = null, $properties = [], $verbose_only = false ) {
		if ( $verbose_only && ! $this->verbose ) {
			return;
		}

		$this->events[ $event_name ] = new EventWithDuration(
			$event_name,
			$event_description,
			$properties
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

		$stopwatch_event = $this->stopwatch->stop( $event_name );

		if ( $this->events[ $event_name ] instanceof EventWithDuration ) {
			$this->events[ $event_name ]->set_duration( $stopwatch_event->get_duration() );
		}
	}

	/**
	 * Log an event that does not have a duration.
	 *
	 * @param string   $event_name        Name of the event to log.
	 * @param string   $event_description Description of the event to log.
	 * @param string[] $properties        Optional. Additional properties to add
	 *                                    to the logged record.
	 * @param bool     $verbose_only      Optional. Whether to only show the
	 *                                    event in verbose mode.
	 */
	public function log( $event_name, $event_description = '', $properties = [], $verbose_only = false ) {
		if ( $verbose_only && ! $this->verbose ) {
			return;
		}

		$this->events[ $event_name ] = new Event(
			$event_name,
			$event_description,
			$properties
		);
	}

	/**
	 * Send the server-timing header.
	 */
	public function send() {
		AMP_HTTP::send_header( 'Server-Timing', $this->get_header_string() );
	}

	/**
	 * Get the server timing header string for all collected events.
	 *
	 * @return string Server timing header string.
	 */
	public function get_header_string() {
		return implode(
			',',
			array_map(
				static function ( $event ) {
					return $event->get_header_string();
				},
				$this->events
			)
		);
	}
}
