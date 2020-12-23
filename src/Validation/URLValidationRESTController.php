<?php
/**
 * REST endpoint providing theme scan results.
 *
 * @package AMP
 * @since 2.1
 */

namespace AmpProject\AmpWP\Validation;

use AMP_Validated_URL_Post_Type;
use AMP_Validation_Error_Taxonomy;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * URLValidationRESTController class.
 *
 * @since 2.1
 */
final class URLValidationRESTController extends WP_REST_Controller implements Delayed, Service, Registerable {

	/**
	 * URLValidationProvider instance.
	 *
	 * @var URLValidationProvider
	 */
	private $url_validation_provider;

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
	 *
	 * @param URLValidationProvider $url_validation_provider URLValidationProvider instance.
	 */
	public function __construct( URLValidationProvider $url_validation_provider ) {
		$this->namespace               = 'amp/v1';
		$this->url_validation_provider = $url_validation_provider;
	}

	/**
	 * Registers all routes for the controller.
	 */
	public function register() {
		register_rest_route(
			$this->namespace,
			'/validate_post_url',
			[
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'validate_post_url' ],
					'args'                => [
						'id' => [
							'type' => 'int',
						],
					],
					'permission_callback' => [ $this, 'update_items_permissions_check' ],
				],
				'schema' => $this->get_public_item_schema(),
			]
		);

		// @todo Additional endpoint to validate a URL (from a URL rather than a post ID).
	}

	/**
	 * Checks whether the current user has permission to manage options.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has permission; WP_Error object otherwise.
	 */
	public function update_items_permissions_check( $request ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
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
	 * Returns validation information about a URL, validating the URL along the way.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function validate_post_url( $request ) {
		$post = get_post( $request['id'] );
		$this->url_validation_provider->get_url_validation( amp_get_permalink( $post ), get_post_type( $post ), true );
		$validation_status_post = AMP_Validated_URL_Post_Type::get_invalid_url_post( amp_get_permalink( $post ) );

		$response = [
			'results'     => [],
			'review_link' => get_edit_post_link( $validation_status_post->ID, 'raw' ),
		];

		foreach ( AMP_Validated_URL_Post_Type::get_invalid_url_validation_errors( $validation_status_post ) as $result ) {
			$response['results'][] = [
				'sanitized'   => AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACK_ACCEPTED_STATUS === $result['status'],
				'title'       => AMP_Validation_Error_Taxonomy::get_error_title_from_code( $result['data'] ),
				'error'       => $result['data'],
				'status'      => $result['status'],
				'term_status' => $result['term_status'],
				'forced'      => $result['forced'],
				'term_id'     => $result['term']->term_id,
			];
		}

		return rest_ensure_response( $response );
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
				'title'      => 'amp-wp-url-validation',
				'type'       => 'object',
				'properties' => [
					'results'     => [
						'type' => 'array',
					],
					'review_link' => [
						'type' => 'string',
					],
				],
			];
		}

		return $this->schema;
	}
}
