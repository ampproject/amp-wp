<?php
/**
 * Class AMP_Template_Customizer
 *
 * @package AMP
 */

use AmpProject\AmpWP\Admin\ReaderThemes;
use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\ReaderThemeLoader;
use AmpProject\AmpWP\Services;

/**
 * AMP class that implements a template style editor in the Customizer.
 *
 * A direct, formed link to the AMP editor in the Customizer is added via
 * {@see amp_customizer_editor_link()} as a submenu to the Appearance menu.
 *
 * @since 0.4
 * @internal
 */
class AMP_Template_Customizer {

	/**
	 * AMP template editor panel ID.
	 *
	 * @since 0.4
	 * @var string
	 */
	const PANEL_ID = 'amp_panel';

	/**
	 * Theme mod name to contain timestamps for when theme mods were last modified.
	 *
	 * @since 2.0
	 * @var string
	 */
	const THEME_MOD_TIMESTAMPS_KEY = 'amp_customize_setting_modified_timestamps';

	/**
	 * Customizer instance.
	 *
	 * @since 0.4
	 * @var WP_Customize_Manager $wp_customize
	 */
	protected $wp_customize;

	/**
	 * Reader theme loader.
	 *
	 * @since 2.0
	 * @var ReaderThemeLoader
	 */
	protected $reader_theme_loader;

	/**
	 * AMP_Template_Customizer constructor.
	 *
	 * @param WP_Customize_Manager $wp_customize        Customize manager.
	 * @param ReaderThemeLoader    $reader_theme_loader Reader theme loader instance.
	 */
	protected function __construct( WP_Customize_Manager $wp_customize, ReaderThemeLoader $reader_theme_loader ) {
		$this->wp_customize        = $wp_customize;
		$this->reader_theme_loader = $reader_theme_loader;
	}

	/**
	 * Initialize the template Customizer feature class.
	 *
	 * @static
	 * @since 0.4
	 * @access public
	 *
	 * @param WP_Customize_Manager $wp_customize        Customizer instance.
	 * @param ReaderThemeLoader    $reader_theme_loader Reader theme loader.
	 * @return AMP_Template_Customizer Instance.
	 */
	public static function init( WP_Customize_Manager $wp_customize, ReaderThemeLoader $reader_theme_loader = null ) {
		if ( null === $reader_theme_loader ) {
			$reader_theme_loader = Services::get( 'reader_theme_loader' );
		}
		$self = new self( $wp_customize, $reader_theme_loader );

		$is_reader_mode   = ( AMP_Theme_Support::READER_MODE_SLUG === AMP_Options_Manager::get_option( Option::THEME_SUPPORT ) );
		$has_reader_theme = ( ReaderThemes::DEFAULT_READER_THEME !== AMP_Options_Manager::get_option( Option::READER_THEME ) ); // @todo Verify that the theme actually exists.

		if ( $is_reader_mode ) {
			if ( $has_reader_theme ) {
				add_action( 'customize_save_after', [ $self, 'store_modified_theme_mod_setting_timestamps' ] );
			}

			if ( $reader_theme_loader->is_theme_overridden() ) {
				add_action( 'customize_controls_enqueue_scripts', [ $self, 'add_customizer_scripts' ] );
				add_action( 'customize_controls_print_footer_scripts', [ $self, 'render_setting_import_section_template' ] );
			} elseif ( ! $has_reader_theme ) {
				/**
				 * Fires when the AMP Template Customizer initializes.
				 *
				 * In practice the `customize_register` hook should be used instead.
				 *
				 * @param AMP_Template_Customizer $self Instance.
				 *
				 * @since 0.4
				 */
				do_action( 'amp_customizer_init', $self );

				$self->register_legacy_settings();
				$self->register_legacy_ui();

				add_action( 'customize_controls_print_footer_scripts', [ $self, 'print_legacy_controls_templates' ] );
				add_action( 'customize_preview_init', [ $self, 'init_legacy_preview' ] );

				add_action( 'customize_controls_enqueue_scripts', [ $self, 'add_legacy_customizer_scripts' ] );
			}
		}

		$self->set_refresh_setting_transport();
		$self->remove_cover_template_section();
		$self->remove_homepage_settings_section();
		return $self;
	}

