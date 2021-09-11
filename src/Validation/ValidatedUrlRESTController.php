<?php
/**
 * REST endpoint providing Validated URL data.
 *
 * @package AMP
 * @since   2.2
 */

namespace AmpProject\AmpWP\Validation;

use AMP_Validated_URL_Post_Type;
use AmpProject\AmpWP\DevTools\UserAccess;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use WP_Error;
use WP_REST_Controller;
use WP_Post;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * ValidatedUrlRESTController class.
 *
 * @since 2.2
 * @internal
 */
final class ValidatedUrlRESTController extends WP_REST_Controller implements Delayed, Service, Registerable {

	/**
	 * UserAccess instance.
	 *
	 * @var UserAccess
	 */
	private $dev_tools_user_access;

	/**
	 * ValidatedUrlDataProvider instance.
	 *
	 * @var ValidatedUrlDataProvider
	 */
	private $validated_url_data_provider;

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
	 * @param UserAccess               $dev_tools_user_access       UserAccess instance.
	 * @param ValidatedUrlDataProvider $validated_url_data_provider ValidatedUrlDataProvider instance.
	 */
	public function __construct( UserAccess $dev_tools_user_access, ValidatedUrlDataProvider $validated_url_data_provider ) {
		$this->namespace                   = 'amp/v1';
		$this->dev_tools_user_access       = $dev_tools_user_access;
		$this->validated_url_data_provider = $validated_url_data_provider;
	}

	/**
	 * Registers all routes for the controller.
	 */
	public function register() {
		register_rest_route(
			$this->namespace,
			'/validated-urls/(?P<id>[\d]+)',
			[
				'args'   => [
					'id' => [
						'description'       => __( 'Post ID for the AMP validated URL post.', 'amp' ),
						'required'          => true,
						'type'              => 'integer',
						'minimum'           => 1,
						'validate_callback' => [ $this, 'validate_post_id_param' ],
					],
				],
				[
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::READABLE ),
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_item' ],
					'permission_callback' => [ $this, 'get_item_permissions_check' ],
				],
				'schema' => [ $this, 'get_item_schema' ],
			]
		);
	}

	/**
	 * Validate post ID param.
	 *
	 * @param string|int      $id      Post ID.
	 * @param WP_REST_Request $request REST request.
	 * @param string          $param   Param name ('id').
	 *
	 * @return true|WP_Error True on valid, WP_Error otherwise.
	 */
	public function validate_post_id_param( $id, $request, $param ) {
		// First enforce the schema to ensure $id is an integer greater than 0.
		$validity = rest_validate_request_arg( $id, $request, $param );
		if ( is_wp_error( $validity ) ) {
			return $validity;
		}

		// Make sure the post exists and is of correct post type.
		$post = get_post( (int) $id );
		if (
			! $post instanceof WP_Post
			||
			AMP_Validated_URL_Post_Type::POST_TYPE_SLUG !== get_post_type( $post )
		) {
			return new WP_Error(
				'rest_amp_validated_url_post_invalid_id',
				__( 'Invalid post ID.', 'default' ),
				[ 'status' => 404 ]
			);
		}

		return true;
	}

	/**
	 * Checks whether the current user can view results of a URL AMP validation.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return true|WP_Error True if the request has permission; WP_Error object otherwise.
	 */
	public function get_item_permissions_check( $request ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
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
	 * Returns validation information about a URL.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_item( $request ) {
		$post_id            = (int) $request['id'];
		$validated_url_data = $this->validated_url_data_provider->for_id( $post_id );

		if ( is_wp_error( $validated_url_data ) ) {
			return $validated_url_data;
		}

		$data = [
			'id'          => $validated_url_data->get_id(),
			'url'         => $validated_url_data->get_url(),
			'date'        => $validated_url_data->get_date(),
			'author'      => $validated_url_data->get_author(),
			'stylesheets' => $validated_url_data->get_stylesheets(),
			'environment' => $validated_url_data->get_environment(),
		];

		return rest_ensure_response( $this->filter_response_by_context( $data, $request['context'] ) );
	}

	/**
	 * Retrieves the schema for Validated URL data provided by the endpoint.
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		if ( $this->schema ) {
			return $this->schema;
		}

		$this->schema = [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'amp-wp-validated-url',
			'type'       => 'object',
			'properties' => [
				'id'          => [
					'description' => esc_html__( 'Unique identifier for the object.', 'amp' ),
					'type'        => 'integer',
					'readonly'    => true,
				],
				'url'         => [
					'description' => esc_html__( 'URL that was validated.', 'amp' ),
					'type'        => 'string',
					'readonly'    => true,
				],
				'date'        => [
					'description' => esc_html__( 'Date that the URL was validated.', 'amp' ),
					'type'        => 'string',
					'readonly'    => true,
				],
				'author'      => [
					'description' => esc_html__( 'User ID who last validated the URL.', 'amp' ),
					'type'        => 'integer',
					'readonly'    => true,
				],
				'stylesheets' => [
					'description' => esc_html__( 'Stylesheet data.', 'amp' ),
					'type'        => 'array',
					'readonly'    => true,
					'items'       => [
						'type'       => 'object',
						'properties' => [
							'group'              => [
								'type'     => 'string',
								'readonly' => true,
							],
							'original_size'      => [
								'type'     => 'integer',
								'readonly' => true,
							],
							'final_size'         => [
								'type'     => 'integer',
								'readonly' => true,
							],
							'element'            => [
								'type'       => 'object',
								'readonly'   => true,
								'properties' => [
									'name'       => [
										'type'     => 'string',
										'readonly' => true,
									],
									'attributes' => [
										'type'     => 'object',
										'readonly' => true,
									],
								],
							],
							'origin'             => [
								'type'     => 'string',
								'readonly' => true,
							],
							'sources'            => [
								'type'     => 'array',
								'readonly' => true,
								'items'    => [
									'type'     => 'object',
									'readonly' => true,
								],
							],
							'priority'           => [
								'type'     => 'integer',
								'readonly' => true,
							],
							'hash'               => [
								'type'     => 'string',
								'readonly' => true,
							],
							'parse_time'         => [
								'type'     => 'number',
								'readonly' => true,
							],
							'shake_time'         => [
								'type'     => 'number',
								'readonly' => true,
							],
							'cached'             => [
								'type'     => 'string',
								'readonly' => true,
							],
							'imported_font_urls' => [
								'type'     => 'string',
								'readonly' => true,
							],
							'shaken_tokens'      => [
								'type'     => 'array',
								'readonly' => true,
							],
							'included'           => [
								'type'     => 'string',
								'readonly' => true,
							],
							'original_tag_abbr'  => [
								'type'     => 'string',
								'readonly' => true,
							],
							'original_tag'       => [
								'type'     => 'string',
								'readonly' => true,
							],
						],
					],
				],
				'environment' => [
					'description' => esc_html__( 'Validated environment information.', 'amp' ),
					'type'        => 'object',
					'readonly'    => true,
					'properties'  => [
						'theme'   => [
							'type'     => 'object',
							'readonly' => true,
						],
						'plugins' => [
							'type'     => 'object',
							'readonly' => true,
						],
						'options' => [
							'type'     => 'object',
							'readonly' => true,
						],
					],
				],
			],
		];

		return $this->schema;
	}
}
