<?php
/**
 * Class AMP_Options_Manager.
 *
 * @package AMP
 */

use AmpProject\AmpWP\Admin\ReaderThemes;
use AmpProject\AmpWP\Option;

/**
 * Class AMP_Options_Manager
 *
 * @internal
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
	protected static $defaults = [
		Option::THEME_SUPPORT           => AMP_Theme_Support::READER_MODE_SLUG,
		Option::SUPPORTED_POST_TYPES    => [ 'post', 'page' ],
		Option::ANALYTICS               => [],
		Option::ALL_TEMPLATES_SUPPORTED => true,
		Option::SUPPORTED_TEMPLATES     => [ 'is_singular' ],
		Option::VERSION                 => AMP__VERSION,
		Option::READER_THEME            => ReaderThemes::DEFAULT_READER_THEME,
		Option::PAIRED_URL_STRUCTURE    => Option::PAIRED_URL_STRUCTURE_QUERY_VAR,
		Option::PLUGIN_CONFIGURED       => false,
	];

	/**
	 * Sets up hooks.
	 */
	public static function init() {
		add_action( 'admin_notices', [ __CLASS__, 'render_php_css_parser_conflict_notice' ] );
		add_action( 'admin_notices', [ __CLASS__, 'insecure_connection_notice' ] );
		add_action( 'admin_notices', [ __CLASS__, 'reader_theme_fallback_notice' ] );
	}

	/**
	 * Register settings.
	 */
	public static function register_settings() {
		register_setting(
			self::OPTION_NAME,
			self::OPTION_NAME,
			[
				'type'              => 'array',
				'sanitize_callback' => [ __CLASS__, 'validate_options' ],
			]
		);
	}

	/**
	 * Get plugin options.
	 *
	 * @return array Options.
	 */
	public static function get_options() {
		$options = get_option( self::OPTION_NAME, [] );
		if ( empty( $options ) ) {
			$options = []; // Ensure empty string becomes array.
		}

		$defaults      = self::$defaults;
		$theme_support = AMP_Theme_Support::get_theme_support_args();

		// Make sure the plugin is marked as being already configured if there saved options.
		if ( ! empty( $options ) ) {
			$defaults[ Option::PLUGIN_CONFIGURED ] = true;
		}

		// Migrate legacy method of specifying the mode.
		if ( ! isset( $options[ Option::THEME_SUPPORT ] ) && $theme_support ) {
			$template   = get_template();
			$stylesheet = get_stylesheet();
			if (
				// If theme support was probably explicitly added by the theme (since not core).
				! in_array( $template, AMP_Core_Theme_Sanitizer::get_supported_themes(), true )
				||
				// If it is a core theme no child theme is being used (which likely won't be AMP-compatible by default).
				$template === $stylesheet
			) {
				if ( empty( $theme_support[ AMP_Theme_Support::PAIRED_FLAG ] ) ) {
					$defaults[ Option::THEME_SUPPORT ] = AMP_Theme_Support::STANDARD_MODE_SLUG;
				} else {
					$defaults[ Option::THEME_SUPPORT ] = AMP_Theme_Support::TRANSITIONAL_MODE_SLUG;
				}
			}
		}

		// Migrate legacy amp post type support to be reflected in the default supported_post_types value.
		if ( ! isset( $options[ Option::SUPPORTED_POST_TYPES ] ) ) {
			$defaults[ Option::SUPPORTED_POST_TYPES ] = array_merge(
				$defaults[ Option::SUPPORTED_POST_TYPES ],
				(array) get_post_types_by_support( 'amp' )
			);
		}

		// Migrate legacy method of specifying all_templates_supported.
		if ( ! isset( $options[ Option::ALL_TEMPLATES_SUPPORTED ] ) && isset( $theme_support['templates_supported'] ) ) {
			$defaults[ Option::ALL_TEMPLATES_SUPPORTED ] = ( 'all' === $theme_support['templates_supported'] );
		}

		// Migrate legacy amp theme support to be reflected in the default supported_templates value.
		if ( ! isset( $options[ Option::SUPPORTED_TEMPLATES ] ) && isset( $theme_support['templates_supported'] ) && is_array( $theme_support['templates_supported'] ) ) {
			$defaults[ Option::SUPPORTED_TEMPLATES ] = array_merge(
				$defaults[ Option::SUPPORTED_TEMPLATES ],
				array_keys( array_filter( $theme_support['templates_supported'] ) )
			);
			$defaults[ Option::SUPPORTED_TEMPLATES ] = array_diff(
				$defaults[ Option::SUPPORTED_TEMPLATES ],
				array_keys(
					array_filter(
						$theme_support['templates_supported'],
						static function ( $supported ) {
							return ! $supported;
						}
					)
				)
			);
		}

		$options = array_merge(
			$defaults,
			/**
			 * Filters default options.
			 *
			 * @internal
			 * @param array $defaults        Default options.
			 * @param array $current_options Current options.
			 */
			(array) apply_filters( 'amp_default_options', $defaults, $options ),
			$options
		);

		// Ensure current template mode.
		if ( 'native' === $options[ Option::THEME_SUPPORT ] ) {
			// The slug 'native' is the old term for 'standard'.
			$options[ Option::THEME_SUPPORT ] = AMP_Theme_Support::STANDARD_MODE_SLUG;
		} elseif ( 'paired' === $options[ Option::THEME_SUPPORT ] ) {
			// The slug 'paired' is the old term for 'transitional.
			$options[ Option::THEME_SUPPORT ] = AMP_Theme_Support::TRANSITIONAL_MODE_SLUG;
		} elseif ( 'disabled' === $options[ Option::THEME_SUPPORT ] ) {
			/*
			 * Prior to 1.2, the theme support slug for Reader mode was 'disabled'. This would be saved in options for
			 * themes that had 'amp' theme support defined. Also prior to 1.2, the user could not switch between modes
			 * when the theme had 'amp' theme support. The result is that a site running 1.1 could be AMP-first and then
			 * upon upgrading to 1.2, be switched to Reader mode. So when migrating the old 'disabled' slug to the new
			 * value, we need to make sure we use the default theme support slug as it has been determined above. If the
			 * site has non-paired 'amp' theme support and the theme support slug is 'disabled' then it should here be
			 * set to 'standard' as opposed to 'reader', and the same goes for paired 'amp' theme support, as it should
			 * become 'transitional'. Otherwise, if the theme lacks 'amp' theme support, then this will become the
			 * default 'reader' mode.
			 */
			$options[ Option::THEME_SUPPORT ] = $defaults[ Option::THEME_SUPPORT ];
		}

		// Migrate options from 1.5 to 2.0.
		if ( isset( $options['version'] ) && version_compare( $options['version'], '2.0', '<' ) ) {

			// It used to be that the themes_supported flag overrode the options, so make sure the option gets updated to reflect the theme support.
			if ( isset( $theme_support['templates_supported'] ) ) {
				if ( 'all' === $theme_support['templates_supported'] ) {
					$options[ Option::ALL_TEMPLATES_SUPPORTED ] = true;
				} elseif ( is_array( $theme_support['templates_supported'] ) ) {
					$options[ Option::ALL_TEMPLATES_SUPPORTED ] = false;

					$options[ Option::SUPPORTED_TEMPLATES ] = array_merge(
						$options[ Option::SUPPORTED_TEMPLATES ],
						array_keys( array_filter( $theme_support['templates_supported'] ) )
					);

					$options[ Option::SUPPORTED_TEMPLATES ] = array_diff(
						$options[ Option::SUPPORTED_TEMPLATES ],
						array_keys(
							array_filter(
								$theme_support['templates_supported'],
								static function ( $supported ) {
									return ! $supported;
								}
							)
						)
					);
				}
			}

			// Make sure programmatic post type support is persisted in the DB, as from now on the DB option is the source of truth.
			$options[ Option::SUPPORTED_POST_TYPES ] = array_merge(
				$options[ Option::SUPPORTED_POST_TYPES ],
				(array) get_post_types_by_support( AMP_Post_Type_Support::SLUG )
			);

			// Make sure that all post types get enabled if all templates were supported since they are now independently controlled. This only applies to non-Reader mode.
			if ( ! empty( $options[ Option::ALL_TEMPLATES_SUPPORTED ] ) && AMP_Theme_Support::READER_MODE_SLUG !== $options[ Option::THEME_SUPPORT ] ) {
				$options[ Option::SUPPORTED_POST_TYPES ] = array_merge(
					$options[ Option::SUPPORTED_POST_TYPES ],
					AMP_Post_Type_Support::get_eligible_post_types()
				);
			}
		}

		unset(
			/**
			 * Remove 'auto_accept_sanitization' option.
			 *
			 * @since 1.4.0
			 */
			$options[ Option::AUTO_ACCEPT_SANITIZATION ],
			/**
			 * Remove Story related options.
			 *
			 * Option::ENABLE_AMP_STORIES was added in 1.2-beta and later migrated into the `experiences` option.
			 *
			 * @since 1.5.0
			 */
			$options[ Option::STORY_TEMPLATES_VERSION ],
			$options[ Option::STORY_EXPORT_BASE_URL ],
			$options[ Option::STORY_SETTINGS ],
			$options[ Option::ENABLE_AMP_STORIES ],
			/**
			 * Remove 'experiences' option.
			 *
			 * @since 1.5.0
			 */
			$options[ Option::EXPERIENCES ],
			/**
			 * Remove 'enable_response_caching' option.
			 *
			 * @since 1.5.0
			 */
			$options[ Option::ENABLE_RESPONSE_CACHING ]
		);

		return $options;
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

		if ( ! array_key_exists( $option, $amp_options ) ) {
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
		$recognized_theme_supports = [
			AMP_Theme_Support::READER_MODE_SLUG,
			AMP_Theme_Support::TRANSITIONAL_MODE_SLUG,
			AMP_Theme_Support::STANDARD_MODE_SLUG,
		];
		if ( isset( $new_options[ Option::THEME_SUPPORT ] ) && in_array( $new_options[ Option::THEME_SUPPORT ], $recognized_theme_supports, true ) ) {
			$options[ Option::THEME_SUPPORT ] = $new_options[ Option::THEME_SUPPORT ];
		}

		// Validate post type support.
		if ( isset( $new_options[ Option::SUPPORTED_POST_TYPES ] ) && is_array( $new_options[ Option::SUPPORTED_POST_TYPES ] ) ) {
			$options[ Option::SUPPORTED_POST_TYPES ] = [];
			foreach ( $new_options[ Option::SUPPORTED_POST_TYPES ] as $post_type ) {
				if ( post_type_exists( $post_type ) ) {
					$options[ Option::SUPPORTED_POST_TYPES ][] = $post_type;
				}
			}
			$options[ Option::SUPPORTED_POST_TYPES ] = array_values( array_unique( $options[ Option::SUPPORTED_POST_TYPES ] ) );
		}

		// Update all_templates_supported.
		if ( isset( $new_options[ Option::ALL_TEMPLATES_SUPPORTED ] ) ) {
			$options[ Option::ALL_TEMPLATES_SUPPORTED ] = rest_sanitize_boolean( $new_options[ Option::ALL_TEMPLATES_SUPPORTED ] );
		}

		// Validate supported templates.
		if ( isset( $new_options[ Option::SUPPORTED_TEMPLATES ] ) && is_array( $new_options[ Option::SUPPORTED_TEMPLATES ] ) ) {
			$supportable_templates                  = AMP_Theme_Support::get_supportable_templates();
			$options[ Option::SUPPORTED_TEMPLATES ] = [];
			foreach ( $new_options[ Option::SUPPORTED_TEMPLATES ] as $template_id ) {
				if ( array_key_exists( $template_id, $supportable_templates ) ) {
					$options[ Option::SUPPORTED_TEMPLATES ][] = $template_id;
				}
			}
			$options[ Option::SUPPORTED_TEMPLATES ] = array_values( array_unique( $options[ Option::SUPPORTED_TEMPLATES ] ) );
		}

		// Validate wizard completion.
		if ( isset( $new_options[ Option::PLUGIN_CONFIGURED ] ) ) {
			$options[ Option::PLUGIN_CONFIGURED ] = (bool) $new_options[ OPTION::PLUGIN_CONFIGURED ];
		}

		// Validate analytics.
		if ( isset( $new_options[ Option::ANALYTICS ] ) && $new_options[ Option::ANALYTICS ] !== $options[ Option::ANALYTICS ] ) {
			$new_analytics_option = [];

			foreach ( $new_options[ Option::ANALYTICS ] as $id => $data ) {
				if ( empty( $data['config'] ) || ! AMP_HTML_Utils::is_valid_json( $data['config'] ) ) {
					// Bad JSON or missing config.
					continue;
				}

				$new_analytics_option[ sanitize_key( $id ) ] = [
					'type'   => ! empty( $data['type'] ) ? preg_replace( '/[^a-zA-Z0-9_\-]/', '', $data['type'] ) : '',
					'config' => trim( $data['config'] ),
				];
			}

			$options[ OPTION::ANALYTICS ] = $new_analytics_option;
		}

		if ( isset( $new_options[ Option::READER_THEME ] ) ) {
			$reader_theme_slugs = wp_list_pluck( ( new ReaderThemes() )->get_themes(), 'slug' );
			if ( in_array( $new_options[ Option::READER_THEME ], $reader_theme_slugs, true ) ) {
				$options[ Option::READER_THEME ] = $new_options[ Option::READER_THEME ];
			}
		}

		if ( array_key_exists( Option::DISABLE_CSS_TRANSIENT_CACHING, $new_options ) && true === $new_options[ Option::DISABLE_CSS_TRANSIENT_CACHING ] ) {
			$options[ Option::DISABLE_CSS_TRANSIENT_CACHING ] = true;
		} else {
			unset( $options[ Option::DISABLE_CSS_TRANSIENT_CACHING ] );
		}

		/**
		 * Filter the options being updated, so services can handle the sanitization and validation of
		 * their respective options.
		 *
		 * @internal
		 *
		 * @param array $options     Existing options with already-sanitized values for updating.
		 * @param array $new_options Unsanitized options being submitted for updating.
		 */
		$options = apply_filters( 'amp_options_updating', $options, $new_options );

		// Store the current version with the options so we know the format.
		$options[ Option::VERSION ] = AMP__VERSION;

		return $options;
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
	 * Update plugin options.
	 *
	 * @param array $options Plugin option name.
	 * @return bool Whether update succeeded.
	 */
	public static function update_options( $options ) {
		$amp_options = array_merge(
			self::get_options(),
			$options
		);

		return update_option( self::OPTION_NAME, $amp_options, false );
	}

	/**
	 * Render PHP-CSS-Parser conflict notice.
	 *
	 * @return void
	 */
	public static function render_php_css_parser_conflict_notice() {
		$current_screen = get_current_screen();
		if ( ! ( $current_screen instanceof WP_Screen ) || 'toplevel_page_' . self::OPTION_NAME !== $current_screen->id ) {
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
				wp_kses(
					sprintf(
						/* translators: %s: path to the conflicting library */
						__( 'A conflicting version of PHP-CSS-Parser appears to be installed by another plugin or theme (located in %s). Because of this, CSS processing will be limited, and tree shaking will not be available.', 'amp' ),
						'<code>' . esc_html( $source_dir ) . '</code>'
					),
					[ 'code' => [] ]
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
	 * Outputs an admin notice if the site is not served over HTTPS.
	 *
	 * @since 1.3
	 *
	 * @return void
	 */
	public static function insecure_connection_notice() {
		$current_screen = get_current_screen();

		// is_ssl() only tells us whether the admin backend uses HTTPS here, so we add a few more sanity checks.
		$uses_ssl = (
			is_ssl()
			&&
			( strpos( get_bloginfo( 'wpurl' ), 'https' ) === 0 )
			&&
			( strpos( get_bloginfo( 'url' ), 'https' ) === 0 )
		);

		if ( ! $uses_ssl && $current_screen instanceof WP_Screen && 'toplevel_page_' . self::OPTION_NAME === $current_screen->id ) {
			printf(
				'<div class="notice notice-warning"><p>%s</p></div>',
				wp_kses(
					sprintf(
						/* translators: %s: "Why should I use HTTPS" support URL */
						__( 'Your site is not being fully served over a secure connection (using HTTPS).<br>As some AMP functionality requires a secure connection, you might experience degraded performance or broken components.<br><a href="%s">More details</a>', 'amp' ),
						esc_url( __( 'https://wordpress.org/support/article/why-should-i-use-https/', 'amp' ) )
					),
					[
						'br' => [],
						'a'  => [ 'href' => true ],
					]
				)
			);
		}
	}

	/**
	 * Outputs an admin notice if the AMP Legacy Reader theme is used as a fallback.
	 */
	public static function reader_theme_fallback_notice() {
		$current_screen = get_current_screen();

		if ( ! ( $current_screen instanceof WP_Screen ) || ! in_array( $current_screen->id, [ 'themes', 'toplevel_page_' . self::OPTION_NAME ], true ) ) {
			return;
		}

		$reader_themes = new ReaderThemes();

		if ( $reader_themes->using_fallback_theme() && current_user_can( 'manage_options' ) ) {
			$selected_theme = self::get_option( Option::READER_THEME );
			$error_message  = sprintf(
				/* translators: 1: slug of the Reader theme, 2: the URL for the reader theme selection UI */
				__( 'The AMP Reader theme %1$s cannot be found. Your site is currently falling back to using the Legacy templates for AMP pages. Please <a href="%2$s">re-select</a> the desired Reader theme.', 'amp' ),
				"<code>{$selected_theme}</code>",
				esc_url( add_query_arg( 'page', self::OPTION_NAME, admin_url( 'admin.php' ) ) . '#reader-themes' )
			);
			?>
			<div class="notice notice-warning">
				<p>
					<?php
					echo wp_kses(
						$error_message,
						[
							'code' => [],
							'a'    => [
								'href' => true,
							],
						]
					);
					?>
				</p>
			</div>
			<?php
		}
	}
}
