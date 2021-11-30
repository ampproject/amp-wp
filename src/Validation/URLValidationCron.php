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
use AMP_Validated_URL_Post_Type;
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
				'urls'      => [],
				'timestamp' => 0,
				'env'       => [],
			],
			$data
		);

		$current_env = AMP_Validated_URL_Post_Type::get_validated_environment();

		// When the validated environment changes, make sure the URLs and timestamp are reset so that new URLs are obtained.
		if ( $data['timestamp'] && $data['env'] !== $current_env ) {
			$data['urls']      = [];
			$data['timestamp'] = 0;
		}

		// If there are no URLs queued, then obtain a new set.
		if ( empty( $data['urls'] ) ) {

			// If it has been less than a week since the last enqueueing, then do nothing.
			if ( time() - $data['timestamp'] < WEEK_IN_SECONDS ) {
				return null;
			}

			$data['urls']      = wp_list_pluck( $this->scannable_url_provider->get_urls(), 'url' );
			$data['timestamp'] = time();
		}

		$url = array_shift( $data['urls'] );

		$data['env'] = $current_env;
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
