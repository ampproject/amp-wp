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
 * ValidationRESTController class.
 *
 * @since 2.1
 */
final class ValidationRESTController extends WP_REST_Controller implements Delayed, Service, Registerable {

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
		$this->rest_base = 'validate';
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
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'validate_urls' ],
					'args'                => [
						'urls' => [
							'default' => [],
							'type'    => 'array',
							'items'   => [
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
					'permission_callback' => [ $this, 'get_items_permissions_check' ],
				],
				'schema' => $this->get_public_item_schema(),
			]
		);
	}

	/**
	 * Checks whether the current user has permission to manage options.
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
	 * Given a list of URLs, validates the URLs one by one, returning results with the remaining URLs after the first unstored validation check.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function validate_urls( $request ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$validation_provider = new ValidationProvider();

		return $validation_provider->with_lock(
			function() use ( $request, $validation_provider ) {
				$results = [];
				$urls    = $request['urls'];

				while ( ! empty( $urls ) ) {
					$url = array_shift( $urls );

					$validation = $validation_provider->get_url_validation( $url['url'], $url['type'] );

					if ( ! empty( $validation['validity'] ) ) {
						$results[] = $validation['validity'];
					}

					// Return after the first URL is validated. The frontend application will make multiple requests until there are no more URLs.
					if ( true === $validation['revalidated'] ) {
						break;
					}
				}

				return rest_ensure_response(
					[
						'results'           => $results,
						'total_errors'      => $validation_provider->total_errors,
						'unaccepted_errors' => $validation_provider->unaccepted_errors,
						'validity_by_type'  => $validation_provider->validity_by_type,
						'remaining_urls'    => $urls,
					]
				);
			}
		);
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
				'title'      => 'amp-wp-validation',
				'type'       => 'object',
				'properties' => [
					'results'           => [
						'type' => 'array',
					],
					'total_errors'      => [
						'type' => 'integer',
					],
					'unaccepted_errors' => [
						'type' => 'integer',
					],
					'validity_by_type'  => [
						'type' => 'object',
					],
					'remaining_urls'    => [
						'type' => 'array',
					],
				],
			];
		}

		return $this->schema;
	}
}