	/**
	 * Force changes to header video to cause refresh since there are various JS dependencies that prevent selective refresh from working properly.
	 *
	 * In the AMP Customizer preview, selective refresh partial for `custom_header` will render <amp-video> or <amp-youtube> elements.
	 * Nevertheless, custom-header.js in core is not expecting AMP components. Therefore the `wp-custom-header-video-loaded` event never
	 * fires. This prevents themes from toggling the `has-header-video` class on the body.
	 *
	 * Additionally, the Twenty Seventeen core theme (the only which supports header videos) has two separate scripts
	 * `twentyseventeen-global` and `twentyseventeen-skip-link-focus-fix` which are depended on for displaying the
	 * video, for example toggling the 'has-header-video' class when the video is added or removed.
	 *
	 * This applies whenever AMP is being served in the Customizer preview, that is, in Standard mode or Reader mode with a Reader theme.
	 */
	protected function set_refresh_setting_transport() {
		if ( ! amp_is_canonical() && ! $this->reader_theme_loader->is_theme_overridden() ) {
			return;
		}

		$setting_ids = [
			'header_video',
			'external_header_video',
		];
		foreach ( $setting_ids as $setting_id ) {
			$setting = $this->wp_customize->get_setting( $setting_id );
			if ( $setting ) {
				$setting->transport = 'refresh';
			}
		}
	}

	/**
	 * Remove the Cover Template section if needed.
	 *
	 * Prevent showing the "Cover Template" section if the active (non-Reader) theme does not have the same template
	 * as Twenty Twenty, as otherwise the user would be shown a section that would never reflect any preview change.
	 */
	protected function remove_cover_template_section() {
		if ( ! $this->reader_theme_loader->is_theme_overridden() ) {
			return;
		}

		$active_theme = $this->reader_theme_loader->get_active_theme();
		$reader_theme = $this->reader_theme_loader->get_reader_theme();
		if ( ! $active_theme instanceof WP_Theme || ! $reader_theme instanceof WP_Theme ) {
			return;
		}

		// This only applies to Twenty Twenty.
		if ( $reader_theme->get_template() !== 'twentytwenty' ) {
			return;
		}

		// Prevent deactivating the cover template if the active theme and reader theme both have a cover template.
		$cover_template_name = 'templates/template-cover.php';
		if (
			array_key_exists( $cover_template_name, $active_theme->get_page_templates() )
			&&
			array_key_exists( $cover_template_name, $reader_theme->get_page_templates() )
		) {
			return;
		}

		$this->wp_customize->remove_section( 'cover_template_options' );
	}

	/**
	 * Remove the Homepage Settings section in the AMP Customizer for a Reader theme if needed.
	 *
	 * The Homepage Settings section exclusively contains controls for options which apply to both AMP and non-AMP.
	 * If this is the case and there are no other controls added to it, then remove the section. Otherwise, the controls
	 * will all get the same notice added to them.
	 */
	protected function remove_homepage_settings_section() {
		if ( ! $this->reader_theme_loader->is_theme_overridden() ) {
			return;
		}

		$section_id  = 'static_front_page';
		$control_ids = [];
		foreach ( $this->wp_customize->controls() as $control ) {
			/** @var WP_Customize_Control $control */
			if ( $section_id === $control->section ) {
				$control_ids[] = $control->id;
			}
		}

		$static_front_page_control_ids = [
			'show_on_front',
			'page_on_front',
			'page_for_posts',
		];

		if ( count( array_diff( $control_ids, $static_front_page_control_ids ) ) === 0 ) {
			$this->wp_customize->remove_section( $section_id );
		}
	}

