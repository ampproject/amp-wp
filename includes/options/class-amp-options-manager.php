<?php
/**
 * Class AMP_Options_Manager.
 *
 * @package AMP
 */

/**
 * Class AMP_Options_Manager
 */
class AMP_Options_Manager {

	/**
	 * Option name.
	 *
	 * @var string
	 */
	const OPTION_NAME = 'amp-options';

	/**
	 * Default option values.
	 *
	 * @var array
	 */
	protected static $defaults = array(
		'theme_support'           => 'disabled',
		'supported_post_types'    => array( 'post' ),
		'analytics'               => array(),
		'force_sanitization'      => true,
		'accept_tree_shaking'     => true,
		'disable_admin_bar'       => false,
		'all_templates_supported' => true,
		'supported_templates'     => array( 'is_singular' ),
		'enable_response_caching' => true,
	);

	/**
	 * Register settings.
	 */
	public static function register_settings() {
		register_setting(
			self::OPTION_NAME,
			self::OPTION_NAME,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'validate_options' ),
			)
		);

		add_action( 'update_option_' . self::OPTION_NAME, array( __CLASS__, 'maybe_flush_rewrite_rules' ), 10, 2 );
		add_action( 'admin_notices', array( __CLASS__, 'persistent_object_caching_notice' ) );
		add_action( 'admin_notices', array( __CLASS__, 'render_cache_miss_notice' ) );
	}

	/**
	 * Flush rewrite rules if the supported_post_types have changed.
	 *
	 * @since 0.6.2
	 *
	 * @param array $old_options Old options.
	 * @param array $new_options New options.
	 */
	public static function maybe_flush_rewrite_rules( $old_options, $new_options ) {
		$old_post_types = isset( $old_options['supported_post_types'] ) ? $old_options['supported_post_types'] : array();
		$new_post_types = isset( $new_options['supported_post_types'] ) ? $new_options['supported_post_types'] : array();
		sort( $old_post_types );
		sort( $new_post_types );
		if ( $old_post_types !== $new_post_types ) {
			flush_rewrite_rules( false );
		}
	}

	/**
	 * Get plugin options.
	 *
	 * @return array Options.
	 */
	public static function get_options() {
		$options = get_option( self::OPTION_NAME, array() );
		if ( empty( $options ) ) {
			$options = array();
		}
		self::$defaults['enable_response_caching'] = wp_using_ext_object_cache();
		return array_merge( self::$defaults, $options );
	}

	/**
	 * Get plugin option.
	 *
	 * @param string $option  Plugin option name.
	 * @param bool   $default Default value.
	 *
	 * @return mixed Option value.
	 */
	public static function get_option( $option, $default = false ) {
		$amp_options = self::get_options();

		if ( ! isset( $amp_options[ $option ] ) ) {
			return $default;
		}

		return $amp_options[ $option ];
	}

	/**
	 * Validate options.
	 *
	 * @param array $new_options Plugin options.
	 * @return array Options.
	 */
	public static function validate_options( $new_options ) {
		$options = self::get_options();

		// Theme support.
		$recognized_theme_supports = array(
			'disabled',
			'paired',
			'native',
		);
		if ( isset( $new_options['theme_support'] ) && in_array( $new_options['theme_support'], $recognized_theme_supports, true ) ) {
			$options['theme_support'] = $new_options['theme_support'];
		}

		$options['force_sanitization']  = ! empty( $new_options['force_sanitization'] );
		$options['accept_tree_shaking'] = ! empty( $new_options['accept_tree_shaking'] );
		$options['disable_admin_bar']   = ! empty( $new_options['disable_admin_bar'] );

		// Validate post type support.
		$options['supported_post_types'] = array();
		if ( isset( $new_options['supported_post_types'] ) ) {
			foreach ( $new_options['supported_post_types'] as $post_type ) {
				if ( ! post_type_exists( $post_type ) ) {
					add_settings_error( self::OPTION_NAME, 'unknown_post_type', __( 'Unrecognized post type.', 'amp' ) );
				} else {
					$options['supported_post_types'][] = $post_type;
				}
			}
		}

		$theme_support_args = AMP_Theme_Support::get_theme_support_args();

		$is_template_support_required = ( isset( $theme_support_args['templates_supported'] ) && 'all' === $theme_support_args['templates_supported'] );
		if ( ! $is_template_support_required && ! isset( $theme_support_args['available_callback'] ) ) {
			$options['all_templates_supported'] = ! empty( $new_options['all_templates_supported'] );

			// Validate supported templates.
			$options['supported_templates'] = array();
			if ( isset( $new_options['supported_templates'] ) ) {
				$options['supported_templates'] = array_intersect(
					$new_options['supported_templates'],
					array_keys( AMP_Theme_Support::get_supportable_templates() )
				);
			}
		}

		// Validate analytics.
		if ( isset( $new_options['analytics'] ) ) {
			foreach ( $new_options['analytics'] as $id => $data ) {

				// Check save/delete pre-conditions and proceed if correct.
				if ( empty( $data['type'] ) || empty( $data['config'] ) ) {
					add_settings_error( self::OPTION_NAME, 'missing_analytics_vendor_or_config', __( 'Missing vendor type or config.', 'amp' ) );
					continue;
				}

				// Validate JSON configuration.
				$is_valid_json = AMP_HTML_Utils::is_valid_json( $data['config'] );
				if ( ! $is_valid_json ) {
					add_settings_error( self::OPTION_NAME, 'invalid_analytics_config_json', __( 'Invalid analytics config JSON.', 'amp' ) );
					continue;
				}

				$entry_vendor_type = preg_replace( '/[^a-zA-Z0-9_\-]/', '', $data['type'] );
				$entry_config      = trim( $data['config'] );

				if ( ! empty( $data['id'] ) && '__new__' !== $data['id'] ) {
					$entry_id = sanitize_key( $data['id'] );
				} else {

					// Generate a hash string to uniquely identify this entry.
					$entry_id = substr( md5( $entry_vendor_type . $entry_config ), 0, 12 );

					// Avoid duplicates.
					if ( isset( $options['analytics'][ $entry_id ] ) ) {
						add_settings_error( self::OPTION_NAME, 'duplicate_analytics_entry', __( 'Duplicate analytics entry found.', 'amp' ) );
						continue;
					}
				}

				if ( isset( $data['delete'] ) ) {
					unset( $options['analytics'][ $entry_id ] );
				} else {
					$options['analytics'][ $entry_id ] = array(
						'type'   => $entry_vendor_type,
						'config' => $entry_config,
					);
				}
			}
		}

		// Store the current version with the options so we know the format.
		$options['version'] = AMP__VERSION;

		// Handle the caching option.
		$options['enable_response_caching'] = (
			wp_using_ext_object_cache()
			&&
			! empty( $new_options['enable_response_caching'] )
		);
		if ( $options['enable_response_caching'] ) {
			AMP_Theme_Support::reset_cache_miss_url_option();
		}

		return $options;
	}

	/**
	 * Check for errors with updating the supported post types.
	 *
	 * @since 0.6
	 * @see add_settings_error()
	 */
	public static function check_supported_post_type_update_errors() {

		// If all templates are supported then skip check since all post types are also supported. This option only applies with native/paired theme support.
		if ( self::get_option( 'all_templates_supported', false ) && 'disabled' !== self::get_option( 'theme_support' ) ) {
			return;
		}

		$supported_types = self::get_option( 'supported_post_types', array() );
		foreach ( AMP_Post_Type_Support::get_eligible_post_types() as $name ) {
			$post_type = get_post_type_object( $name );
			if ( empty( $post_type ) ) {
				continue;
			}

			$post_type_supported = post_type_supports( $post_type->name, amp_get_slug() );
			$is_support_elected  = in_array( $post_type->name, $supported_types, true );

			$error = null;
			$code  = null;
			if ( $is_support_elected && ! $post_type_supported ) {
				/* translators: %s: Post type name. */
				$error = __( '"%s" could not be activated because support is removed by a plugin or theme', 'amp' );
				$code  = sprintf( '%s_activation_error', $post_type->name );
			} elseif ( ! $is_support_elected && $post_type_supported ) {
				/* translators: %s: Post type name. */
				$error = __( '"%s" could not be deactivated because support is added by a plugin or theme', 'amp' );
				$code  = sprintf( '%s_deactivation_error', $post_type->name );
			}

			if ( isset( $error, $code ) ) {
				add_settings_error(
					self::OPTION_NAME,
					$code,
					sprintf(
						$error,
						isset( $post_type->label ) ? $post_type->label : $post_type->name
					)
				);
			}
		}
	}

	/**
	 * Update plugin option.
	 *
	 * @param string $option Plugin option name.
	 * @param mixed  $value  Plugin option value.
	 *
	 * @return bool Whether update succeeded.
	 */
	public static function update_option( $option, $value ) {
		$amp_options = self::get_options();

		$amp_options[ $option ] = $value;

		return update_option( self::OPTION_NAME, $amp_options, false );
	}

	/**
	 * Handle analytics submission.
	 */
	public static function handle_analytics_submit() {

		// Request must come from user with right capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Sorry, you do not have the necessary permissions to perform this action', 'amp' ) );
		}

		// Ensure request is coming from analytics option form.
		check_admin_referer( 'analytics-options', 'analytics-options' );

		if ( isset( $_POST['amp-options']['analytics'] ) ) {
			self::update_option( 'analytics', wp_unslash( $_POST['amp-options']['analytics'] ) );

			$errors = get_settings_errors( self::OPTION_NAME );
			if ( empty( $errors ) ) {
				add_settings_error( self::OPTION_NAME, 'settings_updated', __( 'The analytics entry was successfully saved!', 'amp' ), 'updated' );
				$errors = get_settings_errors( self::OPTION_NAME );
			}
			set_transient( 'settings_errors', $errors );
		}

		/*
		 * Redirect to keep the user in the analytics options page.
		 * Wrap in is_admin() to enable phpunit tests to exercise this code.
		 */
		wp_safe_redirect( admin_url( 'admin.php?page=amp-analytics-options&settings-updated=1' ) );
		exit;
	}

	/**
	 * Update analytics options.
	 *
	 * @codeCoverageIgnore
	 * @deprecated
	 * @param array $data Unsanitized unslashed data.
	 * @return bool Whether options were updated.
	 */
	public static function update_analytics_options( $data ) {
		_deprecated_function( __METHOD__, '0.6', __CLASS__ . '::update_option' );
		return self::update_option( 'analytics', wp_unslash( $data ) );
	}

	/**
	 * Outputs an admin notice if persistent object cache is not present.
	 *
	 * @return void
	 */
	public static function persistent_object_caching_notice() {
		if ( ! wp_using_ext_object_cache() && 'toplevel_page_' . self::OPTION_NAME === get_current_screen()->id ) {
			printf(
				'<div class="notice notice-warning"><p>%s <a href="%s">%s</a></p></div>',
				esc_html__( 'The AMP plugin performs at its best when persistent object cache is enabled.', 'amp' ),
				esc_url( 'https://codex.wordpress.org/Class_Reference/WP_Object_Cache#Persistent_Caching' ),
				esc_html__( 'More details', 'amp' )
			);
		}
	}

	/**
	 * Render the cache miss admin notice.
	 *
	 * @return void
	 */
	public static function render_cache_miss_notice() {
		if ( 'toplevel_page_' . self::OPTION_NAME !== get_current_screen()->id ) {
			return;
		}

		if ( ! self::show_response_cache_disabled_notice() ) {
			return;
		}

		printf(
			'<div class="notice notice-warning is-dismissible"><p>%s <a href="%s">%s</a></p></div>',
			esc_html__( "The AMP plugin's post-processor cache disabled due to the detection of highly-variable content.", 'amp' ),
			esc_url( 'https://github.com/Automattic/amp-wp/wiki/Post-Processor-Cache' ),
			esc_html__( 'More details', 'amp' )
		);
	}

	/**
	 * Show the response cache disabled notice.
	 *
	 * @since 1.0
	 *
	 * @return bool
	 */
	public static function show_response_cache_disabled_notice() {
		return (
			wp_using_ext_object_cache()
			&&
			! self::get_option( 'enable_response_caching' )
			&&
			AMP_Theme_Support::exceeded_cache_miss_threshold()
		);
	}
}
