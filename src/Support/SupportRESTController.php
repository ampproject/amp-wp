<?php
/**
 * REST API support for Support.
 *
 * @package amp-wp
 */

namespace AmpProject\AmpWP\Support;

use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Injector;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use WP_Error;
use WP_HTTP_Response;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Class SupportRESTController
 * REST API support to send AMP support data.
 *
 * @package AmpProject\AmpWP\Support
 * @internal
 * @since 2.2
 */
class SupportRESTController extends WP_REST_Controller implements Delayed, Service, Registerable {

	/**
	 * Namespace for REST API endpoint.
	 *
	 * @var string $namespace
	 */
	public $namespace = 'amp/v1';

	/**
	 * Injector.
	 *
	 * @var Injector
	 */
	private $injector;

	/**
	 * Class constructor.
	 *
	 * @param Injector $injector Injector.
	 */
	public function __construct( Injector $injector ) {

		$this->injector = $injector;
	}

	/**
	 * Get the action to use for registering the service.
	 *
	 * @return string Registration action to use.
	 */
	public static function get_registration_action() {

		return 'rest_api_init';
	}

	/**
	 * Registers all routes for the controller.
	 */
	public function register() {

		register_rest_route(
			$this->namespace,
			'/send-diagnostic',
			[
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'args'                => [],
					'permission_callback' => [ $this, 'permission_callback' ],
					'callback'            => [ $this, 'callback' ],
				],
			]
		);
	}

	/**
	 * Permission check to send support data.
	 *
	 * @return bool True if user have permission. Otherwise False.
	 */
	public function permission_callback() {

		return current_user_can( 'manage_options' );
	}

	/**
	 * Send AMP support data to insight server.
	 *
	 * @param WP_REST_Request $request REST API request.
	 *
	 * @return WP_Error|WP_HTTP_Response|WP_REST_Response REST API response.
	 */
	public function callback( WP_REST_Request $request ) {

		$request_args = $request->get_param( 'args' );
		$request_args = ( ! empty( $request_args ) && is_array( $request_args ) ) ? $request_args : [];

		$support_data     = $this->injector->make( SupportData::class, [ 'args' => $request_args ] );
		$support_response = $support_data->send_data();

		$response = new WP_Error(
			'fail_to_send_data',
			__( 'Failed to send support request. Please try again later.', 'amp' ),
			[ 'status' => 500 ]
		);

		if ( ! empty( $support_response ) && is_wp_error( $support_response ) ) {
			$response = $support_response;
		}

		if ( 'ok' === $support_response['status'] && ! empty( $support_response['data']['uuid'] ) ) {
			$response = [
				'success' => true,
				'data'    => $support_response['data'],
			];
		}

		return rest_ensure_response( $response );
	}
}
