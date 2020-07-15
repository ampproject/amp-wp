<?php
/**
 * Class OptionsMenu
 *
 * @package Ampproject\Ampwp
 */

namespace AmpProject\AmpWP\Admin;

use AMP_Analytics_Options_Submenu;
use AMP_Core_Theme_Sanitizer;
use AMP_Options_Manager;
use AMP_Theme_Support;
use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Option;

/**
 * OptionsMenu class.
 */
class OptionsMenu implements Conditional, Service, Registerable {
	/**
	 * Handle for JS file.
	 *
	 * @since 1.6.0
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
	 * @param GoogleFonts  $google_fonts An instance of the GoogleFonts service.
	 * @param ReaderThemes $reader_themes An instance of the ReaderThemes class.
	 */
	public function __construct( GoogleFonts $google_fonts, ReaderThemes $reader_themes ) {
		$this->google_fonts  = $google_fonts;
		$this->reader_themes = $reader_themes;
	}

	/**
	 * Adds hooks.
	 */
	public function register() {
		add_action( 'admin_post_amp_analytics_options', 'AMP_Options_Manager::handle_analytics_submit' );
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
					esc_url( add_query_arg( 'page', AMP_Options_Manager::OPTION_NAME, admin_url( 'admin.php' ) ) ),
					esc_html__( 'Settings', 'amp' )
				),
			],
			$links
		);
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
			AMP_Options_Manager::OPTION_NAME,
			[ $this, 'render_screen' ],
			self::ICON_BASE64_SVG
		);

		add_submenu_page(
			AMP_Options_Manager::OPTION_NAME,
			esc_html__( 'AMP Settings', 'amp' ),
			esc_html__( 'Settings', 'amp' ),
			'manage_options',
			AMP_Options_Manager::OPTION_NAME
		);

		/**
		 * This fires when settings fields for the AMP Options menu need to be registered.
		 *
		 * This action is intended for internal use only, not to be used by other plugins.
		 *
		 * @internal
		 */
		do_action( 'amp_options_menu_items' );

		$submenus = [
			new AMP_Analytics_Options_Submenu( AMP_Options_Manager::OPTION_NAME ),
		];

		// Create submenu items and calls on the Submenu Page object to render the actual contents of the page.
		foreach ( $submenus as $submenu ) {
			$submenu->init();
		}
	}

	/**
	 * Provides the settings screen handle.
	 *
	 * @return string
	 */
	public function screen_handle() {
		return sprintf( 'toplevel_page_%s', AMP_Options_Manager::OPTION_NAME );
	}

	/**
	 * Enqueues settings page assets.
	 *
	 * @since 1.6.0
	 *
	 * @param string $hook_suffix The current admin page.
	 */
	public function enqueue_assets( $hook_suffix ) {
		if ( $this->screen_handle() !== $hook_suffix ) {
			return;
		}

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
		$is_reader_theme = in_array( get_stylesheet(), wp_list_pluck( $this->reader_themes->get_themes(), 'slug' ), true );

		$js_data = [
			'CURRENT_THEME'                      => [
				'name'            => $theme->get( 'Name' ),
				'description'     => $theme->get( 'Description' ),
				'is_reader_theme' => $is_reader_theme,
				'screenshot'      => $theme->get_screenshot(),
				'url'             => $theme->get( 'ThemeURI' ),
			],
			'OPTIONS_REST_ENDPOINT'              => rest_url( 'amp/v1/options' ),
			'READER_THEMES_REST_ENDPOINT'        => rest_url( 'amp/v1/reader-themes' ),
			'IS_CORE_THEME'                      => in_array(
				get_stylesheet(),
				AMP_Core_Theme_Sanitizer::get_supported_themes(),
				true
			),
			'THEME_SUPPORT_ARGS'                 => AMP_Theme_Support::get_theme_support_args(),
			'THEME_SUPPORTS_READER_MODE'         => AMP_Theme_Support::supports_reader_mode(),
			'UPDATES_NONCE'                      => wp_create_nonce( 'updates' ),
			'USER_FIELD_DEVELOPER_TOOLS_ENABLED' => DevToolsUserAccess::USER_FIELD_DEVELOPER_TOOLS_ENABLED,
			'USER_REST_ENDPOINT'                 => rest_url( 'wp/v2/users/me' ),
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
	}

	/**
	 * Display Settings.
	 */
	public function render_screen() {
		/* translators: %s: URL to the ecosystem page. */
		$plugin_configured = AMP_Options_Manager::get_option( Option::PLUGIN_CONFIGURED );

		?>
		<div class="wrap">
			<form id="amp-settings" action="options.php" method="post">
				<?php settings_fields( AMP_Options_Manager::OPTION_NAME ); ?>
				<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
				<?php settings_errors(); ?>

				<div class="amp amp-settings">
					<div class="settings-welcome">
						<div class="selectable selectable--left">
							<div class="settings-welcome__illustration">
								<svg width="62" height="51" viewBox="0 0 62 51" fill="none" xmlns="http://www.w3.org/2000/svg">
									<g clip-path="url(#welcome-svg-clip)">
										<path d="M19.0226 3.89844H39.5226C45.0226 3.89844 49.4226 8.29844 49.4226 13.7984V34.2984C49.4226 39.7984 45.0226 44.1984 39.5226 44.1984H19.0226C13.5226 44.1984 9.12256 39.7984 9.12256 34.2984V13.7984C9.12256 8.29844 13.5226 3.89844 19.0226 3.89844Z" fill="white" stroke="#2459E7" stroke-width="2"/>
										<path d="M17.8227 11.1992C18.7227 11.1992 19.4227 11.8992 19.4227 12.7992V35.6992C19.4227 36.5992 18.7227 37.2992 17.8227 37.2992C16.9227 37.2992 16.2227 36.5992 16.2227 35.6992V12.6992C16.2227 11.7992 16.9227 11.1992 17.8227 11.1992Z" fill="white" stroke="#2459E7" stroke-width="2"/>
										<path d="M17.8228 21.9C19.5901 21.9 21.0228 20.4673 21.0228 18.7C21.0228 16.9327 19.5901 15.5 17.8228 15.5C16.0555 15.5 14.6228 16.9327 14.6228 18.7C14.6228 20.4673 16.0555 21.9 17.8228 21.9Z" fill="white" stroke="#2459E7" stroke-width="2"/>
										<path d="M29.3227 37.0977C28.4227 37.0977 27.7227 36.3977 27.7227 35.4977V12.6977C27.7227 11.7977 28.4227 11.0977 29.3227 11.0977C30.2227 11.0977 30.9227 11.7977 30.9227 12.6977V35.5977C30.9227 36.3977 30.2227 37.0977 29.3227 37.0977Z" fill="white" stroke="#2459E7" stroke-width="2"/>
										<path d="M40.9225 37.0977C40.0225 37.0977 39.3225 36.3977 39.3225 35.4977V12.6977C39.3225 11.7977 40.0225 11.0977 40.9225 11.0977C41.8225 11.0977 42.5225 11.7977 42.5225 12.6977V35.5977C42.5225 36.3977 41.8225 37.0977 40.9225 37.0977Z" fill="white" stroke="#2459E7" stroke-width="2"/>
										<path d="M40.9227 24.0992C42.69 24.0992 44.1227 22.6665 44.1227 20.8992C44.1227 19.1319 42.69 17.6992 40.9227 17.6992C39.1553 17.6992 37.7227 19.1319 37.7227 20.8992C37.7227 22.6665 39.1553 24.0992 40.9227 24.0992Z" fill="white" stroke="#2459E7" stroke-width="2"/>
										<path d="M29.2227 30.9977C30.99 30.9977 32.4227 29.565 32.4227 27.7977C32.4227 26.0303 30.99 24.5977 29.2227 24.5977C27.4554 24.5977 26.0227 26.0303 26.0227 27.7977C26.0227 29.565 27.4554 30.9977 29.2227 30.9977Z" fill="white" stroke="#2459E7" stroke-width="2"/>
										<path d="M47.3225 5.19784C47.9225 3.69784 49.9225 0.797843 53.4225 1.49784" stroke="#2459E7" stroke-width="2" stroke-linecap="round"/>
										<path d="M50.5227 7.19675C51.7227 6.69675 54.5227 6.29675 56.2227 9.09675" stroke="#2459E7" stroke-width="2" stroke-linecap="round"/>
										<path d="M12.4225 44.7969C11.9225 45.7969 10.9225 48.1969 11.1225 49.3969" stroke="#2459E7" stroke-width="2" stroke-linecap="round"/>
										<path d="M8.92266 43.6992C8.42266 44.0992 7.52266 44.6992 6.72266 45.1992" stroke="#2459E7" stroke-width="2" stroke-linecap="round"/>
										<path d="M7.42261 39.8984C5.92261 40.4984 2.82261 41.5984 1.92261 41.7984" stroke="#2459E7" stroke-width="2" stroke-linecap="round"/>
										<path d="M3.92251 48.8992C4.80617 48.8992 5.52251 48.1829 5.52251 47.2992C5.52251 46.4156 4.80617 45.6992 3.92251 45.6992C3.03885 45.6992 2.32251 46.4156 2.32251 47.2992C2.32251 48.1829 3.03885 48.8992 3.92251 48.8992Z" fill="#2459E7"/>
										<path d="M60.1227 12.7C61.0064 12.7 61.7227 11.9837 61.7227 11.1C61.7227 10.2163 61.0064 9.5 60.1227 9.5C59.2391 9.5 58.5227 10.2163 58.5227 11.1C58.5227 11.9837 59.2391 12.7 60.1227 12.7Z" fill="#2459E7"/>
									</g>
									<defs>
										<clipPath id="welcome-svg-clip">
											<rect width="60.8" height="50" fill="white" transform="translate(0.922607 0.398438)"/>
										</clipPath>
									</defs>
								</svg>


							</div>

							<div class="settings-welcome__body">
								<h2>
									<?php if ( $plugin_configured ) : ?>
										<?php esc_html_e( 'AMP Settings Configured', 'amp' ); ?>

										<svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
											<mask id="check-circle-mask" mask-type="alpha" maskUnits="userSpaceOnUse" x="2" y="2" width="21" height="21">
												<path fill-rule="evenodd" clip-rule="evenodd" d="M12.7537 2.60938C7.23366 2.60938 2.75366 7.08938 2.75366 12.6094C2.75366 18.1294 7.23366 22.6094 12.7537 22.6094C18.2737 22.6094 22.7537 18.1294 22.7537 12.6094C22.7537 7.08938 18.2737 2.60938 12.7537 2.60938ZM12.7537 20.6094C8.34366 20.6094 4.75366 17.0194 4.75366 12.6094C4.75366 8.19938 8.34366 4.60938 12.7537 4.60938C17.1637 4.60938 20.7537 8.19938 20.7537 12.6094C20.7537 17.0194 17.1637 20.6094 12.7537 20.6094ZM10.7537 14.7794L17.3437 8.18937L18.7537 9.60938L10.7537 17.6094L6.75366 13.6094L8.16366 12.1994L10.7537 14.7794Z" fill="white"/>
											</mask>
											<g mask="url(#check-circle-mask)">
												<rect x="0.753662" y="0.609375" width="24" height="24" fill="#2459E7"/>
											</g>
										</svg>

									<?php else : ?>
										<?php esc_html_e( 'Configure AMP', 'amp' ); ?>

									<?php endif; ?>

								</h2>
								<p>
									<?php esc_html_e( 'The AMP configuration wizard can help you choose the best settings for your theme, plugins, and technical capabilities.', 'amp' ); ?>
								</p>

								<?php if ( amp_should_use_new_onboarding() ) : ?>
									<a class="components-button is-primary settings-welcome__button" href="<?php menu_page_url( OnboardingWizardSubmenu::SCREEN_ID ); ?>">
										<?php if ( $plugin_configured ) : ?>
											<?php esc_html_e( 'Reopen Wizard', 'amp' ); ?>

										<?php else : ?>
											<?php esc_html_e( 'Open Wizard', 'amp' ); ?>

										<?php endif; ?>
									</a>
									<a class="components-button is-link" href="<?php echo esc_url( amp_get_customizer_url() ); ?>" target="_blank">
										<?php esc_html_e( 'Visit Customizer', 'amp' ); ?>
									</a>
								<?php endif; ?>
							</div>
						</div>
					</div>
					<div id="amp-settings-root"></div>
				</div>
			</form>
		</div>
		<?php
	}
}
