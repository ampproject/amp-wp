<?php
/**
 * Abstract class RecurringBackgroundTask.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\BackgroundTask;

/**
 * Abstract base class for using cron to execute a background task that runs on a schedule.
 *
 * @package AmpProject\AmpWP
 * @since 2.1
 * @internal
 */
abstract class RecurringBackgroundTask extends CronBasedBackgroundTask {

	/**
	 * Register the service with the system.
	 */
	public function register() {
		parent::register();

		add_action( 'admin_init', [ $this, 'schedule_event' ], 10, 0 );
		add_action( $this->get_event_name(), [ $this, 'process' ] );
	}

	/**
	 * Schedule the event.
	 *
	 * @param mixed[] ...$args Arguments passed to the function from the action hook, which here is empty always since add_action().
	 */
	final public function schedule_event( ...$args ) {
		if ( ! is_user_logged_in() ) {
			return;
		}

		$event_name = $this->get_event_name();
		$recurrence = $this->get_interval();

		$scheduled_event = wp_get_scheduled_event( $event_name, $args );

		// Unschedule any existing event which had a differing recurrence.
		if ( $scheduled_event && $scheduled_event->schedule !== $recurrence ) {
			wp_unschedule_event( $scheduled_event->timestamp, $event_name, $args );
			$scheduled_event = null;
		}

		if ( ! $scheduled_event ) {
			wp_schedule_event( time(), $recurrence, $event_name, $args );
		}
	}

	/**
	 * Get the interval to use for the event.
	 *
	 * @return string An existing interval name. Valid values are 'hourly', 'twicedaily' or 'daily'.
	 */
	abstract protected function get_interval();
}
