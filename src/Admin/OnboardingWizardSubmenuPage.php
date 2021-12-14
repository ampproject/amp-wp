<?php
/**
 * OnboardingWizardSubmenuPage class.
 *
 * @package AmpProject\AmpWP
 * @since 2.0
 */

namespace AmpProject\AmpWP\Admin;

use AMP_Options_Manager;
use AMP_Validated_URL_Post_Type;
use AMP_Validation_Manager;
use AmpProject\AmpWP\DependencySupport;
use AmpProject\AmpWP\DevTools\UserAccess;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\LoadingError;
use AmpProject\AmpWP\QueryVar;
use AmpProject\AmpWP\Validation\ScannableURLsRestController;

/**
 * AMP setup wizard submenu page class.
 *
 * @since 2.0
 * @internal
 */
final class OnboardingWizardSubmenuPage implements Delayed, Registerable, Service {
	/**
	 * Handle for JS file.
	 *
	 * @since 2.0
	 *
	 * @var string
	 */
	const ASSET_HANDLE = 'amp-onboarding-wizard';

	/**
	 * HTML ID for the app root element.
	 *
	 * @since 2.0
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
	 * ReaderThemes instance.
	 *
	 * @var ReaderThemes
	 */
	private $reader_themes;

	/**
	 * RESTPreloader instance.
	 *
	 * @var RESTPreloader
	 */
	private $rest_preloader;

	/**
	 * LoadingError instance.
	 *
	 * @var LoadingError
	 */
	private $loading_error;

	/**
	 * DependencySupport instance.
	 *
	 * @var DependencySupport
	 */
	private $dependency_support;