	/**
	 * Init Customizer preview for legacy.
	 *
	 * @since 0.4
	 */
	public function init_legacy_preview() {
		add_action( 'amp_post_template_head', 'wp_no_robots' );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_legacy_preview_scripts' ] );
		add_action( 'amp_customizer_enqueue_preview_scripts', [ $this, 'enqueue_legacy_preview_scripts' ] );

		// Output scripts and styles which will break AMP validation only when preview is opened with controls for manipulation.
		if ( $this->wp_customize->get_messenger_channel() ) {
			add_action( 'amp_post_template_head', [ $this->wp_customize, 'customize_preview_loading_style' ] );
			add_action( 'amp_post_template_css', [ $this, 'add_legacy_customize_preview_styles' ] );
			add_action( 'amp_post_template_head', [ $this->wp_customize, 'remove_frameless_preview_messenger_channel' ] );
			add_action( 'amp_post_template_footer', [ $this, 'add_legacy_preview_scripts' ] );
		}
	}

	/**
	 * Sets up the AMP Customizer preview.
	 */
	public function register_legacy_ui() {
		$this->wp_customize->add_panel(
			self::PANEL_ID,
			[
				'type'        => 'amp',
				'title'       => __( 'AMP', 'amp' ),
				'description' => $this->get_amp_panel_description(),
			]
		);

		/**
		 * Fires after the AMP panel has been registered for plugins to add additional controls.
		 *
		 * In practice the `customize_register` hook should be used instead.
		 *
		 * @since 0.4
		 * @param WP_Customize_Manager $manager Manager.
		 */
		do_action( 'amp_customizer_register_ui', $this->wp_customize );
	}

	/**
	 * Get AMP panel description.
	 *
	 * This is also added to the root panel description in the AMP Customizer when a Reader theme is being customized.
	 *
	 * @return string Description, with markup.
	 */
	protected function get_amp_panel_description() {
		return wp_kses_post(
			sprintf(
				/* translators: 1: URL to AMP project, 2: URL to admin settings screen */
				__( 'While <a href="%1$s" target="_blank">AMP</a> works well on both desktop and mobile pages, your site is <a href="%2$s" target="_blank">currently configured</a> in Reader mode to serve AMP pages to mobile visitors. These settings customize the experience for these users.', 'amp' ),
				'https://amp.dev',
				admin_url( 'admin.php?page=amp-options' )
			)
		);
	}

	/**
	 * Registers settings for customizing Legacy Reader AMP templates.
	 *
	 * @since 0.4
	 */
	public function register_legacy_settings() {

		/**
		 * Fires when plugins should register settings for AMP.
		 *
		 * In practice the `customize_register` hook should be used instead.
		 *
		 * @since 0.4
		 * @param WP_Customize_Manager $manager Manager.
		 */
		do_action( 'amp_customizer_register_settings', $this->wp_customize );
	}

	/**
	 * Load up AMP scripts needed for Customizer integrations when a Reader theme has been selected.
	 *
	 * @since 2.0
	 */
	public function add_customizer_scripts() {
		$handle       = 'amp-customize-controls';
		$asset_file   = AMP__DIR__ . '/assets/js/amp-customize-controls.asset.php';
		$asset        = require $asset_file;
		$dependencies = $asset['dependencies'];
		$version      = $asset['version'];

		/** This action is documented in includes/class-amp-theme-support.php */
		do_action( 'amp_register_polyfills' );

		wp_enqueue_script(
			$handle,
			amp_get_asset_url( 'js/amp-customize-controls.js' ),
			array_merge( $dependencies, [ 'jquery', 'customize-controls' ] ),
			$version,
			true
		);
		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( $handle, 'amp' );
		} elseif ( function_exists( 'wp_get_jed_locale_data' ) || function_exists( 'gutenberg_get_jed_locale_data' ) ) {
			$locale_data = function_exists( 'wp_get_jed_locale_data' ) ? wp_get_jed_locale_data( 'amp' ) : gutenberg_get_jed_locale_data( 'amp' );
			wp_add_inline_script(
				$handle,
				sprintf( 'wp.i18n.setLocaleData( %s, "amp" );', wp_json_encode( $locale_data ) ),
				'after'
			);
		}

