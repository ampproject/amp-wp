<?php
/**
 * Class OptionsMenu
 *
 * @package AMP
 */

namespace AmpProject\AmpWP\Admin;

use AMP_Analytics_Options_Submenu;
use AMP_Core_Theme_Sanitizer;
use AMP_Options_Manager;
use AMP_Reader_Themes;
use AMP_Theme_Support;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Option;

/**
 * OptionsMenu class.
 */
class OptionsMenu implements Service, Registerable {
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
	 * OptionsMenu constructor.
	 *
	 * @param GoogleFonts $google_fonts An instance of the GoogleFonts service.
	 */
	public function __construct( GoogleFonts $google_fonts ) {
		$this->google_fonts = $google_fonts;
	}

	/**
	 * Registers AMP settings.
	 */
	public function register() {
		if ( ! is_admin() ) {
			return;
		}

		/**
		 * Filter whether to enable the AMP settings.
		 *
		 * @since 0.5
		 * @param bool $enable Whether to enable the AMP settings. Default true.
		 */
		$short_circuit = apply_filters( 'amp_options_menu_is_enabled', true );

		if ( true !== $short_circuit ) {
			return;
		}

		$this->add_hooks();
	}

	/**
	 * Adds hooks.
	 */
	public function add_hooks() {
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
					__( 'Settings', 'amp' )
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
			__( 'AMP Options', 'amp' ),
			__( 'AMP', 'amp' ),
			'manage_options',
			AMP_Options_Manager::OPTION_NAME,
			[ $this, 'render_screen' ],
			self::ICON_BASE64_SVG
		);

