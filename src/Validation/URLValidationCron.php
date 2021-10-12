<?php
/**
 * WP cron process to validate URLs in the background.
 *
 * @package AMP
 * @since   2.1
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
	 * Class constructor.
	 *
	 * @param BackgroundTaskDeactivator $background_task_deactivator Service that deactivates background events.
	 * @param URLValidationProvider     $url_validation_provider     URLValidationProvider instance.
	 */
	public function __construct( BackgroundTaskDeactivator $background_task_deactivator, URLValidationProvider $url_validation_provider ) {
		parent::__construct( $background_task_deactivator );

		$this->url_validation_provider = $url_validation_provider;
	}

	/**
	 * Callback for the cron action.
	 *
	 * @param mixed[] ...$args Unused callback arguments.
	 */
	public function process( ...$args ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$validation_queue = get_option( URLValidationQueueCron::OPTION_KEY, [] );

		if ( empty( $validation_queue ) || ! is_array( $validation_queue ) ) {
			return;
		}

		$entry = array_shift( $validation_queue );
		if ( isset( $entry['url'], $entry['type'] ) ) {
			$this->url_validation_provider->get_url_validation( $entry['url'], $entry['type'] );
		}

		update_option( URLValidationQueueCron::OPTION_KEY, $validation_queue );
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
}