		$option_settings = [];
		foreach ( $this->wp_customize->settings() as $setting ) {
			/** @var WP_Customize_Setting $setting */
			if ( 'option' === $setting->type ) {
				$option_settings[] = $setting->id;
			}
		}

		wp_add_inline_script(
			$handle,
			sprintf(
				'ampCustomizeControls.boot( %s );',
				wp_json_encode(
					[
						'queryVar'                  => amp_get_slug(),
						'optionSettings'            => $option_settings,
						'activeThemeSettingImports' => $this->get_active_theme_import_settings(),
						'mimeTypeIcons'             => [
							'image'    => wp_mime_type_icon( 'image' ),
							'document' => wp_mime_type_icon( 'document' ),
						],
						'l10n'                      => [
							/* translators: placeholder is URL to non-AMP Customizer. */
							'ampVersionNotice'     => wp_kses_post( sprintf( __( 'You are customizing the AMP version of your site. <a href="%s">Customize non-AMP version</a>.', 'amp' ), esc_url( admin_url( 'customize.php' ) ) ) ),
							'rootPanelDescription' => $this->get_amp_panel_description(),
						],
					]
				)
			)
		);

		wp_enqueue_style(
			'amp-customizer',
			amp_get_asset_url( 'css/amp-customizer.css' ),
			[],
			AMP__VERSION
		);

