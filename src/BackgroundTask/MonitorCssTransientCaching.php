<?php
/**
 * Class MonitorCssTransientCaching.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\BackgroundTask;

use AMP_Options_Manager;
use AmpProject\AmpWP\Option;
use DateTimeImmutable;
use Exception;

/**
 * Monitor the CSS transient caching to detect and remedy issues.
 *
 * This checks whether there's excessive cycling of CSS cached stylesheets and disables transient caching if so.
 *
 * @package AmpProject\AmpWP
 */
final class MonitorCssTransientCaching extends CronBasedBackgroundTask {

	/**
	 * Name of the event to schedule.
	 *
	 * @var string
	 */
	const EVENT_NAME = 'amp_monitor_css_transient_caching';

	/**
	 * Key to use to persist the time series in the WordPress options table.
	 *
	 * @var string
	 */
	const TIME_SERIES_OPTION_KEY = 'amp_css_transient_monitor_time_series';

	/**
	 * Default threshold to use for problem detection in number of transients per day.
	 *
	 * @var float
	 */
	const DEFAULT_THRESHOLD = 50.0;

	/**
	 * Sampling range in days to calculate the moving average from.
	 *
	 * @var int
	 */
	const DEFAULT_SAMPLING_RANGE = 14;

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
		return self::EVENT_NAME;
	}

	/**
	 * Process a single cron tick.
	 *
	 * @return void
	 * @throws Exception If a date could not be instantiated.
	 */
	public function process() {
		if ( $this->is_css_transient_caching_disabled() ) {
			return;
		}

		$count = $this->query_css_transient_count();
		$date  = ( new DateTimeImmutable() )->format( 'Ymd' );

		$time_series = $this->get_time_series();

		$time_series[ $date ] = $count;
		ksort( $time_series );

		$sampling_range = $this->get_sampling_range();
		$time_series    = array_slice( $time_series, - $sampling_range );

		$moving_average = $this->calculate_average( $time_series );

		if ( $moving_average > 0.0 && $moving_average > (float) $this->get_threshold() ) {
			$this->disable_css_transient_caching();
		}
	}

	/**
	 * Check whether transient caching of stylesheets is disabled.
	 *
	 * @return bool Whether transient caching of stylesheets is disabled.
	 */
	private function is_css_transient_caching_disabled() {
		return AMP_Options_Manager::get_option( Option::DISABLE_CSS_TRANSIENT_CACHING, false );
	}

	/**
	 * Disable transient caching of stylesheets.
	 */
	private function disable_css_transient_caching() {
		AMP_Options_Manager::update_option( Option::DISABLE_CSS_TRANSIENT_CACHING, true );
	}

	/**
	 * Query the number of transients containing cache stylesheets.
	 *
	 * @return int Count of transients caching stylesheets.
	 */
	private function query_css_transient_count() {
		global $wpdb;

		return (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '_transient_amp-parsed-stylesheet%'"
		);
	}

	/**
	 * Get the time series stored in the WordPress options table.
	 *
	 * @return int[] Time series with the count of transients per day.
	 */
	private function get_time_series() {
		return (array) get_option( self::TIME_SERIES_OPTION_KEY, [] );
	}

	/**
	 * Calculate the average for the provided time series.
	 *
	 * Note: The single highest value is discarded to calculate the average, so as to avoid a single outlier causing the
	 * threshold to be reached.
	 *
	 * @param int[] $time_series Associative array of integers with the key being a date string and the value the count
	 *                           of transients.
	 * @return float Average value for the provided time series.
	 */
	private function calculate_average( $time_series ) {
		$sum                   = array_sum( $time_series );
		$sum_without_outlier   = $sum - max( $time_series );
		$count_without_outlier = count( $time_series ) - 1;

		if ( $count_without_outlier <= 0 ) {
			return 0.0;
		}

		return $sum_without_outlier / $count_without_outlier;
	}

	/**
	 * Get the threshold to check the moving average against.
	 *
	 * This can be filtered via the 'amp_css_transient_monitoring_threshold' filter.
	 *
	 * @return float Threshold to use.
	 */
	private function get_threshold() {
		$threshold = (float) apply_filters( 'amp_css_transient_monitoring_threshold', self::DEFAULT_THRESHOLD );

		return $threshold > 0.0 ? $threshold : self::DEFAULT_THRESHOLD;
	}

	/**
	 * Get the sampling range to limit the time series to for calculating the moving average.
	 *
	 * This can be filtered via the 'amp_css_transient_monitoring_sampling_range' filter.
	 *
	 * @return int Sampling range to use.
	 */
	private function get_sampling_range() {
		$sampling_range = (int) apply_filters( 'amp_css_transient_monitoring_sampling_range', self::DEFAULT_SAMPLING_RANGE );

		return $sampling_range > 0 ? $sampling_range : self::DEFAULT_SAMPLING_RANGE;
	}
}