		add_submenu_page(
			AMP_Options_Manager::OPTION_NAME,
			__( 'AMP Settings', 'amp' ),
			__( 'General', 'amp' ),
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
	 * Display Settings.
	 *
	 * @since 0.6
	 */
	public function render_screen() {
		/* translators: %s: URL to the ecosystem page. */
		$ecosystem_description = sprintf( __( 'For a list of themes and plugins that are known to be AMP compatible, please see the <a href="%s">ecosystem page</a>.', 'amp' ), esc_url( 'https://amp-wp.org/ecosystem/' ) );
		$reader_mode_support   = __( 'Your theme indicates it works best in <strong>Reader mode.</strong>', 'amp' );
		$builtin_support       = in_array( get_template(), AMP_Core_Theme_Sanitizer::get_supported_themes(), true );
		$plugin_configured      = AMP_Options_Manager::get_option( Option::PLUGIN_CONFIGURED );

		if ( ! empty( $_GET['settings-updated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			AMP_Options_Manager::check_supported_post_type_update_errors();
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<?php settings_errors(); ?>
			<?php if ( AMP_Theme_Support::READER_MODE_SLUG === AMP_Theme_Support::get_support_mode() ) : ?>
				<?php if ( AMP_Theme_Support::STANDARD_MODE_SLUG === AMP_Theme_Support::get_support_mode_added_via_theme() ) : ?>
					<div class="notice notice-success notice-alt inline">
						<p><?php esc_html_e( 'Your active theme is known to work well in standard mode.', 'amp' ); ?></p>
					</div>
				<?php elseif ( $builtin_support || AMP_Theme_Support::TRANSITIONAL_MODE_SLUG === AMP_Theme_Support::get_support_mode_added_via_theme() ) : ?>
					<div class="notice notice-success notice-alt inline">
						<p><?php esc_html_e( 'Your active theme is known to work well in standard or transitional mode.', 'amp' ); ?></p>
					</div>
				<?php endif; ?>
			<?php elseif ( AMP_Theme_Support::supports_reader_mode() ) : ?>
				<div class="notice notice-success notice-alt inline">
					<p><?php echo wp_kses( $reader_mode_support, [ 'strong' => [] ] ); ?></p>
				</div>
			<?php endif; ?>

			<?php if ( ! AMP_Theme_Support::get_support_mode_added_via_theme() && ! AMP_Theme_Support::supports_reader_mode() && ! $builtin_support ) : ?>
				<p>
					<?php echo wp_kses_post( $ecosystem_description ); ?>
				</p>
			<?php endif; ?>
			<div class="amp">
				<div class="welcome">
					<div class="selectable selectable--left">
						<div class="welcome__illustration">
							<svg width="154" height="135" viewBox="0 0 154 135" fill="none" xmlns="http://www.w3.org/2000/svg">
								<rect x="28.8652" y="35.8047" width="77.3354" height="77.3354" rx="19" fill="white" stroke="#2459E7" strokeWidth="2" />
								<rect x="42.542" y="49.75" width="5.96464" height="49.7702" rx="2.98232" fill="white" stroke="#2459E7" strokeWidth="2" />
								<circle cx="45.5244" cy="64.2988" r="6.09961" fill="white" stroke="#2459E7" strokeWidth="2" />
								<rect x="70.6704" y="99.5117" width="5.96464" height="49.7702" rx="2.98232" transform="rotate(-180 70.6704 99.5117)" fill="white" stroke="#2459E7" strokeWidth="2" />
								<rect x="92.8345" y="99.5117" width="5.96464" height="49.7702" rx="2.98232" transform="rotate(-180 92.8345 99.5117)" fill="white" stroke="#2459E7" strokeWidth="2" />
								<circle cx="89.8516" cy="68.4355" r="6.09961" transform="rotate(-180 89.8516 68.4355)" fill="white" stroke="#2459E7" strokeWidth="2" />
								<circle cx="67.5327" cy="81.5723" r="6.09961" transform="rotate(-180 67.5327 81.5723)" fill="white" stroke="#2459E7" strokeWidth="2" />
								<path d="M95.3496 28.9929C97.5189 23.8663 105.289 14.2044 119.015 16.5696" stroke="#2459E7" strokeWidth="2" strokeLinecap="round" />
								<path d="M80.2668 26.0743C82.2095 22.1887 84.3994 13.8877 77.6177 11.7695" stroke="#2459E7" strokeWidth="2" strokeLinecap="round" />
								<path d="M140.528 26.6495C140.835 26.9335 141.287 26.9961 141.66 26.8062C142.033 26.6164 142.248 26.2144 142.2 25.7987L141.337 18.4382L147.49 14.938C147.855 14.7307 148.051 14.3181 147.982 13.9046C147.914 13.491 147.594 13.1643 147.182 13.0863L140.054 11.7358L138.754 5.1671C138.673 4.76193 138.352 4.44775 137.946 4.37632C137.539 4.3049 137.13 4.49093 136.916 4.84456L133.333 10.7815L126.416 9.73864C125.999 9.67576 125.587 9.88135 125.387 10.2526C125.187 10.6238 125.241 11.0808 125.522 11.3949L130.464 16.9086L127.441 23.3886C127.261 23.7737 127.345 24.2302 127.649 24.527C127.953 24.8238 128.411 24.896 128.792 24.7072L135.059 21.5972L140.528 26.6495Z" stroke="#2459E7" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
								<path d="M113.025 45.2951C116.002 42.2624 124.049 37.8452 132.429 44.4385" stroke="#2459E7" strokeWidth="2" strokeLinecap="round" />
								<path d="M37.8788 118.656C36.2358 122.01 33.0868 129.691 33.6344 133.579" stroke="#2459E7" strokeWidth="2" strokeLinecap="round" />
								<path d="M26.6027 114.924C22.749 117.612 14.5433 123.427 12.5492 125.19" stroke="#2459E7" strokeWidth="2" strokeLinecap="round" />
								<path d="M21.5176 102.637C16.7026 104.37 6.37452 107.987 3.58154 108.59" stroke="#2459E7" strokeWidth="2" strokeLinecap="round" />
								<circle cx="3.58159" cy="131.11" r="3.01225" fill="#2459E7" />
							</svg>
						</div>

						<div class="welcome__body">
							<h2>
								<?php if ( $plugin_configured ) : ?>
									<?php esc_html_e( 'AMP Settings Configured', 'amp' ); ?>

								<?php else : ?>
									<?php esc_html_e( 'Configure AMP', 'amp' ); ?>

								<?php endif; ?>
							</h2>
							<p>
								<?php esc_html_e( 'The AMP plugin can guide you through choosing the best settings accordint to your theme, plugins, and technical capabilities.', 'amp' ); ?>
							</p>
						</div>
					</div>
				</div>
				<div id="amp-settings-root"></div>
			</div>
		</div>
		<?php
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
			[ $this->google_fonts->get_handle() ],
			AMP__VERSION
		);

		wp_styles()->add_data( self::ASSET_HANDLE, 'rtl', 'replace' );

		$theme           = wp_get_theme();
		$is_reader_theme = in_array( get_stylesheet(), wp_list_pluck( ( new AMP_Reader_Themes() )->get_themes(), 'slug' ), true );

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
}
