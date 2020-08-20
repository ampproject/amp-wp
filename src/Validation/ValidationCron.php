<?php
/**
 * REST endpoint providing theme scan results.
 *
 * @package AMP
 * @since 2.1
 */

namespace AmpProject\AmpWP\Validation;

use AMP_Validated_URL_Post_Type;
use AMP_Validation_Manager;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * ValidationCron class.
 *
 * @since 2.1
 */
final class ValidationCron implements Delayed, Service, Registerable {
	const CRON_ACTION = 'amp_validate_urls';

	/**
	 * Get the action to use for registering the service.
	 *
	 * @return string Registration action to use.
	 */
	public static function get_registration_action() {
		return 'wp';
	}

	/**
	 * Schedules the cron action.
	 *
	 * @return void
	 */
	public function register() {
		add_action( self::CRON_ACTION, [ $this, 'validate_urls' ] );

		if ( ! wp_next_scheduled( self::CRON_ACTION ) ) {
			wp_schedule_event( time(), 'hourly', self::CRON_ACTION );
		}
	}

	/**
	 * Validates URLs.
	 */
	public function validate_urls() {
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
