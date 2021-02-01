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
use DateTimeInterface;
use Exception;

/**
 * Monitor the CSS transient caching to detect and remedy issues.
 *
 * This checks whether there's excessive cycling of CSS cached stylesheets and disables transient caching if so.
 *
 * @package AmpProject\AmpWP
 * @since 2.0
 * @internal
 */
final class MonitorCssTransientCaching extends RecurringBackgroundTask {

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
	 * This is set high to avoid false positives and only trigger on high-traffic sites that exhibit serious problems.
	 *
	 * @var float
	 */
	const DEFAULT_THRESHOLD = 5000.0;

	/**
	 * Sampling range in days to calculate the moving average from.
	 *
	 * @var int
	 */
	const DEFAULT_SAMPLING_RANGE = 14;

	/**
	 * Register the service with the system.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'amp_plugin_update', [ $this, 'handle_plugin_update' ] );
		parent::register();
	}

	/**
	 * Get the interval to use for the event.
	 *
	 * @return string An existing interval name. Valid values are 'hourly', 'twicedaily' or 'daily'.
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
	 * @todo This has arbitrary arguments to allow for testing, as we don't have dependency injection for services.
	 *       With dependency injection, we could for example inject a Clock object and mock it for testing.
	 *
	 * @param array ...$args {
	 *     Arguments passed to the cron callback.
	 *
	 *     @type DateTimeInterface $date Optional. Date to use for timestamping the processing (for testing).
	 *     @type int               $transient_count Optional. Count of transients to use for the processing (for testing).
	 * }
	 * @return void
	 * @throws Exception If a date could not be instantiated.
	 */
	public function process( ...$args ) {
		if ( wp_using_ext_object_cache() || $this->is_css_transient_caching_disabled() ) {
			return;
		}

		$date = isset( $args[0] ) && $args[0] instanceof DateTimeInterface ? $args[0] : new DateTimeImmutable();

		/** @phpstan-ignore-next-line */
		$transient_count = isset( $args[1] ) ? (int) $args[1] : $this->query_css_transient_count();

		$date_string = $date->format( 'Ymd' );
		$time_series = $this->get_time_series();

		$time_series[ $date_string ] = $transient_count;
		ksort( $time_series );

		$sampling_range = $this->get_sampling_range();
		$time_series    = array_slice( $time_series, - $sampling_range, null, true );

		$this->persist_time_series( $time_series );

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
	public function query_css_transient_count() {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '_transient_amp-parsed-stylesheet%'"
		);
	}

	/**
	 * Handle update to plugin.
	 *
	 * @param string $old_version Old version.
	 */
	public function handle_plugin_update( $old_version ) {
		// Reset the disabling of the CSS caching subsystem when updating from versions 1.5.0 or 1.5.1.
		if ( version_compare( $old_version, '1.5.0', '>=' ) && version_compare( $old_version, '1.5.2', '<' ) ) {
			AMP_Options_Manager::update_option( Option::DISABLE_CSS_TRANSIENT_CACHING, false );
		}
	}

	/**
	 * Get the time series stored in the WordPress options table.
	 *
	 * @return int[] Time series with the count of transients per day.
	 */
	public function get_time_series() {
		return (array) get_option( self::TIME_SERIES_OPTION_KEY, [] );
	}

	/**
	 * Get the default threshold to use.
	 *
	 * @return float Default threshold to use.
	 */
	public function get_default_threshold() {
		return self::DEFAULT_THRESHOLD;
	}

	/**
	 * Get the default sampling range to use.
	 *
	 * @return int Default sampling range to use.
	 */
	public function get_default_sampling_range() {
		return self::DEFAULT_SAMPLING_RANGE;
	}

	/**
	 * Persist the time series in the database.
	 *
	 * @param int[] $time_series Associative array of integers with the key being a date string and the value the count
	 *                           of transients.
	 */
	private function persist_time_series( $time_series ) {
		update_option( self::TIME_SERIES_OPTION_KEY, $time_series, false );
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

		/**
		 * Filters the threshold to use for disabling transient caching of stylesheets.
		 *
		 * @since 1.5.0
		 *
		 * @param int $threshold Maximum average number of transients per day.
		 */
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

		/**
		 * Filters the sampling range to use for monitoring the transient caching of stylesheets.
		 *
		 * @since 1.5.0
		 *
		 * @param int $sampling_rage Sampling range in number of days.
		 */
		$sampling_range = (int) apply_filters( 'amp_css_transient_monitoring_sampling_range', self::DEFAULT_SAMPLING_RANGE );

		return $sampling_range > 0 ? $sampling_range : self::DEFAULT_SAMPLING_RANGE;
	}
}
