<?php
/**
 * Abstract class CronBasedBackgroundTask.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\BackgroundTask;

use AmpProject\AmpWP\Exception\FailedToRegisterBackgroundTask;
use AmpProject\AmpWP\Exception\InvalidInterval;

/**
 * Abstract base class for using cron to execute a background task.
 *
 * @package AmpProject\AmpWP
 */
abstract class CronBasedBackgroundTask {

	const DEFAULT_INTERVAL_HOURLY      = 'hourly';
	const DEFAULT_INTERVAL_TWICE_DAILY = 'twicedaily';
	const DEFAULT_INTERVAL_DAILY       = 'daily';

	/**
	 * List of default interval names that are shipped with WordPress.
	 *
	 * @var string[]
	 */
	const DEFAULT_INTERVAL_NAMES = [
		self::DEFAULT_INTERVAL_HOURLY,
		self::DEFAULT_INTERVAL_TWICE_DAILY,
		self::DEFAULT_INTERVAL_DAILY,
	];

	/**
	 * Register the background task with the system.
	 *
	 * @return void
	 * @throws InvalidInterval If the interval was neither an existing interval name nor a valid duration.
	 * @throws FailedToRegisterBackgroundTask If the background task could not be registered with WordPress.
	 */
	public function register() {
		$interval      = $this->validate_interval( $this->get_interval() );
		$interval_name = $this->maybe_register_interval( $interval );
		$this->register_hook();

		$timestamp = $this->schedule_event( $interval_name );

		if ( ! $timestamp ) {
			throw FailedToRegisterBackgroundTask::for_scheduled_event( $this->get_event_name() );
		}

		$this->register_deactivation( $timestamp );
	}

	/**
	 * Maybe register a custom interval if a custom duration was provided.
	 *
	 * @param string|int $interval Either an existing interval name, or a new interval duration in seconds.
	 * @return string Name of the interval to use.
	 */
	protected function maybe_register_interval( $interval ) {
		if ( is_string( $interval ) ) {
			// A string was already validated to be one of the default interval names.
			return $interval;
		}

		$interval_name = $this->get_interval_name();

		// The interval is a duration, so we add a new cron_schedule for this duration.
		add_filter( // phpcs:ignore WordPress.WP.CronInterval.ChangeDetected
			'cron_schedules',
			function ( $schedules ) use ( $interval_name, $interval ) {
				$schedules[ $interval_name ] = [
					'interval' => $interval,
					'display'  => esc_attr( $this->get_interval_display_name() ),
				];

				return $schedules;
			}
		);

		return $interval_name;
	}

	/**
	 * Register the hook that triggers the processing.
	 */
	protected function register_hook() {
		add_action( $this->get_event_name(), [ $this, 'process' ] );
	}

	/**
	 * Schedule the event.
	 *
	 * @param string $interval_name Name of the interval to use for scheduling.
	 * @return string|false Timestamp of the next recurrence or false if failed.
	 */
	protected function schedule_event( $interval_name ) {
		$event_name = $this->get_event_name();
		$timestamp  = wp_next_scheduled( $event_name );

		if ( $timestamp ) {
			return $timestamp;
		}

		if ( ! wp_schedule_event( time(), $interval_name, $event_name ) ) {
			return false;
		}

		return wp_next_scheduled( $event_name );
	}

	/**
	 * Register the deactivation hook.
	 *
	 * @param string $timestamp Timestamp to register the deactivation hook for.
	 */
	protected function register_deactivation( $timestamp ) {
		register_deactivation_hook(
			dirname( dirname( __DIR__ ) ) . '/amp.php',
			function () use ( $timestamp ) {
				wp_unschedule_event( $timestamp, $this->get_event_name() );
			}
		);
	}

	/**
	 * Get the name for the event's interval.
	 *
	 * @return string Name of the interval.
	 */
	protected function get_interval_name() {
		return "{$this->get_event_name()}_interval";
	}

	/**
	 * Get the display name for the event's interval.
	 *
	 * @return string Display name of the interval.
	 */
	protected function get_interval_display_name() {
		return sprintf(
			/* translators: %s => name of the registered event */
			__( 'Interval for the "%s" event', 'amp' ),
			$this->get_event_name()
		);
	}

	/**
	 * Validate the given interval value.
	 *
	 * The $interval needs to be either a string matching one of the default intervals shipped with WordPress (i.e.
	 * 'hourly', 'twicedaily' or 'daily'), or a positive integer representing the duration in seconds of a new interval
	 * to be registered.
	 *
	 * @param mixed $interval Interval value to validate.
	 * @return string|int Validated interval.
	 * @throws InvalidInterval If the interval was neither an existing interval name nor a valid duration.
	 */
	protected function validate_interval( $interval ) {
		if ( ! is_int( $interval ) && ! is_string( $interval ) ) {
			throw InvalidInterval::for_invalid_type( $interval );
		}

		if ( is_int( $interval ) && $interval <= 0 ) {
			throw InvalidInterval::for_invalid_duration( $interval );
		}

		if ( is_string( $interval ) && ! in_array( $interval, self::DEFAULT_INTERVAL_NAMES, true ) ) {
			throw InvalidInterval::for_unknown_name( $interval );
		}

		return $interval;
	}

	/**
	 * Get the interval to use for the event.
	 *
	 * If the passed-in interval is a string that matches an existing interval, that interval is used as-is.
	 *
	 * If the passed-in interval is an integer, that value is used as a duration in seconds to create a new interval.
	 *
	 * @return string|int Either an existing interval name, or a new interval duration in seconds.
	 */
	abstract protected function get_interval();

	/**
	 * Get the event name.
	 *
	 * This is the "slug" of the event, not the display name.
	 *
	 * Note: the event name should be prefixed to prevent naming collisions.
	 *
	 * @return string Name of the event.
	 */
	abstract protected function get_event_name();

	/**
	 * Process a single cron tick.
	 *
	 * @return void
	 */
	abstract public function process();
}
