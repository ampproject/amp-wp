<?php
/**
 * Rest endpoint for fetching and updating plugin options from admin screens.
 *
 * @package AMP
 * @since 1.6.0
 */

use AmpProject\AmpWP\Option;

/**
 * AMP_Options_REST_Controller class.
 *
 * @since 1.6.0
 */
final class AMP_Options_REST_Controller extends WP_REST_Controller {

	/**
	 * Reader themes provider class.
	 *
	 * @var AMP_Reader_Themes
	 */
	private $reader_themes;

	/**
	 * Cached results of get_item_schema.
	 *
	 * @var array
	 */
	protected $schema;

	/**
	 * Constructor.
	 *
	 * @param AMP_Reader_Themes $reader_themes Reader themes helper class instance.
	 */
	public function __construct( $reader_themes ) {
		$this->namespace     = 'amp/v1';
		$this->rest_base     = 'options';
		$this->reader_themes = $reader_themes;
	}

	/**
	 * Registers all routes for the controller.
	 */
	public function register_routes() {
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
	 * Retrieves all AMP plugin options.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		$options    = AMP_Options_Manager::get_options();
		$properties = $this->get_item_schema()['properties'];

		$options = wp_array_slice_assoc( $options, array_keys( $properties ) );
		return rest_ensure_response( $options );
	}

	/**
	 * Updates AMP plugin options.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return array|WP_Error Array on success, or error object on failure.
	 */
	public function update_items( $request ) {
		$params = $request->get_params();

		AMP_Options_Manager::update_options( wp_array_slice_assoc( $params, array_keys( $this->get_item_schema()['properties'] ) ) );

		return rest_ensure_response( $this->get_items( $request ) );
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
				'title'      => 'amp-wp-options',
				'type'       => 'object',
				'properties' => [
					// Note: The sanitize_callback from AMP_Options_Manager::register_settings() is applying to this option.
					Option::THEME_SUPPORT   => [
						'type' => 'string',
						'enum' => [
							AMP_Theme_Support::READER_MODE_SLUG,
							AMP_Theme_Support::STANDARD_MODE_SLUG,
							AMP_Theme_Support::TRANSITIONAL_MODE_SLUG,
						],
					],
					Option::READER_THEME    => [
						'type'        => 'string',
						'arg_options' => [
							'validate_callback' => function ( $value ) {
								// Note: The validate_callback is used instead of enum in order to prevent leaking the list of themes.
								return in_array( $value, wp_list_pluck( $this->reader_themes->get_themes(), 'slug' ), true );
							},
						],
					],
					Option::MOBILE_REDIRECT => [
						'type'    => 'boolean',
						'default' => false,
					],
				],
			];
		}

		return $this->schema;
	}
}
