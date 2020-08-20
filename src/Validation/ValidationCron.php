<?php
/**
 * REST endpoint providing theme scan results.
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
	const EVENT_NAME = 'amp_validate_urls';

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
	 * Validates URLs.
	 */
	public function process() {
		$urls = ( new ValidationURLProvider( 2, [], true ) )->get_urls();

		$validation_provider = new ValidationProvider();

		if ( $validation_provider->is_locked() ) {
			return;
		}

		$validation_provider->lock();

		foreach ( $urls as $url ) {
			$validation_provider->get_url_validation( $url['url'], $url['type'] );
		}

		$validation_provider->unlock();
	}
}
