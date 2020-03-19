<?php
/**
 * Class MonitorCssTransientCaching.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\BackgroundTask;

/**
 * Monitor the CSS transient caching to detect and remedy issues.
 *
 * This checks whether there's excessive cycling of CSS cached stylesheets and disables transient caching if so.
 *
 * @package AmpProject\AmpWP
 */
final class MonitorCssTransientCaching extends CronBasedBackgroundTask {

	/**
	 * Get the interval to use for the event.
	 *
	 * If the passed-in interval is a string that matches an existing interval, that interval is used as-is.
	 *
	 * If the passed-in interval is an integer, that value is used as a duration in seconds to create a new interval.
	 *
	 * @return string|int Either an existing interval name, or a new interval duration in seconds.
	 */
	protected function get_interval() {
		return self::DEFAULT_INTERVAL_DAILY;
	}

	/**
	 * Get the event name.
	 *
	 * This is the "slug" of the event, not the display name.
	 *
	 * Note: the event name should be prefixed to prevent naming collisions.
	 *
	 * @return string Name of the event.
	 */
	protected function get_event_name() {
		return 'amp_monitor_css_transient_caching';
	}

	/**
	 * Process a single cron tick.
	 *
	 * @return void
	 */
	public function process() {
		// TODO: Implement process() method.
	}
}
