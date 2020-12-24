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
	 * The URL validation context in the editor.
	 *
	 * @var string
	 */
	const CONTEXT_EDITOR = 'amp-editor';

	/**
	 * URLValidationProvider instance.
	 *
	 * @var URLValidationProvider
	 */
	private $url_validation_provider;

	/**
	 * DevToolsUserAccess instance.
	 *
	 * @var DevToolsUserAccess
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
					'context' => [
						'description' => __( 'The request context.', 'amp' ),
						'enum'        => [
							self::CONTEXT_EDITOR,
						],
						'required'    => true,
						'type'        => 'string',
					],
					'id'      => [
						'description' => __( 'Unique identifier for the object.', 'amp' ),
						'required'    => true,
						'type'        => 'integer',
					],
				],
				[
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::READABLE ),
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'validate_post_url' ],
					'permission_callback' => [ $this, 'get_items_permissions_check' ],
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
	public function get_items_permissions_check( $request ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
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
		$post = get_post( $request['id'] );
		$this->url_validation_provider->get_url_validation( amp_get_permalink( $post ), get_post_type( $post ), true );
		$validation_status_post = AMP_Validated_URL_Post_Type::get_invalid_url_post( amp_get_permalink( $post ) );

		$data = [
			'results'     => [],
			'review_link' => get_edit_post_link( $validation_status_post->ID, 'raw' ),
		];

		foreach ( AMP_Validated_URL_Post_Type::get_invalid_url_validation_errors( $validation_status_post ) as $result ) {
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
				'context'    => [ self::CONTEXT_EDITOR ],
				'properties' => [
					'block_content_index' => [
						'context' => [ self::CONTEXT_EDITOR ],
						'type'    => 'integer',
					],
					'block_name'          => [
						'context' => [ self::CONTEXT_EDITOR ],
						'type'    => 'string',
					],
					'dependency_handle'   => [
						'context' => [],
						'type'    => 'string',
					],
					'dependency_type'     => [
						'context' => [],
						'type'    => 'string',
					],
					'extra_key'           => [
						'context' => [],
						'type'    => 'string',
					],
					'file'                => [
						'context' => [],
						'type'    => 'string',
					],
					'function'            => [
						'context' => [],
						'type'    => 'string',
					],
					'handle'              => [
						'context' => [],
						'type'    => 'string',
					],
					'hook'                => [
						'context' => [],
						'type'    => 'string',
					],
					'line'                => [
						'context' => [],
						'type'    => 'integer',
					],
					'name'                => [
						'context' => [],
						'type'    => 'string',
					],
					'post_id'             => [
						'context' => [ self::CONTEXT_EDITOR ],
						'type'    => 'integer',
					],
					'priority'            => [
						'context' => [],
						'type'    => 'integer',
					],
					'text'                => [
						'context' => [],
						'type'    => 'string',
					],
					'type'                => [
						'context' => [],
						'type'    => 'string',
					],
				],
				'type'       => 'object',
			],
			'type'  => 'array',
		];

		$sources_type['items']['properties']['sources'] = $sources_type;
		$sources_type['context']                        = [ self::CONTEXT_EDITOR ];

		$this->schema = [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'amp-wp-url-validation',
			'type'       => 'object',
			'properties' => [
				'results'     => [
					'context'     => [ self::CONTEXT_EDITOR ],
					'description' => __( 'Validation errors for the post.', 'amp' ),
					'readonly'    => true,
					'type'        => 'array',
					'items'       => [
						'context'    => [ self::CONTEXT_EDITOR ],
						'type'       => 'object',
						'properties' => [
							'error'       => [
								'context'    => [ self::CONTEXT_EDITOR ],
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
										'context' => [ self::CONTEXT_EDITOR ],
										'type'    => 'string',
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
								'context' => [ self::CONTEXT_EDITOR ],
								'type'    => 'integer',
							],
							'term_id'     => [
								'context' => [ self::CONTEXT_EDITOR ],
								'type'    => 'integer',
							],
							'term_status' => [
								'context' => [],
								'type'    => 'integer',
							],
							'title'       => [
								'context' => [ self::CONTEXT_EDITOR ],
								'type'    => 'string',
							],
						],
					],
				],
				'review_link' => [
					'context'     => [ self::CONTEXT_EDITOR ],
					'description' => __( 'The URL where validation errors can be reviewed.', 'amp' ),
					'readonly'    => true,
					'type'        => 'string',
				],
			],
		];

		return $this->schema;
	}
}