		wp_styles()->add_data( 'amp-customizer', 'rtl', 'replace' );
	}

	/**
	 * Store the timestamps for modified theme settings.
	 *
	 * This is used to determine which settings from the Active theme should be presented for importing into the Reader
	 * theme. If a setting has been modified more recently in the Reader theme, then it doesn't make much sense to offer
	 * for the user to re-import a customization they already made.
	 */
	public function store_modified_theme_mod_setting_timestamps() {
		$modified_setting_ids = [];
		foreach ( array_keys( $this->wp_customize->unsanitized_post_values() ) as $setting_id ) {
			$setting = $this->wp_customize->get_setting( $setting_id );
			if ( ! ( $setting instanceof WP_Customize_Setting ) || $setting instanceof WP_Customize_Filter_Setting ) {
				continue;
			}

			if ( $setting instanceof WP_Customize_Custom_CSS_Setting ) {
				$modified_setting_ids[] = $setting->id_data()['base']; // Remove theme slug from ID.
			} elseif ( 'theme_mod' === $setting->type ) {
				$modified_setting_ids[] = $setting->id;
			}
		}

		if ( empty( $modified_setting_ids ) ) {
			return;
		}

		$theme_mod_timestamps = get_theme_mod( self::THEME_MOD_TIMESTAMPS_KEY, [] );
		foreach ( $modified_setting_ids as $modified_setting_id ) {
			$theme_mod_timestamps[ $modified_setting_id ] = time();
		}
		set_theme_mod( self::THEME_MOD_TIMESTAMPS_KEY, $theme_mod_timestamps );
	}

	/**
	 * Get settings to import from the active theme.
	 *
	 * @return array Map of setting IDs to setting values.
	 */
	protected function get_active_theme_import_settings() {
		$active_theme = $this->reader_theme_loader->get_active_theme();
		if ( ! $active_theme instanceof WP_Theme ) {
			return [];
		}

		$active_theme_mods = get_option( 'theme_mods_' . $active_theme->get_stylesheet(), [] );
		$import_settings   = [];

		$active_setting_timestamps = isset( $active_theme_mods[ self::THEME_MOD_TIMESTAMPS_KEY ] ) ? $active_theme_mods[ self::THEME_MOD_TIMESTAMPS_KEY ] : [];
		$reader_setting_timestamps = get_theme_mod( self::THEME_MOD_TIMESTAMPS_KEY, [] );

		// Remove theme mods which will not be imported directly.
		unset(
			$active_theme_mods['sidebars_widgets'],
			$active_theme_mods['custom_css_post_id'],
			$active_theme_mods['background_preset'], // Since a meta setting. When importing a background setting, will be set to 'custom'.
			$active_theme_mods[ self::THEME_MOD_TIMESTAMPS_KEY ]
		);

		// Avoid offering to import background image settings if no background image is set.
		if ( empty( $active_theme_mods['background_image'] ) ) {
			foreach ( [ 'background_position_x', 'background_position_y', 'background_size', 'background_repeat', 'background_attachment' ] as $setting_id ) {
				unset( $active_theme_mods[ $setting_id ] );
			}
		}

		// Map nav menus for importing.
		if ( isset( $active_theme_mods['nav_menu_locations'] ) ) {
			$nav_menu_locations = wp_map_nav_menu_locations(
				get_theme_mod( 'nav_menu_locations', [] ),
				$active_theme_mods['nav_menu_locations']
			);
			foreach ( $nav_menu_locations as $nav_menu_location => $menu_id ) {
				$setting = $this->wp_customize->get_setting( "nav_menu_locations[$nav_menu_location]" );
				if (
					$setting instanceof WP_Customize_Setting
					&&
					// Skip presenting settings which have been more recently updated in the Reader theme.
					(
						! isset( $active_setting_timestamps[ $setting->id ], $reader_setting_timestamps[ $setting->id ] )
						||
						$active_setting_timestamps[ $setting->id ] > $reader_setting_timestamps[ $setting->id ]
					)
				) {
					/** This filter is documented in wp-includes/class-wp-customize-manager.php */
					$value = apply_filters( "customize_sanitize_js_{$setting->id}", $menu_id, $setting );

					$import_settings[ $setting->id ] = $value;
				}
			}
			unset( $active_theme_mods['nav_menu_locations'] );
		}

		foreach ( $this->wp_customize->settings() as $setting ) {
			/** @var WP_Customize_Setting $setting */
			if (
				'theme_mod' !== $setting->type
				||
				// Skip presenting settings which have been more recently updated in the Reader theme.
				(
					isset( $active_setting_timestamps[ $setting->id ], $reader_setting_timestamps[ $setting->id ] )
					&&
					$reader_setting_timestamps[ $setting->id ] > $active_setting_timestamps[ $setting->id ]
				)
			) {
				continue;
			}

			$id_data = $setting->id_data();
			if ( ! array_key_exists( $id_data['base'], $active_theme_mods ) ) {
				continue;
			}
			$value   = $active_theme_mods[ $id_data['base'] ];
			$subkeys = $id_data['keys'];
			while ( ! empty( $subkeys ) ) {
				$subkey = array_shift( $subkeys );
				if ( ! is_array( $value ) || ! array_key_exists( $subkey, $value ) ) {
					// Move on to the next setting.
					continue 2;
				}
				$value = $value[ $subkey ];
			}

			/** This filter is documented in wp-includes/class-wp-customize-manager.php */
			$value = apply_filters( "customize_sanitize_js_{$setting->id}", $value, $setting );

			$import_settings[ $setting->id ] = $value;
		}

		// Import Custom CSS if it has not been more recently updated in the Reader theme.
		if (
			! isset( $active_setting_timestamps['custom_css'], $reader_setting_timestamps['custom_css'] )
			||
			$active_setting_timestamps['custom_css'] > $reader_setting_timestamps['custom_css']
		) {
			$custom_css_setting = $this->wp_customize->get_setting( sprintf( 'custom_css[%s]', get_stylesheet() ) );
			$custom_css_post    = wp_get_custom_css_post( $active_theme->get_stylesheet() );
			if ( $custom_css_setting instanceof WP_Customize_Custom_CSS_Setting && $custom_css_post instanceof WP_Post ) {
				$value = $custom_css_post->post_content;

				/** This filter is documented in wp-includes/class-wp-customize-setting.php */
				$value = apply_filters( 'customize_value_custom_css', $value, $custom_css_setting );

				/** This filter is documented in wp-includes/class-wp-customize-manager.php */
				$value = apply_filters( "customize_sanitize_js_{$custom_css_setting->id}", $value, $custom_css_setting );

				$import_settings[ $custom_css_setting->id ] = $value;
			}
		}

		return $import_settings;
	}

	/**
	 * Render template for the setting import "section".
	 *
	 * This section only has a menu item and it is not intended to expand.
	 */
	public function render_setting_import_section_template() {
		?>
		<script type="text/html" id="tmpl-customize-section-amp_active_theme_settings_import">
			<li id="accordion-section-{{ data.id }}" class="accordion-section control-section control-section-{{ data.type }}">
				<h3 class="accordion-section-title">
					<button type="button" class="button button-secondary" aria-label="<?php esc_attr_e( 'Import settings', 'amp' ); ?>">
						<?php echo esc_html( _ex( 'Import', 'theme', 'amp' ) ); ?>
					</button>
					<details>
						<summary>{{ data.title }}</summary>
						<div>
							<p>
								<?php esc_html_e( 'You can import some settings from the primary Active theme into the corresponding Reader theme settings.', 'amp' ); ?>
							</p>
							<dl></dl>
						</div>
					</details>
				</h3>
				<ul class="accordion-section-content"></ul>
			</li>
		</script>
		<?php
	}

	/**
	 * Load up AMP scripts needed for Customizer integrations in Legacy Reader mode.
	 *
	 * @since 0.6 Originally called add_customizer_scripts.
	 * @since 2.0
	 */
	public function add_legacy_customizer_scripts() {
		$asset_file   = AMP__DIR__ . '/assets/js/amp-customize-controls-legacy.asset.php';
		$asset        = require $asset_file;
		$dependencies = $asset['dependencies'];
		$version      = $asset['version'];

		/** This action is documented in includes/class-amp-theme-support.php */
		do_action( 'amp_register_polyfills' );

		wp_enqueue_script(
			'amp-customize-controls', // Note: This is not 'amp-customize-controls-legacy' to not break existing scripts that have this dependency.
			amp_get_asset_url( 'js/amp-customize-controls-legacy.js' ),
			array_merge( $dependencies, [ 'jquery', 'customize-controls' ] ),
			$version,
			true
		);

		wp_add_inline_script(
			'amp-customize-controls',
			sprintf(
				'ampCustomizeControls.boot( %s );',
				wp_json_encode(
					[
						'queryVar' => amp_get_slug(),
						'panelId'  => self::PANEL_ID,
						'ampUrl'   => amp_admin_get_preview_permalink(),
						'l10n'     => [
							'unavailableMessage'  => __( 'AMP is not available for the page currently being previewed.', 'amp' ),
							'unavailableLinkText' => __( 'Navigate to an AMP compatible page', 'amp' ),
						],
					]
				)
			)
		);

		wp_enqueue_style(
			'amp-customizer',
			amp_get_asset_url( 'css/amp-customizer-legacy.css' ),
			[],
			AMP__VERSION
		);

		wp_styles()->add_data( 'amp-customizer', 'rtl', 'replace' );

		/**
		 * Fires when plugins should register settings for AMP.
		 *
		 * In practice the `customize_controls_enqueue_scripts` hook should be used instead.
		 *
		 * @since 0.4
		 * @param WP_Customize_Manager $manager Manager.
		 */
		do_action( 'amp_customizer_enqueue_scripts', $this->wp_customize );
	}

	/**
	 * Enqueues scripts used in both the AMP and non-AMP Customizer preview (only applies to Legacy Reader mode).
	 *
	 * @since 0.6
	 */
	public function enqueue_legacy_preview_scripts() {
		// Bail if user can't customize anyway.
		if ( ! current_user_can( 'customize' ) ) {
			return;
		}

		/** This action is documented in includes/class-amp-theme-support.php */
		do_action( 'amp_register_polyfills' );

		$asset_file   = AMP__DIR__ . '/assets/js/amp-customize-preview-legacy.asset.php';
		$asset        = require $asset_file;
		$dependencies = $asset['dependencies'];
		$version      = $asset['version'];

		wp_enqueue_script(
			'amp-customize-preview',
			amp_get_asset_url( 'js/amp-customize-preview-legacy.js' ),
			array_merge( $dependencies, [ 'jquery', 'customize-preview' ] ),
			$version,
			true
		);

		wp_add_inline_script(
			'amp-customize-preview',
			sprintf(
				'ampCustomizePreview.boot( %s );',
				wp_json_encode(
					[
						'available' => amp_is_available(),
						'enabled'   => amp_is_request(),
					]
				)
			)
		);
	}

	/**
	 * Add AMP Customizer preview styles for Legacy Reader mode.
	 */
	public function add_legacy_customize_preview_styles() {
		?>
		/* Text meant only for screen readers; this is needed for wp.a11y.speak() */
		.screen-reader-text {
			border: 0;
			clip: rect(1px, 1px, 1px, 1px);
			-webkit-clip-path: inset(50%);
			clip-path: inset(50%);
			height: 1px;
			margin: -1px;
			overflow: hidden;
			padding: 0;
			position: absolute;
			width: 1px;
			word-wrap: normal !important;
		}
		body.wp-customizer-unloading {
			opacity: 0.25 !important; /* Because AMP sets body to opacity:1 once layout complete. */
		}
		<?php
	}

	/**
	 * Enqueues Legacy Reader scripts and does wp_print_footer_scripts() so we can output customizer scripts.
	 *
	 * This breaks AMP validation in the customizer but is necessary for the live preview.
	 *
	 * @since 0.6
	 */
	public function add_legacy_preview_scripts() {

		// Bail if user can't customize anyway.
		if ( ! current_user_can( 'customize' ) ) {
			return;
		}

		wp_enqueue_script( 'customize-selective-refresh' );

		/**
		 * Fires when plugins should enqueue their own scripts for the AMP Customizer preview.
		 *
		 * @since 0.4
		 * @param WP_Customize_Manager $wp_customize Manager.
		 */
		do_action( 'amp_customizer_enqueue_preview_scripts', $this->wp_customize );

		$this->wp_customize->customize_preview_settings();
		$this->wp_customize->selective_refresh->export_preview_data();

		wp_print_footer_scripts();
	}

	/**
	 * Print templates needed for AMP in Customizer (for Legacy Reader mode).
	 *
	 * @since 0.6
	 */
	public function print_legacy_controls_templates() {
		?>
		<script type="text/html" id="tmpl-customize-amp-enabled-toggle">
			<div class="amp-toggle">
				<# var elementIdPrefix = _.uniqueId( 'customize-amp-enabled-toggle' ); #>
				<div id="{{ elementIdPrefix }}tooltip" aria-hidden="true" class="tooltip" role="tooltip">
					{{ data.message }}
					<# if ( data.url ) { #>
						<a href="{{ data.url }}">{{ data.linkText }}</a>
					<# } #>
				</div>
				<input id="{{ elementIdPrefix }}checkbox" type="checkbox" class="disabled" aria-describedby="{{ elementIdPrefix }}tooltip">
				<span class="slider"></span>
				<label for="{{ elementIdPrefix }}checkbox" class="screen-reader-text"><?php esc_html_e( 'AMP preview enabled', 'amp' ); ?></label>
			</div>
		</script>
		<script type="text/html" id="tmpl-customize-amp-unavailable-notification">
			<li class="notice notice-{{ data.type || 'info' }} {{ data.alt ? 'notice-alt' : '' }} {{ data.containerClasses || '' }}" data-code="{{ data.code }}" data-type="{{ data.type }}">
				<div class="notification-message">
					{{ data.message }}
					<# if ( data.url ) { #>
						<a href="{{ data.url }}">{{ data.linkText }}</a>
					<# } #>
				</div>
			</li>
		</script>
		<?php
	}

	/**
	 * Whether the Customizer is AMP. This is always true since the AMP Customizer has been merged with the main Customizer.
	 *
	 * @codeCoverageIgnore
	 * @deprecated 0.6
	 * @return bool
	 */
	public static function is_amp_customizer() {
		_deprecated_function( __METHOD__, '0.6' );
		return true;
	}
}