	/**
	 * OnboardingWizardSubmenuPage constructor.
	 *
	 * @param GoogleFonts       $google_fonts       An instance of the GoogleFonts service.
	 * @param ReaderThemes      $reader_themes      An instance of the ReaderThemes class.
	 * @param RESTPreloader     $rest_preloader     An instance of the RESTPreloader class.
	 * @param LoadingError      $loading_error      An instance of the LoadingError class.
	 * @param DependencySupport $dependency_support An instance of the DependencySupport class.
	 */
	public function __construct( GoogleFonts $google_fonts, ReaderThemes $reader_themes, RESTPreloader $rest_preloader, LoadingError $loading_error, DependencySupport $dependency_support ) {
		$this->google_fonts       = $google_fonts;
		$this->reader_themes      = $reader_themes;
		$this->rest_preloader     = $rest_preloader;
		$this->loading_error      = $loading_error;
		$this->dependency_support = $dependency_support;
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
	 * @since 2.0
	 */
	public function register() {
		add_action( 'admin_head-' . $this->screen_handle(), [ $this, 'override_template' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_filter( 'admin_title', [ $this, 'override_title' ] );
	}

	/**
	 * Overrides the admin title on the wizard screen. Without this filter, the title portion would be empty.
	 *
	 * @param string $admin_title The unfiltered admin title.
	 * @return string If on the wizard screen, the admin title with the page title prepended.
	 */
	public function override_title( $admin_title ) {
		if ( $this->screen_handle() !== get_current_screen()->id ) {
			return $admin_title;
		}

		return esc_html__( 'AMP Onboarding Wizard', 'amp' ) . $admin_title;
	}

	/**
	 * Renders the setup wizard screen output and exits.
	 *
	 * @since 2.0
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

		/** This action is documented in wp-admin/admin-header.php */
		do_action( 'admin_head' );

		// <head> tag was opened prior to this action and hasn't been closed.
		?>
		</head>
		<body class="no-js">
			<script>document.body.className = document.body.className.replace('no-js','js');</script>
			<?php // The admin footer template closes three divs. ?>
			<div>
			<div>
			<div>
			<div class="amp" id="<?php echo esc_attr( self::APP_ROOT_ID ); ?>">
				<?php $this->loading_error->render(); ?>
			</div>

			<style>
			#wpfooter { display:none; }
			</style>
		<?php

		require_once ABSPATH . 'wp-admin/admin-footer.php';
	}

	/**
	 * Provides the setup screen handle.
	 *
	 * @since 2.0
	 *
	 * @return string
	 */
	public function screen_handle() {
		return sprintf( 'admin_page_%s', OnboardingWizardSubmenu::SCREEN_ID );
	}

	/**
	 * Enqueues setup assets.
	 *
	 * @since 2.0
	 *
	 * @param string $hook_suffix The current admin page.
	 */
	public function enqueue_assets( $hook_suffix ) {
		if ( $this->screen_handle() !== $hook_suffix ) {
			return;
		}

		/** This action is documented in includes/class-amp-theme-support.php */
		do_action( 'amp_register_polyfills' );

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
			[
				'wp-components',
				$this->google_fonts->get_handle(),
			],
			AMP__VERSION
		);

		wp_styles()->add_data( self::ASSET_HANDLE, 'rtl', 'replace' );

		$theme           = wp_get_theme();
		$is_reader_theme = $this->reader_themes->theme_data_exists( get_stylesheet() );

		$amp_settings_link       = add_query_arg(
			[ QueryVar::AMP_SCAN_IF_STALE => 1 ],
			menu_page_url( AMP_Options_Manager::OPTION_NAME, false )
		);
		$amp_validated_urls_link = admin_url(
			add_query_arg(
				[ 'post_type' => AMP_Validated_URL_Post_Type::POST_TYPE_SLUG ],
				'edit.php'
			)
		);

		$setup_wizard_data = [
			'AMP_OPTIONS_KEY'                    => AMP_Options_Manager::OPTION_NAME,
			'AMP_QUERY_VAR'                      => amp_get_slug(),
			'LEGACY_THEME_SLUG'                  => ReaderThemes::DEFAULT_READER_THEME,
			'APP_ROOT_ID'                        => self::APP_ROOT_ID,
			'AMP_SCAN_IF_STALE'                  => QueryVar::AMP_SCAN_IF_STALE,
			'CUSTOMIZER_LINK'                    => add_query_arg(
				[
					'return' => rawurlencode( $amp_settings_link ),
				],
				admin_url( 'customize.php' )
			),
			'CLOSE_LINK'                         => $this->get_close_link(),
			// @todo As of June 2020, an upcoming WP release will allow this to be retrieved via REST.
			'CURRENT_THEME'                      => [
				'name'            => $theme->get( 'Name' ),
				'description'     => $theme->get( 'Description' ),
				'is_reader_theme' => $is_reader_theme,
				'screenshot'      => $theme->get_screenshot() ?: null,
				'url'             => $theme->get( 'ThemeURI' ),
			],
			'HAS_DEPENDENCY_SUPPORT'             => $this->dependency_support->has_support(),
			'USING_FALLBACK_READER_THEME'        => $this->reader_themes->using_fallback_theme(),
			'SCANNABLE_URLS_REST_PATH'           => '/amp/v1/scannable-urls',
			'SETTINGS_LINK'                      => $amp_settings_link,
			'OPTIONS_REST_PATH'                  => '/amp/v1/options',
			'READER_THEMES_REST_PATH'            => '/amp/v1/reader-themes',
			'UPDATES_NONCE'                      => current_user_can( 'install_themes' ) ? wp_create_nonce( 'updates' ) : '',
			'USER_FIELD_DEVELOPER_TOOLS_ENABLED' => UserAccess::USER_FIELD_DEVELOPER_TOOLS_ENABLED,
			'USERS_RESOURCE_REST_PATH'           => '/wp/v2/users',
			'VALIDATE_NONCE'                     => AMP_Validation_Manager::has_cap() ? AMP_Validation_Manager::get_amp_validate_nonce() : '',
			'VALIDATED_URLS_LINK'                => $amp_validated_urls_link,
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

		$this->add_preload_rest_paths();
	}

	/**
	 * Adds REST paths to preload.
	 */
	protected function add_preload_rest_paths() {
		$paths = [
			'/amp/v1/options',
			'/amp/v1/reader-themes',
			add_query_arg(
				[
					'_fields' => [ 'url', 'amp_url', 'type', 'label' ],
					ScannableURLsRestController::FORCE_STANDARD_MODE => 1,
				],
				'/amp/v1/scannable-urls'
			),
			add_query_arg(
				'_fields',
				[ 'author', 'name', 'plugin', 'status', 'version' ],
				'/wp/v2/plugins'
			),
			'/wp/v2/settings',
			add_query_arg(
				'_fields',
				[ 'author', 'name', 'status', 'stylesheet', 'version' ],
				'/wp/v2/themes'
			),
			'/wp/v2/users/me',
		];

		foreach ( $paths as $path ) {
			$this->rest_preloader->add_preloaded_path( $path );
		}
	}

	/**
	 * Determine URL that should be used to close the Onboarding Wizard.
	 *
	 * @return string Close link.
	 */
	public function get_close_link() {
		$referer = wp_get_referer();

		if ( $referer && 'wp-login.php' !== wp_basename( wp_parse_url( $referer, PHP_URL_PATH ) ) ) {
			return $referer;
		}

		// Default to the AMP Settings page if a referrer link could not be determined.
		return menu_page_url( AMP_Options_Manager::OPTION_NAME, false );
	}
}
