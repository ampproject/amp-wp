<?php
/**
 * ValidationCountsRestController class.
 *
 * @package AmpProject\AmpWP
 * @since 2.1
 */

namespace AmpProject\AmpWP\Validation;

use AMP_Validated_URL_Post_Type;
use AMP_Validation_Error_Taxonomy;
use AmpProject\AmpWP\DevTools\UserAccess;
use WP_Error;
use WP_REST_Controller;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Rest endpoint for fetching the total count of unreviewed validation URLs and validation errors.
 *
 * @since 2.1
 * @internal
 */
final class ValidationCountsRestController extends WP_REST_Controller implements Delayed, Service, Registerable {

	/** @var UserAccess DevTools User Access */
	private $dev_tools_user_access;

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
	 * @param UserAccess $user_access Instance of UserAccess class.
	 */
	public function __construct( UserAccess $user_access ) {
		$this->namespace             = 'amp/v1';
		$this->rest_base             = 'unreviewed-validation-counts';
		$this->dev_tools_user_access = $user_access;
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
					'args'                => [],
					'permission_callback' => [ $this, 'get_items_permissions_check' ],
				],
				'schema' => [ $this, 'get_public_item_schema' ],
			]
		);
	}

	/**
	 * Checks whether the current user has access to Dev Tools.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has permission; WP_Error object otherwise.
	 */
	public function get_items_permissions_check( $request ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$dev_tools_access = $this->dev_tools_user_access->is_user_enabled();

		if ( ! $dev_tools_access ) {
			return new WP_Error(
				'amp_rest_no_dev_tools_access',
				__( 'Sorry, you are not allowed to view unreviewed counts for validation errors.', 'amp' ),
				[ 'status' => rest_authorization_required_code() ]
			);
		}

		return true;
	}

	/**
	 * Retrieves total unreviewed count for validation URLs and errors.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$unreviewed_validated_url_count    = AMP_Validated_URL_Post_Type::get_validation_error_urls_count();
		$unreviewed_validation_error_count = AMP_Validation_Error_Taxonomy::get_validation_error_count(
			[
				'group' => [
					AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_REJECTED_STATUS,
					AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_ACCEPTED_STATUS,
				],
			]
		);

		$response = [
			'validated_urls' => $unreviewed_validated_url_count,
			'errors'         => $unreviewed_validation_error_count,
		];

		return rest_ensure_response( $response );
	}

	/**
	 * Retrieves the block type' schema, conforming to JSON Schema.
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		return [
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'amp-wp-validation-status',
			'type'       => 'object',
			'properties' => [
				'validation_urls' => [
					'type'     => 'integer',
					'readonly' => true,
				],
				'errors'          => [
					'type'     => 'integer',
					'readonly' => true,
				],
			],
		];
	}
}
