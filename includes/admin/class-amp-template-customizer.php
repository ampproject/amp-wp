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
	 * Customizer instance.
	 *
	 * @since 0.4
	 * @var WP_Customize_Manager $wp_customize
	 */
	protected $wp_customize;

	/**
	 * Reader theme loader.
	 *
	 * @since 1.6
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
	 * @param WP_Customize_Manager $wp_customize Customizer instance.
	 * @return AMP_Template_Customizer Instance.
	 */
	public static function init( WP_Customize_Manager $wp_customize ) {
		/** @var ReaderThemeLoader $reader_theme_loader */
		$reader_theme_loader = Services::get( 'reader_theme_loader' );

		$self = new self( $wp_customize, $reader_theme_loader );

		$is_reader_mode   = ( AMP_Theme_Support::READER_MODE_SLUG === AMP_Options_Manager::get_option( Option::THEME_SUPPORT ) );
		$has_reader_theme = ( ReaderThemes::DEFAULT_READER_THEME !== AMP_Options_Manager::get_option( Option::READER_THEME ) );

		if ( $is_reader_mode ) {
			if ( $reader_theme_loader->is_theme_overridden() ) {
				add_action( 'customize_controls_enqueue_scripts', [ $self, 'add_customizer_scripts' ] );
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
	 * Force changes to header video to cause refresh since logic in wp-customize-header.js does not construct AMP components.
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
	 * @since 1.6
	 */
	public function add_customizer_scripts() {
		$asset_file   = AMP__DIR__ . '/assets/js/amp-customize-controls.asset.php';
		$asset        = require $asset_file;
		$dependencies = $asset['dependencies'];
		$version      = $asset['version'];

		wp_enqueue_script(
			'amp-customize-controls',
			amp_get_asset_url( 'js/amp-customize-controls.js' ),
			array_merge( $dependencies, [ 'jquery', 'customize-controls' ] ),
			$version,
			true
		);

		$option_settings = [];
		foreach ( $this->wp_customize->settings() as $setting ) {
			/** @var WP_Customize_Setting $setting */
			if ( 'option' === $setting->type ) {
				$option_settings[] = $setting->id;
			}
		}

		wp_add_inline_script(
			'amp-customize-controls',
			sprintf(
				'ampCustomizeControls.boot( %s );',
				wp_json_encode(
					[
						'queryVar'       => amp_get_slug(),
						'optionSettings' => $option_settings,
						'l10n'           => [
							/* translators: placeholder is URL to non-AMP Customizer. */
							'ampVersionNotice'     => wp_kses_post( sprintf( __( 'You are customizing the AMP version of your site. <a href="%s">Customize non-AMP version</a>.', 'amp' ), esc_url( admin_url( 'customize.php' ) ) ) ),
							'optionSettingNotice'  => __( 'Also applies to non-AMP version of your site.', 'amp' ),
							'navMenusPanelNotice'  => __( 'The menus here are shared with the non-AMP version of your site. Assign existing menus to menu locations in the Reader theme or create new AMP-specific menus.', 'amp' ),
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
	 * Load up AMP scripts needed for Customizer integrations in Legacy Reader mode.
	 *
	 * @since 0.6 Originally called add_customizer_scripts.
	 * @since 1.6
	 */
	public function add_legacy_customizer_scripts() {
		$asset_file   = AMP__DIR__ . '/assets/js/amp-customize-controls-legacy.asset.php';
		$asset        = require $asset_file;
		$dependencies = $asset['dependencies'];
		$version      = $asset['version'];

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
						'available' => is_amp_available(),
						'enabled'   => is_amp_endpoint(),
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
