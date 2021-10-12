<?php
/**
 * WP cron process to validate URLs in the background.
 *
 * @package AMP
 * @since   2.1
 */

namespace AmpProject\AmpWP\Validation;

use AmpProject\AmpWP\BackgroundTask\RecurringBackgroundTask;
use AMP_Validation_Manager;

/**
 * URLValidationCron class.
 *
 * @since 2.1
 *
 * @todo This should be renamed something to make it distinct from URLValidationQueueCron.
 * @internal
 */
final class URLValidationCron extends RecurringBackgroundTask {

	/**
	 * The cron action name.
	 *
	 * @todo Rename this to amp_validate_dequeued_url or something.
	 *
	 * @var string
	 */
	const BACKGROUND_TASK_NAME = 'amp_validate_urls';

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

		$url = array_shift( $validation_queue );
		if ( is_string( $url ) ) {
			AMP_Validation_Manager::validate_url_and_store( $url );
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
