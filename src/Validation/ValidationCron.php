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
 * ValidationCron class.
 *
 * @since 2.1
 */
final class ValidationCron extends CronBasedBackgroundTask {
	/**
	 * The cron action name.
	 *
	 * @var string
	 */
	const EVENT_NAME = 'amp_validate_urls';

	/**
	 * The key for the transient storing the number of URLs to offset by next time the cron task runs.
	 *
	 * @var string
	 */
	const OFFSET_KEY = 'amp_validate_urls_cron_offset';

	/**
	 * The number of URLs to check per type each time the cron action runs.
	 *
	 * @var int
	 */
	const LIMIT_PER_TYPE = 2;

	/**
	 * The length of time to store the offset transient.
	 *
	 * @var int
	 */
	const OFFSET_TRANSIENT_TIMEOUT = DAY_IN_SECONDS;

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
	 * Validates URLs beginning at the next offset.
	 *
	 * @param boolean $reset_if_no_urls_found If true and no URLs are found, the method will reset the offset to 0 and rerun.
	 * @param boolean $sleep Whether to sleep between URLs. This should only be off in testing.
	 */
	public function validate_urls( $reset_if_no_urls_found = true, $sleep = true ) {
		$validation_url_provider = new ValidationURLProvider( self::LIMIT_PER_TYPE, [], true );
		$offset                  = get_transient( self::OFFSET_KEY ) ?: 0;
		$urls                    = $validation_url_provider->get_urls( $offset );

		// Home (if supported), a date URL, and a search URL are always checked.
		$zero_url_count = 'posts' === get_option( 'show_on_front' ) && $validation_url_provider->is_template_supported( 'is_home' ) ? 3 : 2;

		// If no URLs are found beyond those that are checked every time, reset the offset to 0 and restart.
		if ( $reset_if_no_urls_found && count( $urls ) <= $zero_url_count ) {
			delete_transient( self::OFFSET_KEY );
			$this->validate_urls( false );
			return;
		}

		$validation_provider = new ValidationProvider();

		// with_lock returns an error if the process is locked.
		$potential_error = $validation_provider->with_lock(
			static function() use ( $validation_provider, $sleep, $urls ) {
				foreach ( $urls as $url ) {
					$validation_provider->get_url_validation( $url['url'], $url['type'] );
					if ( $sleep ) {
						sleep( 1 );
					}
				}
			}
		);

		// If the process was locked, run with the same offset next time around.
		if ( ! is_wp_error( $potential_error ) ) {
			set_transient( self::OFFSET_KEY, $offset + self::LIMIT_PER_TYPE );
		}
	}
}
