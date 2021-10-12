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
use AMP_Validation_Manager;

/**
 * URLValidationCron class.
 *
 * @since 2.1
 *
 * @internal
 */
final class URLValidationCron extends RecurringBackgroundTask {

	/**
	 * The cron action name.
	 *
	 * Note that only one queued URL is currently validated at a time.
	 *
	 * @var string
	 */
	const BACKGROUND_TASK_NAME = 'amp_validate_urls';

	/**
	 * Option key to store queue for URL validation.
	 *
	 * @var string
	 */
	const OPTION_KEY = 'amp_url_validation_queue';

	/**
	 * ScannableURLProvider instance.
	 *
	 * @var ScannableURLProvider
	 */
	private $scannable_url_provider;

	/**
	 * Constructor.
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
		$url = $this->dequeue();
		if ( $url ) {
			AMP_Validation_Manager::validate_url_and_store( $url );
		}
	}

	/**
	 * Dequeue to obtain URL to validate.
	 *
	 * @return string|null URL to validate or null if there is nothing queued up.
	 */
	protected function dequeue() {
		$data = get_option( self::OPTION_KEY, [] );
		if ( ! is_array( $data ) ) {
			$data = [];
		}
		$data = array_merge(
			[
				'timestamp' => 0,
				'urls'      => [],
			],
			$data
		);

		// If there are no URLs queued, then obtain a new set.
		if ( empty( $data['urls'] ) ) {

			// If it has been less than a week since the last enqueueing, then do nothing.
			if ( time() - $data['timestamp'] < WEEK_IN_SECONDS ) {
				return null;
			}

			// @todo The URLs should be contextual based on the selected template mode, in particular only singular URLs should be included if using legacy Reader mode.
			$data['urls']      = wp_list_pluck( $this->scannable_url_provider->get_urls(), 'url' );
			$data['timestamp'] = time();
		}

		// If there is not a queued URL, then enqueue a new set of URLs.
		$url = array_shift( $data['urls'] );

		update_option( self::OPTION_KEY, $data );

		return $url ?: null;
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
