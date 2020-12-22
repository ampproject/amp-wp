<?php
/**
 * WP cron process to validate URLs in the background.
 *
 * @package AMP
 * @since 2.1
 */

namespace AmpProject\AmpWP\Validation;

use AmpProject\AmpWP\BackgroundTask\BackgroundTaskDeactivator;
use AmpProject\AmpWP\BackgroundTask\RecurringBackgroundTask;

/**
 * URLValidationCron class.
 *
 * @since 2.1
 *
 * @internal
 */
final class URLValidationCron extends RecurringBackgroundTask {

	/**
	 * ScannableURLProvider instance.
	 *
	 * @var ScannableURLProvider
	 */
	private $scannable_url_provider;

	/**
	 * URLValidationProvider instance.
	 *
	 * @var URLValidationProvider
	 */
	private $url_validation_provider;

	/**
	 * The cron action name.
	 *
	 * @var string
	 */
	const BACKGROUND_TASK_NAME = 'amp_validate_urls';

	/**
	 * The length of time, in seconds, to sleep between each URL validation.
	 *
	 * @var int
	 */
	const DEFAULT_SLEEP_TIME = 1;

	/**
	 * Class constructor.
	 *
	 * @param BackgroundTaskDeactivator $background_task_deactivator Service that deactivates background events.
	 * @param ScannableURLProvider      $scannable_url_provider ScannableURLProvider instance.
	 * @param URLValidationProvider     $url_validation_provider URLValidationProvider isntance.
	 */
	public function __construct( BackgroundTaskDeactivator $background_task_deactivator, ScannableURLProvider $scannable_url_provider, URLValidationProvider $url_validation_provider ) {
		parent::__construct( $background_task_deactivator );

		$this->scannable_url_provider  = $scannable_url_provider;
		$this->url_validation_provider = $url_validation_provider;
	}

	/**
	 * Callback for the cron action.
	 *
	 * @param mixed[] ...$args Unused callback arguments.
	 */
	public function process( ...$args ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$urls       = $this->scannable_url_provider->get_urls();
		$sleep_time = $this->get_sleep_time();

		foreach ( $urls as $url ) {
			$this->url_validation_provider->get_url_validation( $url['url'], $url['type'], true );
			if ( $sleep_time ) {
				sleep( $sleep_time );
			}
		}
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
	 * Provides the length of time, in seconds, to sleep between validating URLs.
	 *
	 * @return int
	 */
	private function get_sleep_time() {

		/**
		 * Filters the length of time to sleep between validating URLs.
		 *
		 * @param int The number of seconds. Default 1.
		 */
		return absint( apply_filters( 'amp_url_validation_sleep_time', self::DEFAULT_SLEEP_TIME ) );
	}
}
