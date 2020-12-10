<?php
/**
 * WP cron process to validate URLs in the background.
 *
 * @package AMP
 * @since 2.1
 */

namespace AmpProject\AmpWP\Validation;

use AmpProject\AmpWP\BackgroundTask\BackgroundTaskDeactivator;
use AmpProject\AmpWP\BackgroundTask\CronBasedBackgroundTask;
use AmpProject\AmpWP\Infrastructure\Injector;

/**
 * URLValidationCron class.
 *
 * @since 2.1
 *
 * @internal
 */
final class URLValidationCron extends CronBasedBackgroundTask {

	/**
	 * Injector instance.
	 *
	 * @var Injector
	 */
	private $injector;

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
	 * The length of time, in seconds, to sleep between each URL validation.
	 *
	 * @var int
	 */
	const DEFAULT_SLEEP_TIME = 1;

	/**
	 * Class constructor.
	 *
	 * @param BackgroundTaskDeactivator $background_task_deactivator Service that deactivates background events.
	 * @param Injector                  $injector Injector instance.
	 */
	public function __construct( BackgroundTaskDeactivator $background_task_deactivator, Injector $injector ) {
		parent::__construct( $background_task_deactivator );

		$this->injector = $injector;
	}

	/**
	 * Callback for the cron action.
	 */
	public function process() {
		$number_per_type         = $this->get_url_validation_number_per_type();
		$validation_url_provider = $this->injector->make( ScannableURLProvider::class, [ $number_per_type, [], true ] );
		$urls                    = $validation_url_provider->get_urls();
		$sleep_time              = $this->get_sleep_time();
		$validation_provider     = $this->injector->make( URLValidationProvider::class );

		foreach ( $urls as $url ) {
			$validation_provider->get_url_validation( $url['url'], $url['type'], true );
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
	 * Returns the number of URLs per content type to check.
	 *
	 * @return int
	 */
	private function get_url_validation_number_per_type() {

		/**
		 * Filters the number of URLs per content type to check during each run of the cron task.
		 *
		 * @param int $url_validation_number_per_type The number of URLs. Default 1.
		 */
		$url_validation_number_per_type = apply_filters( 'amp_url_validation_number_per_type', self::DEFAULT_LIMIT_PER_TYPE );

		// Valid values are any integer -1 and above.
		if ( -1 > $url_validation_number_per_type ) {
			return 1;
		}

		return $url_validation_number_per_type;
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
