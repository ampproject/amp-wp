<?php
/**
 * Rest endpoint for fetching and updating plugin options from admin screens.
 *
 * @package AMP
 * @since 2.0
 */

namespace AmpProject\AmpWP;

use AMP_Options_Manager;
use AMP_Post_Type_Support;
use AMP_Theme_Support;
use AmpProject\AmpWP\Admin\OnboardingWizardSubmenu;
use AmpProject\AmpWP\Admin\ReaderThemes;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * OptionsRESTController class.
 *
 * @since 2.0
 * @internal
 */
final class OptionsRESTController extends WP_REST_Controller implements Delayed, Service, Registerable {

	/**
	 * Key for a preview permalink added to the endpoint data.
	 *
	 * @var string
	 */
	const PREVIEW_PERMALINK = 'preview_permalink';

	/**
	 * Key for suppressible plugins data added to the endpoint.
	 *
	 * @var string
	 */
	const SUPPRESSIBLE_PLUGINS = 'suppressible_plugins';

	/**
	 * Key for post type data.
	 *
	 * @var string
	 */
	const SUPPORTABLE_POST_TYPES = 'supportable_post_types';

	/**
	 * Key for supportable templates data.
	 *
	 * @var string
	 */
	const SUPPORTABLE_TEMPLATES = 'supportable_templates';

	/**
	 * Key for the read-only property providing a link to the onboarding wizard if available.
	 *
	 * @var string
	 */
	const ONBOARDING_WIZARD_LINK = 'onboarding_wizard_link';

	/**
	 * Key for the read-only customizer link property.
	 *
	 * @var string
	 */
	const CUSTOMIZER_LINK = 'customizer_link';

	/**
	 * Reader themes provider class.
	 *
	 * @var ReaderThemes
	 */
	private $reader_themes;

	/**
	 * PluginSuppression instance.
	 *
	 * @var PluginSuppression
	 */
	private $plugin_suppression;

	/**
	 * Cached results of get_item_schema.
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
	 * @param ReaderThemes      $reader_themes Reader themes helper class instance.
	 * @param PluginSuppression $plugin_suppression An instance of the PluginSuppression class.
	 */
	public function __construct( ReaderThemes $reader_themes, PluginSuppression $plugin_suppression ) {
		$this->namespace          = 'amp/v1';
		$this->rest_base          = 'options';
		$this->reader_themes      = $reader_themes;
		$this->plugin_suppression = $plugin_suppression;
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
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'update_items' ],
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
					'permission_callback' => [ $this, 'get_items_permissions_check' ],
				],
				'schema' => [ $this, 'get_public_item_schema' ],
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

		// Add the preview permalink. The permalink can't be handled via AMP_Options_Manager::get_options because
		// amp_admin_get_preview_permalink calls AMP_Options_Manager::get_options, leading to infinite recursion.
		$options[ self::PREVIEW_PERMALINK ] = amp_admin_get_preview_permalink();

		$options[ self::SUPPRESSIBLE_PLUGINS ]   = $this->plugin_suppression->get_suppressible_plugins_with_details();
		$options[ self::SUPPORTABLE_POST_TYPES ] = array_map(
			static function( $slug ) {
				$post_type                 = (array) get_post_type_object( $slug );
				$post_type['supports_amp'] = post_type_supports( $post_type['name'], AMP_Post_Type_Support::SLUG );
				return $post_type;
			},
			AMP_Post_Type_Support::get_eligible_post_types()
		);

		$options[ self::SUPPORTABLE_TEMPLATES ] = $this->get_nested_supportable_templates( AMP_Theme_Support::get_supportable_templates() );

		$options[ Option::SUPPRESSED_PLUGINS ] = $this->plugin_suppression->prepare_suppressed_plugins_for_response( $options[ Option::SUPPRESSED_PLUGINS ] );

		$options[ self::ONBOARDING_WIZARD_LINK ] = amp_should_use_new_onboarding()
			? get_admin_url( null, add_query_arg( [ 'page' => OnboardingWizardSubmenu::SCREEN_ID ], 'admin.php' ) )
			: null;

		$options[ self::CUSTOMIZER_LINK ] = amp_get_customizer_url();

		return rest_ensure_response( $options );
	}

	/**
	 * Provides a hierarchical array of supportable templates.
	 *
	 * @param array[]     $supportable_templates Template options.
	 * @param string|null $parent_template_id    The parent to provide templates for.
	 * @return array[] Supportable templates with nesting.
	 */
	private function get_nested_supportable_templates( $supportable_templates, $parent_template_id = null ) {
		$nested_supportable_templates = [];

		foreach ( $supportable_templates as $id => $supportable_template ) {
			if (
				$parent_template_id ?
					empty( $supportable_template['parent'] ) || $parent_template_id !== $supportable_template['parent']
					:
					! empty( $supportable_template['parent'] )
			) {
				continue;
			}

			// Skip showing an option if it doesn't have a label.
			if ( empty( $supportable_template['label'] ) ) {
				continue;
			}

			$supportable_template['id']       = $id;
			$supportable_template['children'] = $this->get_nested_supportable_templates( $supportable_templates, $id );

			// Omit obsolete properties.
			unset(
				$supportable_template['supported'],
				$supportable_template['user_supported'],
				$supportable_template['immutable']
			);

			$nested_supportable_templates[] = $supportable_template;
		}

		return $nested_supportable_templates;
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
					Option::THEME_SUPPORT           => [
						'type' => 'string',
						'enum' => [
							AMP_Theme_Support::READER_MODE_SLUG,
							AMP_Theme_Support::STANDARD_MODE_SLUG,
							AMP_Theme_Support::TRANSITIONAL_MODE_SLUG,
						],
					],
					Option::READER_THEME            => [
						'type'        => 'string',
						'arg_options' => [
							'validate_callback' => function ( $value ) {
								// Note: The validate_callback is used instead of enum in order to prevent leaking the list of themes.
								return $this->reader_themes->theme_data_exists( $value );
							},
						],
					],
					Option::MOBILE_REDIRECT         => [
						'type'    => 'boolean',
						'default' => false,
					],
					self::PREVIEW_PERMALINK         => [
						'type'     => 'string',
						'readonly' => true,
						'format'   => 'url',
					],
					Option::PLUGIN_CONFIGURED       => [
						'type'    => 'boolean',
						'default' => false,
					],
					Option::ALL_TEMPLATES_SUPPORTED => [
						'type' => 'boolean',
					],
					Option::SUPPRESSED_PLUGINS      => [
						'type' => 'object',
					],
					self::SUPPRESSIBLE_PLUGINS      => [
						'type'     => 'object',
						'readonly' => true,
					],
					Option::SUPPORTED_TEMPLATES     => [
						'type'  => 'array',
						'items' => [
							'type' => 'string',
						],
					],
					Option::SUPPORTED_POST_TYPES    => [
						'type'  => 'array',
						'items' => [
							'type' => 'string',
						],
					],
					Option::ANALYTICS               => [
						'type' => 'object',
					],
					self::SUPPORTABLE_POST_TYPES    => [
						'type'     => 'array',
						'readonly' => true,
					],
					self::SUPPORTABLE_TEMPLATES     => [
						'type'     => 'array',
						'readonly' => true,
					],
					self::ONBOARDING_WIZARD_LINK    => [
						'type'     => 'url',
						'readonly' => true,
					],
					self::CUSTOMIZER_LINK           => [
						'type'     => 'url',
						'readonly' => true,
					],
				],
			];
		}

		return $this->schema;
	}
}
