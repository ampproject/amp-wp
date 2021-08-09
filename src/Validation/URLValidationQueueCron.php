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
use AmpProject\AmpWP\Infrastructure\Conditional;

/**
 * URLValidationCron class.
 *
 * @since 2.1
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
		$urls                 = $this->scannable_url_provider->get_urls();
		$validation_queue_key = 'amp_url_validation_queue';
		$validation_queue     = get_option( $validation_queue_key, [] );

		foreach ( $urls as $url ) {

			if ( empty( $url['url'] ) || empty( $url['type'] ) ) {
				continue;
			}

			$url_hash                      = md5( trim( $url['url'] ) );
			$validation_queue[ $url_hash ] = $url;
		}

		update_option( $validation_queue_key, $validation_queue );
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
		return self::DEFAULT_INTERVAL_DAILY;
	}
}
