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

		$scheduled_event = $this->get_scheduled_event( $event_name, $args );

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
	 * Retrieve a scheduled event.
	 *
	 * This uses a copied implementation from WordPress core if `wp_get_scheduled_event()` does not exist, as it was
	 * introduced in WordPress 5.1.
	 *
	 * @link https://github.com/WordPress/wordpress-develop/blob/ba943e113d3b31b121f7/src/wp-includes/cron.php#L753-L793
	 * @see \wp_get_scheduled_event()
	 * @codeCoverageIgnore
	 *
	 * @param string $hook      Action hook of the event.
	 * @param array  $args      Optional. Array containing each separate argument to pass to the hook's callback function.
	 *                            Although not passed to a callback, these arguments are used to uniquely identify the
	 *                            event, so they should be the same as those used when originally scheduling the event.
	 *                            Default empty array.
	 * @param mixed  $timestamp Optional. Unix timestamp (UTC) of the event. If not specified, the next scheduled event
	 *                            is returned. Default null.
	 * @return object|false The event object. False if the event does not exist.
	 */
	final public function get_scheduled_event( $hook, $args = [], $timestamp = null ) {
		if ( function_exists( 'wp_get_scheduled_event' ) ) {
			return wp_get_scheduled_event( $hook, $args, $timestamp );
		}

		if ( null !== $timestamp && ! is_numeric( $timestamp ) ) {
			return false;
		}

		$crons = _get_cron_array();
		if ( empty( $crons ) ) {
			return false;
		}

		$key = md5( serialize( $args ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize -- This is copied from WP core.

		if ( ! $timestamp ) {
			// Get next event.
			$next = false;
			foreach ( $crons as $timestamp => $cron ) {
				if ( isset( $cron[ $hook ][ $key ] ) ) {
					$next = $timestamp;
					break;
				}
			}
			if ( ! $next ) {
				return false;
			}

			$timestamp = $next;
		} elseif ( ! isset( $crons[ $timestamp ][ $hook ][ $key ] ) ) {
			return false;
		}

		$event = (object) [
			'hook'      => $hook,
			'timestamp' => $timestamp,
			'schedule'  => $crons[ $timestamp ][ $hook ][ $key ]['schedule'],
			'args'      => $args,
		];

		if ( isset( $crons[ $timestamp ][ $hook ][ $key ]['interval'] ) ) {
			$event->interval = $crons[ $timestamp ][ $hook ][ $key ]['interval'];
		}

		return $event;
	}

	/**
	 * Get the interval to use for the event.
	 *
	 * @return string An existing interval name. Valid values are 'hourly', 'twicedaily' or 'daily'.
	 */
	abstract protected function get_interval();
}
