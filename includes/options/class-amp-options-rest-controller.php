<?php
/**
 * Rest endpoint for fetching and updating plugin options from admin screens.
 *
 * @package AMP
 * @since 1.6.0
 */

use AmpProject\AmpWP\Option;

/**
 * AMP options REST controller.
 *
 * @since 1.6.0
 */
final class AMP_Options_REST_Controller extends WP_REST_Controller {

	/**
	 * Constructor.
	 *
	 * @since 1.6.0
	 */
	public function __construct() {
		$this->namespace = 'amp-wp/v1';
		$this->rest_base = 'options';
	}

	/**
	 * Registers routes for the controller.
	 *
	 * @since 1.6.0
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
				[
					'methods'             => WP_REST_SERVER::READABLE,
					'callback'            => [ $this, 'get_items' ],
					'args'                => [],
					'permission_callback' => [ $this, 'get_items_permissions_check' ],
				],
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'update_items' ],
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
					'permission_callback' => [ $this, 'get_items_permissions_check' ],
				],
				'schema' => $this->get_public_item_schema(),
			]
		);
	}

	/**
	 * Checks whether the current user has permission to manage AMP plugin options.
	 *
	 * @since 1.6.0
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function get_items_permissions_check( $request ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'amp_wp_rest_cannot_view',
				__( 'Sorry, you are not allowed to manage options for the AMP plugin for WordPress.', 'amp' ),
				[ 'status' => rest_authorization_required_code() ]
			);
		}

		return true;
	}

	/**
	 * Retrieves all AMP plugin options specified in the endpoint schema.
	 *
	 * @since 1.6.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response Response object.
	 */
	public function get_items( $request ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$options    = AMP_Options_Manager::get_options();
		$properties = array_keys( $this->get_item_schema()['properties'] );

		foreach ( array_keys( $options ) as $option ) {
			if ( ! in_array( $option, $properties, true ) ) {
				unset( $options[ $option ] );
			}
		}

		return rest_ensure_response( $options );
	}

	/**
	 * Updates AMP plugin options specified in the endpoint schema.
	 *
	 * @since 1.6.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response Response with updated options.
	 */
	public function update_items( $request ) {
		$params     = $request->get_params();
		$schema     = $this->get_item_schema();
		$properties = array_keys( $schema['properties'] );

		foreach ( $params as $option => $new_value ) {
			if ( in_array( $option, $properties, true ) ) {
				AMP_Options_Manager::update_option( $option, $new_value );
			}
		}

		return rest_ensure_response( $this->get_items( $request ) );
	}

	/**
	 * Retrieves the schema for plugin options provided by the endpoint.
	 *
	 * @since 1.6.0
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		if ( ! $this->schema ) {
			$this->schema = [
				'$schema'    => 'http://json-schema.org/draft-04/schema#',
				'title'      => 'amp-wp-options',
				'type'       => 'object',
				'properties' => [
					Option::THEME_SUPPORT => [
						'description' => __( 'AMP template mode.', 'amp' ),
						'default'     => AMP_Theme_Support::READER_MODE_SLUG,
						'type'        => 'string',
						'enum'        => [
							AMP_Theme_Support::READER_MODE_SLUG,
							AMP_Theme_Support::TRANSITIONAL_MODE_SLUG,
							AMP_Theme_Support::STANDARD_MODE_SLUG,
						],
						'arg_options' => [
							'sanitize_callback' => 'sanitize_text_field',
							'validate_callback' => 'rest_validate_request_arg',
						],
					],
				],
			];
		}

		return $this->schema;
	}
}
