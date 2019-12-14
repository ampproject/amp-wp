<?php
/**
 * Class AMP_Customizer_Design_Settings
 *
 * @package AMP
 */

/**
 * Class AMP_Customizer_Design_Settings
 */
class AMP_Customizer_Design_Settings {

	/**
	 * Default header color.
	 *
	 * @var string
	 */
	const DEFAULT_HEADER_COLOR = '#fff';

	/**
	 * Default header background color.
	 *
	 * @var string
	 */
	const DEFAULT_HEADER_BACKGROUND_COLOR = '#0a89c0';

	/**
	 * Default color scheme.
	 *
	 * @var string
	 */
	const DEFAULT_COLOR_SCHEME = 'light';

	/**
	 * Returns whether the AMP design settings are enabled.
	 *
	 * @since 1.1 This always return false when AMP theme support is present.
	 * @since 0.6
	 *
	 * @return bool AMP Customizer design settings enabled.
	 */
	public static function is_amp_customizer_enabled() {

		if ( AMP_Theme_Support::READER_MODE_SLUG !== AMP_Options_Manager::get_option( 'theme_support' ) ) {
			return false;
		}

		/**
		 * Filter whether to enable the AMP default template design settings.
		 *
		 * @since 0.4
		 * @since 0.6 This filter now controls whether or not the default settings, controls, and sections are registered for the Customizer. The AMP panel will be registered regardless.
		 * @param bool $enable Whether to enable the AMP default template design settings. Default true.
		 */
		return apply_filters( 'amp_customizer_is_enabled', true );
	}

	/**
	 * Init.
	 */
	public static function init() {
		add_action( 'amp_customizer_init', [ __CLASS__, 'init_customizer' ] );

		if ( self::is_amp_customizer_enabled() ) {
			add_filter( 'amp_customizer_get_settings', [ __CLASS__, 'append_settings' ] );
		}
	}

	/**
	 * Init customizer.
	 */
	public static function init_customizer() {
		if ( self::is_amp_customizer_enabled() ) {
			add_action( 'amp_customizer_register_settings', [ __CLASS__, 'register_customizer_settings' ] );
			add_action( 'amp_customizer_register_ui', [ __CLASS__, 'register_customizer_ui' ] );
			add_action( 'amp_customizer_enqueue_preview_scripts', [ __CLASS__, 'enqueue_customizer_preview_scripts' ] );
		}
	}

	/**
	 * Register default Customizer settings for AMP.
	 *
	 * @param WP_Customize_Manager $wp_customize Manager.
	 */
	public static function register_customizer_settings( $wp_customize ) {

		// Header text color setting.
		$wp_customize->add_setting(
			'amp_customizer[header_color]',
			[
				'type'              => 'option',
				'default'           => self::DEFAULT_HEADER_COLOR,
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			]
		);

		// Header background color.
		$wp_customize->add_setting(
			'amp_customizer[header_background_color]',
			[
				'type'              => 'option',
				'default'           => self::DEFAULT_HEADER_BACKGROUND_COLOR,
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'postMessage',
			]
		);

		// Background color scheme.
		$wp_customize->add_setting(
			'amp_customizer[color_scheme]',
			[
				'type'              => 'option',
				'default'           => self::DEFAULT_COLOR_SCHEME,
				'sanitize_callback' => [ __CLASS__, 'sanitize_color_scheme' ],
				'transport'         => 'postMessage',
			]
		);

		// Display exit link.
		$wp_customize->add_setting(
			'amp_customizer[display_exit_link]',
			[
				'type'              => 'option',
				'default'           => false,
				'sanitize_callback' => 'rest_sanitize_boolean',
				'transport'         => 'postMessage',
			]
		);
	}

