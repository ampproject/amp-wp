<?php
/**
 * Abstract class CronBasedBackgroundTask.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\BackgroundTask;

use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;

/**
 * Abstract base class for using cron to execute a background task.
 *
 * @package AmpProject\AmpWP
 * @since 2.0
 * @internal
 */
abstract class CronBasedBackgroundTask implements Service, Registerable, Conditional {

	const DEFAULT_INTERVAL_HOURLY      = 'hourly';
	const DEFAULT_INTERVAL_TWICE_DAILY = 'twicedaily';
	const DEFAULT_INTERVAL_DAILY       = 'daily';

	/**
	 * BackgroundTaskDeactivator instance.
	 *
	 * @var BackgroundTaskDeactivator
	 */
	private $background_task_deactivator;

	/**
	 * Class constructor.
	 *
	 * @param BackgroundTaskDeactivator $background_task_deactivator Service that deactivates background events.
	 */
	public function __construct( BackgroundTaskDeactivator $background_task_deactivator ) {
		$this->background_task_deactivator = $background_task_deactivator;
	}

	/**
	 * Check whether the conditional object is currently needed.
	 *
	 * @return bool Whether the conditional object is needed.
	 */
	public static function is_needed() {
		return is_admin() || wp_doing_cron();
	}

	/**
	 * Register the service with the system.
	 *
	 * @return void
	 */
	public function register() {
		$this->background_task_deactivator->add_event( $this->get_event_name() );
		add_action( 'admin_init', [ $this, 'schedule_event' ] );
		add_action( $this->get_event_name(), [ $this, 'process' ] );
	}

	/**
	 * Schedule the event.
	 *
	 * This does nothing if the event is already scheduled.
	 *
	 * @return void
	 */
	public function schedule_event() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$event_name = $this->get_event_name();
		$timestamp  = wp_next_scheduled( $event_name );

		if ( $timestamp ) {
			return;
		}

		wp_schedule_event( time(), $this->get_interval(), $event_name );
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
