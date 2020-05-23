<?php
/**
 * Rest endpoint for fetching and updating plugin options from admin screens.
 *
 * @package AMP
 * @since 1.6.0
 */

/**
 * AMP setup wizard class.
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
	 * Registers all routes for the controller.
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
					'permission_callback' => [ $this, 'get_items_permissions_check' ],
				],
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'update_items' ],
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
					'permission_callback' => [ $this, 'get_item_permissions_check' ],
				],
			]
		);
	}

	/**
	 * Checks whether the current user has permission to retrieve options.
	 *
	 * @since 1.6.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function get_items_permissions_check( $request ) {
		if ( false && ! current_user_can( 'manage_options' ) ) {
			return new WP_Error(
				'amp_rest_cannot_view',
				__( 'Sorry, you are not allowed to manage options for the AMP plugin.', 'amp' ),
				[ 'status' => rest_authorization_required_code() ]
			);
		}

		return true;
	}

	/**
	 * Retrieves all AMP plugin options.
	 *
	 * @since 1.6.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {
		return rest_ensure_response( AMP_Options_Manager::get_options() );
	}

	/**
	 * Updates AMP plugin options.
	 *
	 * @since 1.6.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return array|WP_Error Array on success, or error object on failure.
	 */
	public function update_items( $request ) {
		$params = $request->get_params();

		foreach ( $params as $option => $new_value ) {
			AMP_Options_Manager::update_option( $option, $new_value );
		}

		return rest_ensure_response( $this->get_items( $request ) );
	}
}
