<?php
/**
 * Abstract class CronBasedBackgroundTask.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\BackgroundTask;

use AmpProject\AmpWP\HasActivation;
use AmpProject\AmpWP\HasDeactivation;
use AmpProject\AmpWP\Service;

/**
 * Abstract base class for using cron to execute a background task.
 *
 * @package AmpProject\AmpWP
 */
abstract class CronBasedBackgroundTask implements Service, HasDeactivation {

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
	 * Register the service with the system.
	 *
	 * @return void
	 */
	public function register() {
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
		if ( ! current_user_can( 'administrator' ) ) {
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
	 * Run deactivation logic.
	 *
	 * This should be hooked up to the WordPress deactivation hook.
	 *
	 * @param bool $network_wide Whether the deactivation was done network-wide.
	 * @return void
	 */
	public function deactivate( $network_wide ) {
		if ( $network_wide && is_multisite() && ! wp_is_large_network( 'sites' ) ) {
			foreach ( get_sites(
				[
					'fields' => 'ids',
					'number' => 0,
				]
			) as $blog_id ) {
				switch_to_blog( $blog_id );
				wp_clear_scheduled_hook( $this->get_event_name() );
				restore_current_blog();
			}
		} else {
			wp_clear_scheduled_hook( $this->get_event_name() );
		}
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
