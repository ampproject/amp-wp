<?php
/**
 * Class OptionsMenu
 *
 * @package Ampproject\Ampwp
 */

namespace AmpProject\AmpWP\Admin;

use AMP_Core_Theme_Sanitizer;
use AMP_Options_Manager;
use AMP_Theme_Support;
use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Option;

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
	 * @param GoogleFonts   $google_fonts An instance of the GoogleFonts service.
	 * @param ReaderThemes  $reader_themes An instance of the ReaderThemes class.
	 * @param RESTPreloader $rest_preloader An instance of the RESTPreloader class.
	 */
	public function __construct( GoogleFonts $google_fonts, ReaderThemes $reader_themes, RESTPreloader $rest_preloader ) {
		$this->google_fonts   = $google_fonts;
		$this->reader_themes  = $reader_themes;
		$this->rest_preloader = $rest_preloader;
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

		$js_data = [
			'CURRENT_THEME'               => [
				'name'            => $theme->get( 'Name' ),
				'description'     => $theme->get( 'Description' ),
				'is_reader_theme' => $is_reader_theme,
				'screenshot'      => $theme->get_screenshot() ?: null,
				'url'             => $theme->get( 'ThemeURI' ),
			],
			'OPTIONS_REST_PATH'           => '/amp/v1/options',
			'READER_THEMES_REST_PATH'     => '/amp/v1/reader-themes',
			'IS_CORE_THEME'               => in_array(
				get_stylesheet(),
				AMP_Core_Theme_Sanitizer::get_supported_themes(),
				true
			),
			'LEGACY_THEME_SLUG'           => ReaderThemes::DEFAULT_READER_THEME,
			'USING_FALLBACK_READER_THEME' => $this->reader_themes->using_fallback_theme(),
			'THEME_SUPPORT_ARGS'          => AMP_Theme_Support::get_theme_support_args(),
			'THEME_SUPPORTS_READER_MODE'  => AMP_Theme_Support::supports_reader_mode(),
			'UPDATES_NONCE'               => wp_create_nonce( 'updates' ),
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
					<div id="amp-settings-root"></div>
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
			'/wp/v2/settings',
		];

		foreach ( $paths as $path ) {
			$this->rest_preloader->add_preloaded_path( $path );
		}
	}
}
