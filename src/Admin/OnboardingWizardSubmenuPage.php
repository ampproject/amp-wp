<?php
/**
 * OnboardingWizardSubmenuPage class.
 *
 * @package AMP
 * @since 1.6.0
 */

namespace AmpProject\AmpWP\Admin;

use AMP_Options_Manager;
use AMP_Reader_Themes;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\QueryVars;
use AmpProject\AmpWP\Services;

/**
 * AMP setup wizard submenu page class.
 *
 * @since 1.6.0
 */
final class OnboardingWizardSubmenuPage implements Delayed, Registerable, Service {
	/**
	 * Handle for JS file.
	 *
	 * @since 1.6.0
	 *
	 * @var string
	 */
	const ASSET_HANDLE = 'amp-onboarding-wizard';

	/**
	 * HTML ID for the app root element.
	 *
	 * @since 1.6.0
	 *
	 * @var string
	 */
	const APP_ROOT_ID = 'amp-onboarding-wizard';

	/**
	 * GoogleFonts instance.
	 *
	 * @var GoogleFonts
	 */
	private $google_fonts;

	/**
	 * OnboardingWizardSubmenuPage constructor.
	 *
	 * @param GoogleFonts $google_fonts An instance of the GoogleFonts service.
	 */
	public function __construct( GoogleFonts $google_fonts ) {
		$this->google_fonts = $google_fonts;
	}

	/**
	 * Get the action to use for registering the service.
	 *
	 * @return string Registration action to use.
	 */
	public static function get_registration_action() {
		return 'admin_init';
	}

	/**
	 * Sets up hooks.
	 *
	 * @since 1.6.0
	 */
	public function register() {
		add_action( 'admin_head-' . $this->screen_handle(), [ $this, 'override_template' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	/**
	 * Renders the setup wizard screen output and exits.
	 *
	 * @since 1.6.0
	 */
	public function override_template() {
		$this->render();

		exit();
	}

	/**
	 * Renders the setup wizard screen output, beginning just before the closing head tag.
	 */
	public function render() {
		// Remove standard admin footer content.
		add_filter( 'admin_footer_text', '__return_empty_string' );
		remove_all_filters( 'update_footer' );

		do_action( 'admin_head' );

		// <head> tag was opened prior to this action and hasn't been closed.
		?>
		</head>
		<body>
			<?php // The admin footer template closes three divs. ?>
			<div>
			<div>
			<div>
			<div class="amp" id="<?php echo esc_attr( static::APP_ROOT_ID ); ?>"></div>

			<style>
			#wpfooter { display:none; }
			</style>
		<?php

		require_once ABSPATH . 'wp-admin/admin-footer.php';
	}

	/**
	 * Provides the setup screen handle.
	 *
	 * @since 1.6.0
	 *
	 * @return string
	 */
	public function screen_handle() {
		return sprintf( 'amp_page_%s', OnboardingWizardSubmenu::SCREEN_ID );
	}

	/**
	 * Enqueues setup assets.
	 *
	 * @since 1.6.0
	 *
	 * @param string $hook_suffix The current admin page.
	 */
	public function enqueue_assets( $hook_suffix ) {
		if ( $this->screen_handle() !== $hook_suffix ) {
			return;
		}

		/** @var AmpSlugCustomizationWatcher $amp_slug_customization_watcher */
		$amp_slug_customization_watcher = Services::get( 'amp_slug_customization_watcher' );

		$asset_file   = AMP__DIR__ . '/assets/js/' . self::ASSET_HANDLE . '.asset.php';
		$asset        = require $asset_file;
		$dependencies = $asset['dependencies'];
		$version      = $asset['version'];

		wp_enqueue_script(
			self::ASSET_HANDLE,
			amp_get_asset_url( 'js/' . self::ASSET_HANDLE . '.js' ),
			$dependencies,
			$version,
			true
		);

		wp_enqueue_style(
			self::ASSET_HANDLE,
			amp_get_asset_url( 'css/amp-onboarding-wizard.css' ),
			[ $this->google_fonts->get_handle() ],
			AMP__VERSION
		);

		wp_styles()->add_data( self::ASSET_HANDLE, 'rtl', 'replace' );

		$theme           = wp_get_theme();
		$is_reader_theme = in_array( get_stylesheet(), wp_list_pluck( ( new AMP_Reader_Themes() )->get_themes(), 'slug' ), true );

		$exit_link = menu_page_url( AMP_Options_Manager::OPTION_NAME, false );

		$setup_wizard_data = [
			'AMP_OPTIONS_KEY'                    => AMP_Options_Manager::OPTION_NAME,
			'AMP_QUERY_VAR'                      => amp_get_slug(),
			'DEFAULT_AMP_QUERY_VAR'              => QueryVars::AMP,
			'AMP_QUERY_VAR_CUSTOMIZED_LATE'      => $amp_slug_customization_watcher->did_customize_late(),
			'LEGACY_THEME_SLUG'                  => AMP_Reader_Themes::DEFAULT_READER_THEME,
			'APP_ROOT_ID'                        => self::APP_ROOT_ID,
			'CUSTOMIZER_LINK'                    => add_query_arg(
				[
					'return' => rawurlencode( $exit_link ),
				],
				admin_url( 'customize.php' )
			),
			'CLOSE_LINK'                         => wp_get_referer() ?: $exit_link,
			// @todo As of June 2020, an upcoming WP release will allow this to be retrieved via REST.
			'CURRENT_THEME'                      => [
				'name'            => $theme->get( 'Name' ),
				'description'     => $theme->get( 'Description' ),
				'is_reader_theme' => $is_reader_theme,
				'screenshot'      => $theme->get_screenshot(),
				'url'             => $theme->get( 'ThemeURI' ),
			],
			'FINISH_LINK'                        => $exit_link,
			'OPTIONS_REST_ENDPOINT'              => rest_url( 'amp/v1/options' ),
			'READER_THEMES_REST_ENDPOINT'        => rest_url( 'amp/v1/reader-themes' ),
			'UPDATES_NONCE'                      => wp_create_nonce( 'updates' ),
			'USER_FIELD_DEVELOPER_TOOLS_ENABLED' => DevToolsUserAccess::USER_FIELD_DEVELOPER_TOOLS_ENABLED,
			'USER_REST_ENDPOINT'                 => rest_url( 'wp/v2/users/me' ),
		];

		wp_add_inline_script(
			self::ASSET_HANDLE,
			sprintf(
				'var ampSettings = %s;',
				wp_json_encode( $setup_wizard_data )
			),
			'before'
		);

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( self::ASSET_HANDLE, 'amp' );
		} elseif ( function_exists( 'wp_get_jed_locale_data' ) || function_exists( 'gutenberg_get_jed_locale_data' ) ) {
			$locale_data  = function_exists( 'wp_get_jed_locale_data' ) ? wp_get_jed_locale_data( 'amp' ) : gutenberg_get_jed_locale_data( 'amp' );
			$translations = wp_json_encode( $locale_data );

			wp_add_inline_script(
				self::ASSET_HANDLE,
				'wp.i18n.setLocaleData( ' . $translations . ', "amp" );',
				'after'
			);
		}
	}
}
