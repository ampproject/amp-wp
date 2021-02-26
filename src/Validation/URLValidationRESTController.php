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
use AmpProject\AmpWP\DevTools\UserAccess;
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
	 * UserAccess instance.
	 *
	 * @var UserAccess
	 */
	private $dev_tools_user_access;

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
	 * @param UserAccess            $dev_tools_user_access UserAccess instance.
	 */
	public function __construct( URLValidationProvider $url_validation_provider, UserAccess $dev_tools_user_access ) {
		$this->namespace               = 'amp/v1';
		$this->url_validation_provider = $url_validation_provider;
		$this->dev_tools_user_access   = $dev_tools_user_access;
	}

	/**
	 * Registers all routes for the controller.
	 */
	public function register() {
		register_rest_route(
			$this->namespace,
			'/validate-post-url/(?P<id>[\d]+)',
			[
				'args'   => [
					'id'            => [
						'description' => __( 'Unique identifier for the object.', 'amp' ),
						'required'    => true,
						'type'        => 'integer',
					],
					'preview_nonce' => [
						'description' => __( 'Preview nonce string.', 'amp' ),
						'required'    => false,
						'type'        => 'string',
					],
				],
				[
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'validate_post_url' ],
					'permission_callback' => [ $this, 'create_item_permissions_check' ],
				],
				'schema' => [ $this, 'get_public_item_schema' ],
			]
		);

		// @todo Additional endpoint to validate a URL (from a URL rather than a post ID).
	}

	/**
	 * Checks whether the current user can view AMP validation results.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has permission; WP_Error object otherwise.
	 */
	public function create_item_permissions_check( $request ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		if ( ! $this->dev_tools_user_access->is_user_enabled() ) {
			return new WP_Error(
				'amp_rest_no_dev_tools',
				__( 'Sorry, you do not have access to dev tools for the AMP plugin for WordPress.', 'amp' ),
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
		$post_id       = (int) $request['id'];
		$preview_nonce = $request['preview_nonce'];
		$url           = amp_get_permalink( $post_id );

		if ( ! empty( $preview_nonce ) ) {
			$url = add_query_arg(
				[
					'preview'       => 1,
					'preview_id'    => $post_id,
					'preview_nonce' => $preview_nonce,
				],
				$url
			);
		}

		$validation_results = $this->url_validation_provider->get_url_validation( $url, get_post_type( $post_id ), true );
		if ( is_wp_error( $validation_results ) ) {
			return $validation_results;
		}

		$data = [
			'results'     => [],
			'review_link' => get_edit_post_link( $validation_results['post_id'], 'raw' ),
		];

		foreach ( AMP_Validated_URL_Post_Type::get_invalid_url_validation_errors( $validation_results['post_id'] ) as $result ) {

			// Handle case where a validationError's `sources` are an object (with numeric keys).
			// Note: this will no longer be an issue after https://github.com/ampproject/amp-wp/commit/bbb0e495a817a56b37554dfd721170712c92d7b8
			// but is still required for validation errors stored in the database prior to that commit.
			if ( isset( $result['data']['sources'] ) ) {
				$result['data']['sources'] = array_values( $result['data']['sources'] );
			}

			$data['results'][] = [
				'error'       => $result['data'],
				'forced'      => $result['forced'],
				'sanitized'   => AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACK_ACCEPTED_STATUS === $result['status'],
				'status'      => $result['status'],
				'term_id'     => $result['term']->term_id,
				'term_status' => $result['term_status'],
				'title'       => AMP_Validation_Error_Taxonomy::get_error_title_from_code( $result['data'] ),
			];
		}

		return rest_ensure_response( $this->filter_response_by_context( $data, $request['context'] ) );
	}

	/**
	 * Retrieves the schema for plugin options provided by the endpoint.
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		if ( $this->schema ) {
			return $this->schema;
		}

		$sources_type = [
			'items' => [
				'type' => 'object',
			],
			'type'  => 'array',
		];

		$sources_type['items']['properties']['sources'] = $sources_type;

		$this->schema = [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'amp-wp-url-validation',
			'type'       => 'object',
			'properties' => [
				'results'     => [
					'description' => __( 'Validation errors for the post.', 'amp' ),
					'readonly'    => true,
					'type'        => 'array',
					'items'       => [
						'type'       => 'object',
						'properties' => [
							'error'       => [
								'properties' => [
									'code'            => [
										'context' => [],
										'type'    => 'string',
									],
									'node_attributes' => [
										'context' => [],
										'type'    => 'object',
									],
									'node_name'       => [
										'context' => [],
										'type'    => 'string',
									],
									'node_type'       => [
										'context' => [],
										'type'    => 'integer',
									],
									'parent_name'     => [
										'context' => [],
										'type'    => 'string',
									],
									'sources'         => $sources_type,
									'type'            => [
										'type' => 'string',
									],
								],
								'type'       => 'object',
							],
							'forced'      => [
								'context' => [],
								'type'    => 'boolean',
							],
							'sanitized'   => [
								'context' => [],
								'type'    => 'boolean',
							],
							'status'      => [
								'type' => 'integer',
							],
							'term_id'     => [
								'type' => 'integer',
							],
							'term_status' => [
								'context' => [],
								'type'    => 'integer',
							],
							'title'       => [
								'type' => 'string',
							],
						],
					],
				],
				'review_link' => [
					'description' => __( 'The URL where validation errors can be reviewed.', 'amp' ),
					'readonly'    => true,
					'type'        => 'string',
				],
			],
		];

		return $this->schema;
	}
}