	/**
	 * Register default Customizer sections and controls for AMP.
	 *
	 * @param WP_Customize_Manager $wp_customize Manager.
	 */
	public static function register_customizer_ui( $wp_customize ) {
		$wp_customize->add_section(
			'amp_design',
			[
				'title' => __( 'Design', 'amp' ),
				'panel' => AMP_Template_Customizer::PANEL_ID,
			]
		);

		// Header text color control.
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'amp_header_color',
				[
					'settings' => 'amp_customizer[header_color]',
					'label'    => __( 'Header Text Color', 'amp' ),
					'section'  => 'amp_design',
					'priority' => 10,
				]
			)
		);

		// Header background color control.
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'amp_header_background_color',
				[
					'settings' => 'amp_customizer[header_background_color]',
					'label'    => __( 'Header Background & Link Color', 'amp' ),
					'section'  => 'amp_design',
					'priority' => 20,
				]
			)
		);

		// Background color scheme.
		$wp_customize->add_control(
			'amp_color_scheme',
			[
				'settings' => 'amp_customizer[color_scheme]',
				'label'    => __( 'Color Scheme', 'amp' ),
				'section'  => 'amp_design',
				'type'     => 'radio',
				'priority' => 30,
				'choices'  => self::get_color_scheme_names(),
			]
		);

		// Display exit link.
		$wp_customize->add_control(
			'amp_display_exit_link',
			[
				'settings' => 'amp_customizer[display_exit_link]',
				'label'    => __( 'Display link to exit reader mode?', 'amp' ),
				'section'  => 'amp_design',
				'type'     => 'checkbox',
				'priority' => 40,
			]
		);

		// Header.
		$wp_customize->selective_refresh->add_partial(
			'amp-wp-header',
			[
				'selector'         => '.amp-wp-header',
				'settings'         => [ 'blogname', 'amp_customizer[display_exit_link]' ], // @todo Site Icon.
				'render_callback'  => [ __CLASS__, 'render_header_bar' ],
				'fallback_refresh' => false,
			]
		);

		// Header.
		$wp_customize->selective_refresh->add_partial(
			'amp-wp-footer',
			[
				'selector'            => '.amp-wp-footer',
				'settings'            => [ 'blogname' ],
				'render_callback'     => [ __CLASS__, 'render_footer' ],
				'fallback_refresh'    => false,
				'container_inclusive' => true,
			]
		);
	}

	/**
	 * Render header bar template.
	 */
	public static function render_header_bar() {
		if ( is_singular() ) {
			$post_template = new AMP_Post_Template( get_post() );
			$post_template->load_parts( [ 'header-bar' ] );
		}
	}

	/**
	 * Render footer template.
	 */
	public static function render_footer() {
		if ( is_singular() ) {
			$post_template = new AMP_Post_Template( get_post() );
			$post_template->load_parts( [ 'footer' ] );
		}
	}

	/**
	 * Enqueue scripts for default AMP Customizer preview.
	 *
	 * @global WP_Customize_Manager $wp_customize
	 */
	public static function enqueue_customizer_preview_scripts() {
		global $wp_customize;

		// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NoExplicitVersion
		wp_enqueue_script(
			'amp-customizer-design-preview',
			amp_get_asset_url( 'js/amp-customizer-design-preview.js' ),
			[ 'amp-customize-preview' ],
			false,
			true
		);
		wp_localize_script(
			'amp-customizer-design-preview',
			'amp_customizer_design',
			[
				'color_schemes' => self::get_color_schemes(),
			]
		);

		// Prevent a theme's registered blogname partial from causing full page refreshes.
		$blogname_partial = $wp_customize->selective_refresh->get_partial( 'blogname' );
		if ( $blogname_partial ) {
			$blogname_partial->fallback_refresh = false;
		}
	}

	/**
	 * Merge default Customizer settings on top of settings for merging into AMP post template.
	 *
	 * @see AMP_Post_Template::build_customizer_settings()
	 *
	 * @param array $settings Settings.
	 * @return array Merged settings.
	 */
	public static function append_settings( $settings ) {
		$settings = wp_parse_args(
			$settings,
			[
				'header_color'            => self::DEFAULT_HEADER_COLOR,
				'header_background_color' => self::DEFAULT_HEADER_BACKGROUND_COLOR,
				'color_scheme'            => self::DEFAULT_COLOR_SCHEME,
				'display_exit_link'       => false,
			]
		);

		$theme_colors = self::get_colors_for_color_scheme( $settings['color_scheme'] );

		return array_merge(
			$settings,
			$theme_colors,
			[
				'link_color' => $settings['header_background_color'],
			]
		);
	}

	/**
	 * Get color scheme names.
	 *
	 * @return array Color scheme names.
	 */
	protected static function get_color_scheme_names() {
		return [
			'light' => __( 'Light', 'amp' ),
			'dark'  => __( 'Dark', 'amp' ),
		];
	}

	/**
	 * Get color schemes.
	 *
	 * @return array Color schemes.
	 */
	protected static function get_color_schemes() {
		return [
			'light' => [
				// Convert colors to greyscale for light theme color; see <http://goo.gl/2gDLsp>.
				'theme_color'      => '#fff',
				'text_color'       => '#353535',
				'muted_text_color' => '#696969',
				'border_color'     => '#c2c2c2',
			],
			'dark'  => [
				// Convert and invert colors to greyscale for dark theme color; see <http://goo.gl/uVB2cO>.
				'theme_color'      => '#0a0a0a',
				'text_color'       => '#dedede',
				'muted_text_color' => '#b1b1b1',
				'border_color'     => '#707070',
			],
		];
	}

	/**
	 * Get colors for color scheme.
	 *
	 * @param string $scheme Color scheme.
	 * @return array Colors.
	 */
	protected static function get_colors_for_color_scheme( $scheme ) {
		$color_schemes = self::get_color_schemes();

		if ( isset( $color_schemes[ $scheme ] ) ) {
			return $color_schemes[ $scheme ];
		}

		return $color_schemes[ self::DEFAULT_COLOR_SCHEME ];
	}

	/**
	 * Sanitize color scheme.
	 *
	 * @param string $value Color scheme name.
	 * @return string Sanitized name.
	 */
	public static function sanitize_color_scheme( $value ) {
		$schemes      = self::get_color_scheme_names();
		$scheme_slugs = array_keys( $schemes );

		if ( ! in_array( $value, $scheme_slugs, true ) ) {
			$value = self::DEFAULT_COLOR_SCHEME;
		}

		return $value;
	}
}
