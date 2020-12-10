<?php
/**
 * Abstract class SingleScheduledBackgroundTask.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\BackgroundTask;

/**
 * Abstract base class for using cron to execute a background task that runs only once.
 *
 * @package AmpProject\AmpWP
 * @since 2.1
 * @internal
 */
abstract class SingleScheduledBackgroundTask extends CronBasedBackgroundTask {

	/**
	 * Register the service with the system.
	 */
	public function register() {
		parent::register();

		add_action( $this->get_action_hook(), [ $this, 'schedule_event' ], 10, $this->get_action_hook_arg_count() );
		add_action( $this->get_event_name(), [ $this, 'process' ] );
	}

	/**
	 * Schedule the event.
	 *
	 * @param array ...$args Arguments passed to the function from the action hook.
	 */
	public function schedule_event( ...$args ) {
		if ( ! $this->should_schedule_event( $args ) ) {
			return;
		}

		wp_schedule_single_event( $this->get_timestamp(), $this->get_event_name(), $args );
	}

	/**
	 * Time after which to run the event.
	 *
	 * @return int A timestamp. Defaults to the current time.
	 */
	protected function get_timestamp() {
		return time();
	}

	/**
	 * Provides the number of args expected from the action hook where the event is registered. Default 1.
	 *
	 * @return int
	 */
	protected function get_action_hook_arg_count() {
		return 1;
	}

	/**
	 * Returns whether the event should be scheduled.
	 *
	 * @param array $args Arguments passed from the action hook where the event is to be scheduled.
	 * @return boolean
	 */
	abstract protected function should_schedule_event( $args );

	/**
	 * Gets the hook on which to schedule the event.
	 *
	 * @return string The action hook name.
	 */
	abstract protected function get_action_hook();
}
