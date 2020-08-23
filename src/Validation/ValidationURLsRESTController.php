<?php
/**
 * REST endpoint providing theme scan results.
 *
 * @package AMP
 * @since 2.1
 */

namespace AmpProject\AmpWP\Validation;

use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * ValidationURLsRESTController class.
 *
 * @since 2.1
 */
final class ValidationURLsRESTController extends WP_REST_Controller implements Delayed, Service, Registerable {

	/**
	 * Response schema.
	 *
	 * @var array
	 */
	protected $schema;

	/**
	 * Get the action to use for registering the service.
	 *
	 * @return string Registration action to use.
	 */
	public static function get_registration_action() {
		return 'rest_api_init';
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->namespace = 'amp/v1';
		$this->rest_base = 'validation-urls';
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
					'callback'            => [ $this, 'get_urls' ],
					'args'                => [
						'include' => [
							'default'     => [],
							'description' => __( 'Array of callbacks. If set, a URL will only be scanned if one is true.', 'amp' ),
							'type'        => 'array',
							'items'       => [
								'type' => 'string',
							],
						],
						'limit'   => [
							'default'     => 2,
							'description' => __( 'The maximum number of URLs to validate for each type.', 'amp' ),
							'type'        => 'integer',
						],
						'offset'  => [
							'default'     => 0,
							'description' => __( 'The number of URLs to offset by for each content type.', 'amp' ),
							'type'        => 'integer',
						],

					],
					'permission_callback' => [ $this, 'get_items_permissions_check' ],
				],
				'schema' => $this->get_public_item_schema(),
			]
		);
	}

	/**
	 * Checks whether the current user has permission to receive URLs.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has permission; WP_Error object otherwise.
	 */
	public function get_items_permissions_check( $request ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'amp_rest_cannot_manage_options',
				__( 'Sorry, you are not allowed to manage options for the AMP plugin for WordPress.', 'amp' ),
				[ 'status' => rest_authorization_required_code() ]
			);
		}

		return true;
	}

	/**
	 * Retrieves URLs to scan.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_urls( $request ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$validation_url_provider = new ValidationURLProvider( $request['limit'], $request['include'], true );

		$urls = $validation_url_provider->get_urls( $request['offset'] );

		return rest_ensure_response( compact( 'urls' ) );
	}

	/**
	 * Retrieves the schema for plugin options provided by the endpoint.
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		if ( ! $this->schema ) {
			$this->schema = [
				'$schema'    => 'http://json-schema.org/draft-04/schema#',
				'title'      => 'amp-wp-validation-urls',
				'properties' => [
					'urls' => [
						'type'  => 'array',
						'items' => [
							'type'       => 'object',
							'properties' => [
								'url'  => [
									'type' => 'string',
								],
								'type' => [
									'type' => 'string',
								],
							],
						],
					],
				],
			];
		}

		return $this->schema;
	}
}
