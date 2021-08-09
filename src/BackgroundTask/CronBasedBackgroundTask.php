<?php
/**
 * Abstract class CronBasedBackgroundTask.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\BackgroundTask;

use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;

/**
 * Abstract base class for using cron to execute a background task.
 *
 * @package AmpProject\AmpWP
 * @since 2.0
 * @internal
 */
abstract class CronBasedBackgroundTask implements Service, Registerable {

	const DEFAULT_INTERVAL_HOURLY            = 'hourly';
	const DEFAULT_INTERVAL_TWICE_DAILY       = 'twicedaily';
	const DEFAULT_INTERVAL_DAILY             = 'daily';
	const DEFAULT_INTERVAL_EVERY_TEN_MINUTES = 'every_ten_minutes';

	/**
	 * BackgroundTaskDeactivator instance.
	 *
	 * @var BackgroundTaskDeactivator
	 */
	protected $background_task_deactivator;

	/**
	 * Class constructor.
	 *
	 * @param BackgroundTaskDeactivator $background_task_deactivator Service that deactivates background events.
	 */
	public function __construct( BackgroundTaskDeactivator $background_task_deactivator ) {
		$this->background_task_deactivator = $background_task_deactivator;
	}

	/**
	 * Register the service with the system.
	 *
	 * @return void
	 */
	public function register() {
		$this->background_task_deactivator->add_event( $this->get_event_name() );
		add_filter( 'cron_schedules', [ $this, 'add_cron_schedules' ] ); // phpcs:ignore WordPress.WP.CronInterval.ChangeDetected
	}

	/**
	 * Schedule the event.
	 *
	 * @param mixed[] ...$args Arguments passed to the function from the action hook.
	 */
	abstract protected function schedule_event( ...$args );

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
	 * Process the event.
	 *
	 * @param mixed[] ...$args Args to pass to the process callback.
	 */
	abstract public function process( ...$args );

	/**
	 * Add new interval for cron schedules.
	 *
	 * @param array $cron_schedules List of cron schedules.
	 *
	 * @return array List of cron schedules.
	 */
	public function add_cron_schedules( $cron_schedules ) {

		$cron_schedules = ( ! empty( $cron_schedules ) && is_array( $cron_schedules ) ) ? $cron_schedules : [];

		$cron_schedules[ self::DEFAULT_INTERVAL_EVERY_TEN_MINUTES ] = [
			'interval' => ( MINUTE_IN_SECONDS * 10 ),
			'display'  => esc_html__( 'Every 10 minutes', 'amp' ),
		];

		return $cron_schedules;
	}
}
