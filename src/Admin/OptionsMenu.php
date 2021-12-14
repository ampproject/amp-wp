<?php
/**
 * Class OptionsMenu
 *
 * @package Ampproject\Ampwp
 */

namespace AmpProject\AmpWP\Admin;

use AMP_Options_Manager;
use AMP_Validated_URL_Post_Type;
use AMP_Validation_Manager;
use AmpProject\AmpWP\DependencySupport;
use AmpProject\AmpWP\DevTools\UserAccess;
use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\LoadingError;
use AmpProject\AmpWP\QueryVar;

/**
 * OptionsMenu class.
 *
 * @since 2.0
 * @internal
 */
class OptionsMenu implements Conditional, Service, Registerable {
	/**
	 * Handle for JS file.
	 *
	 * @since 2.0
	 *
	 * @var string
	 */
	const ASSET_HANDLE = 'amp-settings';

	/**
	 * The AMP svg menu icon.
	 *
	 * @var string
	 */
	const ICON_BASE64_SVG = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjIiIGhlaWdodD0iNjIiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHBhdGggZD0iTTQxLjYyODg2NjcgMjguMTYxNDMzM2wtMTMuMDA0NSAyMS42NDIxMzM0aC0yLjM1NmwyLjMyOTEzMzMtMTQuMTAxOS03LjIxMzcuMDA5M3MtLjA2ODIuMDAyMDY2Ni0uMTAwMjMzMy4wMDIwNjY2Yy0uNjQ5OTY2NyAwLTEuMTc1OTMzNC0uNTI1OTY2Ni0xLjE3NTkzMzQtMS4xNzU5MzMzIDAtLjI3OS4yNTkzNjY3LS43NTEyMzMzLjI1OTM2NjctLjc1MTIzMzNsMTIuOTYyMTMzMy0yMS42MTYzTDM1LjcyNDQgMTIuMTc5OWwtMi4zODgwMzMzIDE0LjEyMzYgNy4yNTA5LS4wMDkzcy4wNzc1LS4wMDEwMzMzLjExNDctLjAwMTAzMzNjLjY0OTk2NjYgMCAxLjE3NTkzMzMuNTI1OTY2NiAxLjE3NTkzMzMgMS4xNzU5MzMzIDAgLjI2MzUtLjEwMzMzMzMuNDk0OTY2Ny0uMjUwMDY2Ny42OTEzbC4wMDEwMzM0LjAwMTAzMzN6TTMxIDBDMTMuODc4NyAwIDAgMTMuODc5NzMzMyAwIDMxYzAgMTcuMTIxMyAxMy44Nzg3IDMxIDMxIDMxIDE3LjEyMDI2NjcgMCAzMS0xMy44Nzg3IDMxLTMxQzYyIDEzLjg3OTczMzMgNDguMTIwMjY2NyAwIDMxIDB6IiBmaWxsPSIjYTBhNWFhIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiLz48L3N2Zz4=';

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

	/** @var LoadingError */
	private $loading_error;

	/** @var SiteHealth */
	private $site_health;

	/** @var DependencySupport */
	private $dependency_support;

	/**
	 * Check whether the conditional object is currently needed.
	 *
	 * @return bool Whether the conditional object is needed.
	 */
	public static function is_needed() {
		if ( ! is_admin() ) {
			return false;
		}

		/**
		 * Filter whether to enable the AMP settings.
		 *
		 * @since 0.5
		 * @param bool $enable Whether to enable the AMP settings. Default true.
		 */
		return (bool) apply_filters( 'amp_options_menu_is_enabled', true );
	}

	/**
	 * OptionsMenu constructor.
	 *
	 * @param GoogleFonts       $google_fonts An instance of the GoogleFonts service.
	 * @param ReaderThemes      $reader_themes An instance of the ReaderThemes class.
	 * @param RESTPreloader     $rest_preloader An instance of the RESTPreloader class.
	 * @param DependencySupport $dependency_support An instance of the DependencySupport class.
	 * @param LoadingError      $loading_error An instance of the LoadingError class.
	 * @param SiteHealth        $site_health An instance of the SiteHealth class.
	 */
	public function __construct( GoogleFonts $google_fonts, ReaderThemes $reader_themes, RESTPreloader $rest_preloader, DependencySupport $dependency_support, LoadingError $loading_error, SiteHealth $site_health ) {
		$this->google_fonts       = $google_fonts;
		$this->reader_themes      = $reader_themes;
		$this->rest_preloader     = $rest_preloader;
		$this->dependency_support = $dependency_support;
		$this->loading_error      = $loading_error;
		$this->site_health        = $site_health;
	}

