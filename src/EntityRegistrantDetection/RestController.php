<?php
/**
 * Rest endpoint for fetching entity registrant detail.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\EntityRegistrantDetection;

use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Response;
use WP_REST_Request;
use WP_REST_Server;

/**
 * Class RestController
 *
 * @since   2.2
 * @package AmpProject\AmpWP
 */
class RestController extends WP_REST_Controller implements Service, Registerable, Delayed {

	/**
	 * The namespace of this controller's route.
	 *
	 * @var string
	 */
	protected $namespace;

	/**
	 * The base of this controller's route.
	 *
	 * @var string
	 */
	protected $rest_base;

	/**
	 * Cached results of get_item_schema.
	 *
	 * @var array
	 */
	protected $schema;

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->namespace = 'amp/v1';
		$this->rest_base = 'entity-registrants';
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
			'/' . $this->rest_base,
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_items' ],
					'permission_callback' => [ $this, 'get_items_permissions_check' ],
					'args'                => [
						EntityRegistrantDetectionManager::NONCE_QUERY_VAR => [
							'description' => __( 'Nonce value.', 'amp' ),
							'type'        => 'string',
							'required'    => true,
						],
					],
				],
				'schema' => [ $this, 'get_public_item_schema' ],
			]
		);
	}

	/**
	 * Checks if a given request has access to get items.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return bool|WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function get_items_permissions_check( $request ) {

		$nonce_value = $request->get_param( EntityRegistrantDetectionManager::NONCE_QUERY_VAR );

		if ( ! EntityRegistrantDetectionManager::verify_nonce( $nonce_value ) ) {
			return new WP_Error(
				'http_request_failed',
				__( 'Nonce authentication failed.', 'amp' )
			);
		}

		return current_user_can( 'manage_options' );
	}

	/**
	 * Retrieves a collection of items.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {

		return rest_ensure_response(
			[
				'post_types' => EntityRegistrantDetectionManager::$post_types_source,
				'taxonomies' => EntityRegistrantDetectionManager::$taxonomies_source,
				'blocks'     => EntityRegistrantDetectionManager::$blocks_source,
				'shortcodes' => EntityRegistrantDetectionManager::$shortcodes_source,
			]
		);
	}
}