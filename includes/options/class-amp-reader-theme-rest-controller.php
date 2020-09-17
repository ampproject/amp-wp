<?php
/**
 * Reader theme controller.
 *
 * @package AMP
 * @since 2.0
 */

use AmpProject\AmpWP\Admin\ReaderThemes;

/**
 * AMP reader theme REST controller.
 *
 * @since 2.0
 * @internal
 */
final class AMP_Reader_Theme_REST_Controller extends WP_REST_Controller {

	/**
	 * Reader themes provider class.
	 *
	 * @var ReaderThemes
	 */
	private $reader_themes;

	/**
	 * Constructor.
	 *
	 * @param ReaderThemes $reader_themes ReaderThemes instance to provide theme data.
	 */
	public function __construct( ReaderThemes $reader_themes ) {
		$this->reader_themes = $reader_themes;
		$this->namespace     = 'amp/v1';
		$this->rest_base     = 'reader-themes';
	}

	/**
	 * Registers routes for the controller.
	 *
	 * @since 2.0
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_items' ],
					'permission_callback' => [ $this, 'get_items_permissions_check' ],
					'args'                => [],
				],
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
	 * Retrieves all available reader themes.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response Response object.
	 */
	public function get_items( $request ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$themes = $this->reader_themes->get_themes();

		$response = rest_ensure_response( $themes );

		$themes_api_error = $this->reader_themes->get_themes_api_error();
		if ( is_wp_error( $themes_api_error ) ) {
			$response->header( 'X-AMP-Theme-API-Error', $themes_api_error->get_error_message() );
		}

		return $response;
	}
}
