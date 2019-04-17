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
		'theme_support'            => 'disabled',
		'supported_post_types'     => array( 'post' ),
		'analytics'                => array(),
		'auto_accept_sanitization' => true,
		'accept_tree_shaking'      => true,
		'disable_admin_bar'        => false,
		'all_templates_supported'  => true,
		'supported_templates'      => array( 'is_singular' ),
		'enable_response_caching'  => true,
		'version'                  => AMP__VERSION,
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
		add_action( 'admin_notices', array( __CLASS__, 'render_welcome_notice' ) );
		add_action( 'admin_notices', array( __CLASS__, 'persistent_object_caching_notice' ) );
		add_action( 'admin_notices', array( __CLASS__, 'render_cache_miss_notice' ) );
		add_action( 'admin_notices', array( __CLASS__, 'render_php_css_parser_conflict_notice' ) );
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

		if ( ! current_user_can( 'manage_options' ) ) {
			return $options;
		}

		// Theme support.
		$recognized_theme_supports = array(
			'disabled',
			'paired',
			'native',
		);
		if ( isset( $new_options['theme_support'] ) && in_array( $new_options['theme_support'], $recognized_theme_supports, true ) ) {
			$options['theme_support'] = $new_options['theme_support'];

			// If this option was changed, display a notice with the new template mode.
			if ( self::get_option( 'theme_support' ) !== $new_options['theme_support'] ) {
				add_action( 'update_option_' . self::OPTION_NAME, array( __CLASS__, 'handle_updated_theme_support_option' ) );
			}
		}

		$options['auto_accept_sanitization'] = ! empty( $new_options['auto_accept_sanitization'] );
		$options['accept_tree_shaking']      = ! empty( $new_options['accept_tree_shaking'] );
		$options['disable_admin_bar']        = ! empty( $new_options['disable_admin_bar'] );

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

		// If all templates are supported then skip check since all post types are also supported. This option only applies with native/transitional theme support.
		if ( self::get_option( 'all_templates_supported', false ) && 'disabled' !== self::get_option( 'theme_support' ) ) {
			return;
		}

		$supported_types = self::get_option( 'supported_post_types', array() );
		foreach ( AMP_Post_Type_Support::get_eligible_post_types() as $name ) {
			$post_type = get_post_type_object( $name );
			if ( empty( $post_type ) ) {
				continue;
			}

			$post_type_supported = post_type_supports( $post_type->name, AMP_Post_Type_Support::SLUG );
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
	 * Renders the welcome notice on the 'AMP Settings' page.
	 *
	 * Uses the user meta values for the dismissed WP pointers.
	 * So once the user dismisses this notice, it will never appear again.
	 */
	public static function render_welcome_notice() {
		if ( 'toplevel_page_' . self::OPTION_NAME !== get_current_screen()->id ) {
			return;
		}

		$notice_id = 'amp-welcome-notice-1';
		$dismissed = get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true );
		if ( in_array( $notice_id, explode( ',', strval( $dismissed ) ), true ) ) {
			return;
		}

		?>
		<div class="amp-welcome-notice notice notice-info is-dismissible" id="<?php echo esc_attr( $notice_id ); ?>">
			<div class="notice-dismiss"></div>
			<div class="amp-welcome-icon-holder">
				<img class="amp-welcome-icon" src="<?php echo esc_url( amp_get_asset_url( 'images/amp-welcome-icon.svg' ) ); ?>" alt="<?php esc_html_e( 'Illustration of WordPress running AMP plugin.', 'amp' ); ?>" />
			</div>
			<h1><?php esc_html_e( 'Welcome to AMP for WordPress', 'amp' ); ?></h1>
			<h3><?php esc_html_e( 'Bring the speed and features of the open source AMP project to your site, complete with the tools to support content authoring and website development.', 'amp' ); ?></h3>
			<h3><?php esc_html_e( 'From granular controls that help you create AMP content, to Core Gutenberg support, to a sanitizer that only shows visitors error-free pages, to a full error workflow for developers, this release enables rich, performant experiences for your WordPress site.', 'amp' ); ?></h3>
			<a href="https://amp-wp.org/getting-started/" target="_blank" class="button button-primary"><?php esc_html_e( 'Learn More', 'amp' ); ?></a>
		</div>

		<script>
		jQuery( function( $ ) {
			// On dismissing the notice, make a POST request to store this notice with the dismissed WP pointers so it doesn't display again.
			$( <?php echo wp_json_encode( "#$notice_id" ); ?> ).on( 'click', '.notice-dismiss', function() {
				$.post( ajaxurl, {
					pointer: <?php echo wp_json_encode( $notice_id ); ?>,
					action: 'dismiss-wp-pointer'
				} );
			} );
		} );
		</script>
		<style type="text/css">
			.amp-welcome-notice {
				padding: 38px;
			}
			.amp-welcome-notice + .notice {
				clear: both;
			}
			.amp-welcome-icon-holder {
				width: 200px;
				height: 200px;
				float: left;
				margin: 0 38px 38px 0;
			}
			.amp-welcome-icon {
				width: 100%;
				height: 100%;
				display: block;
			}
			.amp-welcome-notice h1 {
				font-weight: bold;
			}
			.amp-welcome-notice h3 {
				font-size: 16px;
				font-weight: 500;
			}

		</style>
		<?php
	}

	/**
	 * Outputs an admin notice if persistent object cache is not present.
	 *
	 * @return void
	 */
	public static function persistent_object_caching_notice() {
		if ( ! wp_using_ext_object_cache() && 'toplevel_page_' . self::OPTION_NAME === get_current_screen()->id ) {
			printf(
				'<div class="notice notice-warning"><p>%s</p></div>',
				sprintf(
					/* translators: %s: Persistent object cache support URL */
					__( 'The AMP plugin performs at its best when persistent object cache is enabled. <a href="%s">More details</a>', 'amp' ), // phpcs:ignore WordPress.Security.EscapeOutput
					esc_url( __( 'https://codex.wordpress.org/Class_Reference/WP_Object_Cache#Persistent_Caching', 'amp' ) )
				)
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
			'<div class="notice notice-warning is-dismissible"><p>%s</p></div>',
			sprintf(
				/* translators: %s: post-processor cache support URL */
				__( 'The AMP plugin&lsquo;s post-processor cache was disabled due to the detection of highly-variable content. <a href="%s">More details</a>', 'amp' ), // phpcs:ignore WordPress.Security.EscapeOutput
				esc_url( __( 'https://github.com/ampproject/amp-wp/wiki/Post-Processor-Cache', 'amp' ) )
			)
		);
	}

	/**
	 * Render PHP-CSS-Parser conflict notice.
	 *
	 * @return void
	 */
	public static function render_php_css_parser_conflict_notice() {
		if ( 'toplevel_page_' . self::OPTION_NAME !== get_current_screen()->id ) {
			return;
		}

		if ( AMP_Style_Sanitizer::has_required_php_css_parser() ) {
			return;
		}

		try {
			$reflection = new ReflectionClass( 'Sabberworm\CSS\CSSList\CSSList' );
			$source_dir = str_replace(
				trailingslashit( WP_CONTENT_DIR ),
				'',
				preg_replace( '#/vendor/sabberworm/.+#', '', $reflection->getFileName() )
			);

			printf(
				'<div class="notice notice-warning"><p>%s</p></div>',
				sprintf(
					/* translators: %s: path to the conflicting library */
					__( 'A conflicting version of PHP-CSS-Parser appears to be installed by another plugin or theme (located in %s). Because of this, CSS processing will be limited, and tree shaking will not be available.', 'amp' ), // phpcs:ignore WordPress.Security.EscapeOutput
					'<code>' . esc_html( $source_dir ) . '</code>'
				)
			);
		} catch ( ReflectionException $e ) {
			printf(
				'<div class="notice notice-warning"><p>%s</p></div>',
				esc_html__( 'PHP-CSS-Parser is not available so CSS processing will not be available.', 'amp' )
			);
		}
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

	/**
	 * Adds a message for an update of the theme support setting.
	 */
	public static function handle_updated_theme_support_option() {
		$template_mode = self::get_option( 'theme_support' );

		// Make sure post type support has been added for sake of amp_admin_get_preview_permalink().
		foreach ( AMP_Post_Type_Support::get_eligible_post_types() as $post_type ) {
			remove_post_type_support( $post_type, AMP_Post_Type_Support::SLUG );
		}
		AMP_Post_Type_Support::add_post_type_support();

		// Ensure theme support flags are set properly according to the new mode so that proper AMP URL can be generated.
		$has_theme_support = ( 'native' === $template_mode || 'paired' === $template_mode );
		if ( $has_theme_support ) {
			$theme_support = current_theme_supports( AMP_Theme_Support::SLUG );
			if ( ! is_array( $theme_support ) ) {
				$theme_support = array();
			}
			$theme_support['paired'] = 'paired' === $template_mode;
			add_theme_support( AMP_Theme_Support::SLUG, $theme_support );
		} else {
			remove_theme_support( AMP_Theme_Support::SLUG ); // So that the amp_get_permalink() will work for reader mode URL.
		}

		$url = amp_admin_get_preview_permalink();

		$notice_type     = 'updated';
		$review_messages = array();
		if ( $url && $has_theme_support ) {
			$validation = AMP_Validation_Manager::validate_url( $url );

			if ( is_wp_error( $validation ) ) {
				$review_messages[] = esc_html(
					sprintf(
						/* translators: 1: error message. 2: error code. */
						__( 'However, there was an error when checking the AMP validity for your site.', 'amp' ),
						$validation->get_error_message(),
						$validation->get_error_code()
					)
				);

				$error_message = $validation->get_error_message();
				if ( $error_message ) {
					$review_messages[] = $error_message;
				} else {
					/* translators: %s is the error code */
					$review_messages[] = esc_html( sprintf( __( 'Error code: %s.', 'amp' ), $validation->get_error_code() ) );
				}
				$notice_type = 'error';
			} elseif ( is_array( $validation ) ) {
				$new_errors      = 0;
				$rejected_errors = 0;

				$errors = wp_list_pluck( $validation['results'], 'error' );
				foreach ( $errors as $error ) {
					$sanitization    = AMP_Validation_Error_Taxonomy::get_validation_error_sanitization( $error );
					$is_new_rejected = AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_REJECTED_STATUS === $sanitization['status'];
					if ( $is_new_rejected || AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_NEW_ACCEPTED_STATUS === $sanitization['status'] ) {
						$new_errors++;
					}
					if ( $is_new_rejected || AMP_Validation_Error_Taxonomy::VALIDATION_ERROR_ACK_REJECTED_STATUS === $sanitization['status'] ) {
						$rejected_errors++;
					}
				}

				$invalid_url_post_id    = AMP_Validated_URL_Post_Type::store_validation_errors( $errors, $url );
				$invalid_url_screen_url = ! is_wp_error( $invalid_url_post_id ) ? get_edit_post_link( $invalid_url_post_id, 'raw' ) : null;

				if ( $rejected_errors > 0 ) {
					$notice_type = 'error';

					$message = wp_kses_post(
						sprintf(
							/* translators: %s is count of rejected errors */
							_n(
								'However, AMP is not yet available due to %s validation error (for one URL at least).',
								'However, AMP is not yet available due to %s validation errors (for one URL at least).',
								number_format_i18n( $rejected_errors ),
								'amp'
							),
							$rejected_errors,
							esc_url( $invalid_url_screen_url )
						)
					);

					if ( $invalid_url_screen_url ) {
						$message .= ' ' . wp_kses_post(
							sprintf(
								/* translators: %s is URL to review issues */
								_n(
									'<a href="%s">Review Issue</a>.',
									'<a href="%s">Review Issues</a>.',
									$rejected_errors,
									'amp'
								),
								esc_url( $invalid_url_screen_url )
							)
						);
					}

					$review_messages[] = $message;
				} else {
					$message = wp_kses_post(
						sprintf(
							/* translators: %s is an AMP URL */
							__( 'View an <a href="%s">AMP version of your site</a>.', 'amp' ),
							esc_url( $url )
						)
					);

					if ( $new_errors > 0 && $invalid_url_screen_url ) {
						$message .= ' ' . wp_kses_post(
							sprintf(
								/* translators: 1: URL to review issues. 2: count of new errors. */
								_n(
									'Please also <a href="%1$s">review %2$s issue</a> which may need to be fixed (for one URL at least).',
									'Please also <a href="%1$s">review %2$s issues</a> which may need to be fixed (for one URL at least).',
									$new_errors,
									'amp'
								),
								esc_url( $invalid_url_screen_url ),
								number_format_i18n( $new_errors )
							)
						);
					}

					$review_messages[] = $message;
				}
			}
		}

		switch ( $template_mode ) {
			case 'native':
				$message = esc_html__( 'Native mode activated!', 'amp' );
				if ( $review_messages ) {
					$message .= ' ' . join( ' ', $review_messages );
				}
				break;
			case 'paired':
				$message = esc_html__( 'Transitional mode activated!', 'amp' );
				if ( $review_messages ) {
					$message .= ' ' . join( ' ', $review_messages );
				}
				break;
			case 'disabled':
				$message = wp_kses_post(
					sprintf(
						/* translators: %s is an AMP URL */
						__( 'Reader mode activated! View the <a href="%s">AMP version of a recent post</a>. It is recommended that you upgrade to Native or Transitional mode.', 'amp' ),
						esc_url( $url )
					)
				);
				break;
		}

		if ( isset( $message ) ) {
			add_settings_error( self::OPTION_NAME, 'template_mode_updated', $message, $notice_type );
		}
	}
}
