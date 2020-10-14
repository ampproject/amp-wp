<?php
/**
 * WP cron process to validate URLs in the background.
 *
 * @package AMP
 * @since 2.1
 */

namespace AmpProject\AmpWP\Validation;

use AmpProject\AmpWP\BackgroundTask\CronBasedBackgroundTask;

/**
 * URLValidationCron class.
 *
 * @since 2.1
 *
 * @internal
 */
final class URLValidationCron extends CronBasedBackgroundTask {
	/**
	 * The cron action name.
	 *
	 * @var string
	 */
	const BACKGROUND_TASK_NAME = 'amp_validate_urls';

	/**
	 * The number of URLs to check per type each time the cron action runs.
	 *
	 * @var int
	 */
	const DEFAULT_LIMIT_PER_TYPE = 1;

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
		return self::BACKGROUND_TASK_NAME;
	}

	/**
	 * Get the interval to use for the event.
	 *
	 * @return string An existing interval name.
	 */
	protected function get_interval() {
		return self::DEFAULT_INTERVAL_HOURLY;
	}

	/**
	 * Callback for the cron action.
	 */
	public function process() {
		$this->validate_urls();
	}

	/**
	 * Returns the number of URLs per content type to check.
	 *
	 * @return int
	 */
	private function get_url_validation_number_per_type() {

		/**
		 * Filters the number of URLs per content type to check during each run of the cron task.
		 *
		 * @param int The number of URLs. Default 2.
		 */
		return (int) apply_filters( 'amp_url_validation_number_per_type', self::DEFAULT_LIMIT_PER_TYPE );
	}

	/**
	 * Validates URLs beginning at the next offset.
	 *
	 * @param boolean $sleep Whether to sleep between URLs. This should only be false in testing.
	 */
	public function validate_urls( $sleep = true ) {
		$number_per_type         = $this->get_url_validation_number_per_type();
		$validation_url_provider = new ScannableURLProvider( $number_per_type, [], true );
		$urls                    = $validation_url_provider->get_urls();

		$validation_provider = new URLValidationProvider();

		// with_lock returns an error if the process is locked.
		$validation_provider->with_lock(
			static function() use ( $validation_provider, $sleep, $urls ) {
				foreach ( $urls as $url ) {
					$validation_provider->get_url_validation( $url['url'], $url['type'] );
					if ( $sleep ) {
						sleep( 1 );
					}
				}
			}
		);
	}
}