	/**
	 * Adds hooks.
	 */
	public function register() {
		add_action( 'admin_menu', [ $this, 'add_menu_items' ], 9 );

		$plugin_file = preg_replace( '#.+/(?=.+?/.+?)#', '', AMP__FILE__ );
		add_filter( "plugin_action_links_{$plugin_file}", [ $this, 'add_plugin_action_links' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	/**
	 * Add plugin action links.
	 *
	 * @param array $links Links.
	 * @return array Modified links.
	 */
	public function add_plugin_action_links( $links ) {
		return array_merge(
			[
				'settings' => sprintf(
					'<a href="%1$s">%2$s</a>',
					esc_url( add_query_arg( 'page', $this->get_menu_slug(), admin_url( 'admin.php' ) ) ),
					esc_html__( 'Settings', 'amp' )
				),
			],
			$links
		);
	}

	/**
	 * Returns the slug for the settings page.
	 *
	 * @return string
	 */
	public function get_menu_slug() {
		return AMP_Options_Manager::OPTION_NAME;
	}

	/**
	 * Add menu.
	 */
	public function add_menu_items() {
		require_once ABSPATH . '/wp-admin/includes/plugin.php';

		/*
		 * Note that the admin items for Validated URLs and Validation Errors will also be placed under this admin menu
		 * page when the current user can manage_options.
		 */
		add_menu_page(
			esc_html__( 'AMP Settings', 'amp' ),
			esc_html__( 'AMP', 'amp' ),
			'manage_options',
			$this->get_menu_slug(),
			[ $this, 'render_screen' ],
			self::ICON_BASE64_SVG
		);

		add_submenu_page(
			$this->get_menu_slug(),
			esc_html__( 'AMP Settings', 'amp' ),
			esc_html__( 'Settings', 'amp' ),
			'manage_options',
			$this->get_menu_slug()
		);
	}

	/**
	 * Provides the settings screen handle.
	 *
	 * @return string
	 */
	public function screen_handle() {
		return sprintf( 'toplevel_page_%s', $this->get_menu_slug() );
	}

	/**
	 * Enqueues settings page assets.
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
			amp_get_asset_url( 'css/amp-settings.css' ),
			[
				$this->google_fonts->get_handle(),
				'wp-components',
			],
			AMP__VERSION
		);

		wp_styles()->add_data( self::ASSET_HANDLE, 'rtl', 'replace' );

		$theme           = wp_get_theme();
		$is_reader_theme = $this->reader_themes->theme_data_exists( get_stylesheet() );

		$amp_validated_urls_link = admin_url(
			add_query_arg(
				[ 'post_type' => AMP_Validated_URL_Post_Type::POST_TYPE_SLUG ],
				'edit.php'
			)
		);

		$js_data = [
			'AMP_COMPATIBLE_THEMES_URL'          => current_user_can( 'install_themes' ) ? admin_url( '/theme-install.php?browse=amp-compatible' ) : 'https://amp-wp.org/ecosystem/themes/',
			'AMP_COMPATIBLE_PLUGINS_URL'         => current_user_can( 'install_plugins' ) ? admin_url( '/plugin-install.php?tab=amp-compatible' ) : 'https://amp-wp.org/ecosystem/plugins/',
			'AMP_QUERY_VAR'                      => amp_get_slug(),
			'AMP_SCAN_IF_STALE'                  => QueryVar::AMP_SCAN_IF_STALE,
			'CURRENT_THEME'                      => [
				'name'            => $theme->get( 'Name' ),
				'description'     => $theme->get( 'Description' ),
				'is_reader_theme' => $is_reader_theme,
				'screenshot'      => $theme->get_screenshot() ?: null,
				'url'             => $theme->get( 'ThemeURI' ),
			],
			'HAS_DEPENDENCY_SUPPORT'             => $this->dependency_support->has_support(),
			'OPTIONS_REST_PATH'                  => '/amp/v1/options',
			'READER_THEMES_REST_PATH'            => '/amp/v1/reader-themes',
			'SCANNABLE_URLS_REST_PATH'           => '/amp/v1/scannable-urls',
			'LEGACY_THEME_SLUG'                  => ReaderThemes::DEFAULT_READER_THEME,
			'USING_FALLBACK_READER_THEME'        => $this->reader_themes->using_fallback_theme(),
			'UPDATES_NONCE'                      => current_user_can( 'install_themes' ) ? wp_create_nonce( 'updates' ) : '',
			'USER_FIELD_DEVELOPER_TOOLS_ENABLED' => UserAccess::USER_FIELD_DEVELOPER_TOOLS_ENABLED,
			'USER_FIELD_REVIEW_PANEL_DISMISSED_FOR_TEMPLATE_MODE' => UserRESTEndpointExtension::USER_FIELD_REVIEW_PANEL_DISMISSED_FOR_TEMPLATE_MODE,
			'USERS_RESOURCE_REST_PATH'           => '/wp/v2/users',
			'VALIDATE_NONCE'                     => AMP_Validation_Manager::has_cap() ? AMP_Validation_Manager::get_amp_validate_nonce() : '',
			'VALIDATED_URLS_LINK'                => $amp_validated_urls_link,
			'HAS_PAGE_CACHING'                   => $this->site_health->has_page_caching( true ),
		];

		wp_add_inline_script(
			self::ASSET_HANDLE,
			sprintf(
				'var ampSettings = %s;',
				wp_json_encode( $js_data )
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
	 * Display Settings.
	 */
	public function render_screen() {
		?>
		<div class="wrap">
			<form id="amp-settings" action="options.php" method="post">
				<?php settings_fields( $this->get_menu_slug() ); ?>
				<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
				<?php settings_errors(); ?>

				<div class="amp amp-settings">
					<div id="amp-settings-root">
						<?php $this->loading_error->render(); ?>
					</div>
				</div>
			</form>
		</div>
		<?php
	}

	/**
	 * Adds REST paths to preload.
	 */
	protected function add_preload_rest_paths() {
		$paths = [
			'/amp/v1/options',
			'/amp/v1/reader-themes',
			add_query_arg(
				'_fields',
				[ 'url', 'amp_url', 'type', 'label', 'validation_errors', 'stale' ],
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
}
