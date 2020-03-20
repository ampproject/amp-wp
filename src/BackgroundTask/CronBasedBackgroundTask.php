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
	 */
	public function register() {
		add_action( $this->get_event_name(), [ $this, 'process' ] );
	}

	/**
	 * Run activation logic.
	 *
	 * This should be hooked up to the WordPress deactivation hook.
	 *
	 * @return void
	 * @throws FailedToRegisterBackgroundTask If the background task could not be registered with WordPress.
	 */
	public function activate() {
		$event_name = $this->get_event_name();
		$timestamp  = wp_next_scheduled( $event_name );

		if ( $timestamp ) {
			return;
		}

		wp_schedule_event( time(), $this->get_interval(), $event_name );
	}

	/**
	 * Run deactivation logic.
	 *
	 * This should be hooked up to the WordPress deactivation hook.
	 *
	 * @return void
	 */
	public function deactivate() {
		wp_clear_scheduled_hook( $this->get_event_name() );
	}

	/**
	 * Get the interval to use for the event.
	 *
	 * @return string An existing interval name. Valid values are 'hourly', 'twicedaily' or 'daily'.
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
