<?php
/**
 * WP cron process to validate URLs in the background.
 *
 * @package AMP
 * @since   2.2
 */

namespace AmpProject\AmpWP\Validation;

use AmpProject\AmpWP\BackgroundTask\BackgroundTaskDeactivator;
use AmpProject\AmpWP\BackgroundTask\RecurringBackgroundTask;

/**
 * URLValidationQueueCron class.
 *
 * @since 2.2
 *
 * @internal
 */
final class URLValidationQueueCron extends RecurringBackgroundTask {

	/**
	 * ScannableURLProvider instance.
	 *
	 * @var ScannableURLProvider
	 */
	private $scannable_url_provider;

	/**
	 * The cron action name.
	 *
	 * @var string
	 */
	const BACKGROUND_TASK_NAME = 'amp_validate_url_queue';

	/**
	 * Option key to store queue for URL validation.
	 *
	 * @var string
	 */
	const OPTION_KEY = 'amp_url_validation_queue';

	/**
	 * Class constructor.
	 *
	 * @param BackgroundTaskDeactivator $background_task_deactivator Service that deactivates background events.
	 * @param ScannableURLProvider      $scannable_url_provider      ScannableURLProvider instance.
	 */
	public function __construct( BackgroundTaskDeactivator $background_task_deactivator, ScannableURLProvider $scannable_url_provider ) {

		parent::__construct( $background_task_deactivator );

		$this->scannable_url_provider = $scannable_url_provider;
	}

	/**
	 * Callback for the cron action.
	 *
	 * @param mixed[] ...$args Unused callback arguments.
	 */
	public function process( ...$args ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$urls             = $this->scannable_url_provider->get_urls();
		$validation_queue = get_option( self::OPTION_KEY, [] );

		foreach ( $urls as $url ) {
			if ( ! in_array( $url, $validation_queue, true ) ) {
				$validation_queue[] = $url;
			}
		}

		update_option( self::OPTION_KEY, $validation_queue );
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
		return self::DEFAULT_INTERVAL_WEEKLY;
	}
}
