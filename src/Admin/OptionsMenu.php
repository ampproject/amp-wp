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
use AMP_Post_Type_Support;
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
		$short_circuit = apply_filters( 'amp_options_menu_is_enabled', true );

		if ( true !== $short_circuit ) {
			return false;
		}

		return true;
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

		add_settings_section(
			'general',
			false,
			'__return_false',
			AMP_Options_Manager::OPTION_NAME
		);

		add_settings_section(
			'validation',
			false,
			'__return_false',
			AMP_Options_Manager::OPTION_NAME
		);

		add_settings_field(
			Option::SUPPORTED_TEMPLATES,
			__( 'Supported Templates', 'amp' ),
			[ $this, 'render_supported_templates' ],
			AMP_Options_Manager::OPTION_NAME,
			'general',
			[
				'class' => 'amp-template-support-field',
			]
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
			[ $this->google_fonts->get_handle(), 'wp-components' ],
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
				array_diff( AMP_Core_Theme_Sanitizer::get_supported_themes(), [ 'twentyten' ] ),
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

		if ( ! empty( $_GET['settings-updated'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			AMP_Options_Manager::check_supported_post_type_update_errors();
		}
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
							<svg width="154" height="135" viewBox="0 0 154 135" fill="none" xmlns="http://www.w3.org/2000/svg">
								<rect x="28.9246" y="35.6406" width="77.3354" height="77.3354" rx="19" fill="white" stroke="#2459E7" stroke-width="2"/>
								<rect x="42.6016" y="49.5859" width="5.96464" height="49.7702" rx="2.98232" fill="white" stroke="#2459E7" stroke-width="2"/>
								<circle cx="45.5837" cy="64.1348" r="6.09961" fill="white" stroke="#2459E7" stroke-width="2"/>
								<rect x="70.73" y="99.3477" width="5.96464" height="49.7702" rx="2.98232" transform="rotate(-180 70.73 99.3477)" fill="white" stroke="#2459E7" stroke-width="2"/>
								<rect x="92.8936" y="99.3477" width="5.96464" height="49.7702" rx="2.98232" transform="rotate(-180 92.8936 99.3477)" fill="white" stroke="#2459E7" stroke-width="2"/>
								<circle cx="89.9111" cy="68.2715" r="6.09961" transform="rotate(-180 89.9111 68.2715)" fill="white" stroke="#2459E7" stroke-width="2"/>
								<circle cx="67.592" cy="81.4082" r="6.09961" transform="rotate(-180 67.592 81.4082)" fill="white" stroke="#2459E7" stroke-width="2"/>
								<path d="M95.4089 28.8288C97.5783 23.7022 105.348 14.0404 119.074 16.4055" stroke="#2459E7" stroke-width="2" stroke-linecap="round"/>
								<path d="M80.3262 25.9103C82.2688 22.0246 84.4587 13.7237 77.677 11.6055" stroke="#2459E7" stroke-width="2" stroke-linecap="round"/>
								<path d="M140.587 26.4855C140.894 26.7695 141.346 26.832 141.719 26.6422C142.092 26.4523 142.308 26.0503 142.259 25.6346L141.397 18.2741L147.55 14.7739C147.914 14.5666 148.111 14.154 148.042 13.7405C147.973 13.3269 147.653 13.0002 147.242 12.9222L140.113 11.5717L138.813 5.00304C138.733 4.59787 138.412 4.28369 138.005 4.21226C137.598 4.14084 137.189 4.32687 136.976 4.68049L133.393 10.6174L126.476 9.57457C126.058 9.51169 125.647 9.71729 125.446 10.0885C125.246 10.4597 125.3 10.9167 125.582 11.2308L130.523 16.7446L127.5 23.2246C127.321 23.6096 127.404 24.0661 127.708 24.3629C128.012 24.6597 128.471 24.732 128.851 24.5431L135.118 21.4331L140.587 26.4855Z" stroke="#2459E7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								<path d="M113.085 45.131C116.061 42.0983 124.109 37.6812 132.488 44.2744" stroke="#2459E7" stroke-width="2" stroke-linecap="round"/>
								<path d="M37.9379 118.492C36.2949 121.846 33.1459 129.526 33.6935 133.415" stroke="#2459E7" stroke-width="2" stroke-linecap="round"/>
								<path d="M26.6622 114.76C22.8086 117.447 14.6029 123.263 12.6088 125.026" stroke="#2459E7" stroke-width="2" stroke-linecap="round"/>
								<path d="M21.5767 102.473C16.7617 104.206 6.4336 107.823 3.64062 108.426" stroke="#2459E7" stroke-width="2" stroke-linecap="round"/>
								<circle cx="3.64091" cy="130.946" r="3.01225" fill="#2459E7"/>
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

								<a class="components-button is-primary settings-welcome__button" href="<?php menu_page_url( OnboardingWizardSubmenu::SCREEN_ID ); ?>">
									<?php if ( $plugin_configured ) : ?>
										<?php esc_html_e( 'Reopen Wizard', 'amp' ); ?>

									<?php else : ?>
										<?php esc_html_e( 'Open Wizard', 'amp' ); ?>

									<?php endif; ?>
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">
										<path d="M43.16 10.18c-0.881-0.881-2.322-0.881-3.203 0s-0.881 2.322 0 3.203l16.335 16.335h-54.051c-1.281 0-2.242 1.041-2.242 2.242 0 1.281 0.961 2.322 2.242 2.322h54.051l-16.415 16.335c-0.881 0.881-0.881 2.322 0 3.203s2.322 0.881 3.203 0l20.259-20.259c0.881-0.881 0.881-2.322 0-3.203l-20.179-20.179z" />
									</svg>
								</a>
							</div>
						</div>
					</div>
					<div id="amp-settings-root"></div>
					<div id="amp-settings-sections" style="display: none !important;">
						<?php do_settings_sections( AMP_Options_Manager::OPTION_NAME ); ?>
					</div>
				</div>
			</form>
		</div>
		<?php
	}

	/**
	 * Supported templates section renderer.
	 */
	public function render_supported_templates() {
		$theme_support_args = AMP_Theme_Support::get_theme_support_args();

		?>

		<fieldset id="supported_post_types_fieldset" class="hidden">
			<?php
			$element_name         = AMP_Options_Manager::OPTION_NAME . '[supported_post_types][]';
			$supported_post_types = AMP_Options_Manager::get_option( Option::SUPPORTED_POST_TYPES );
			?>
			<h4 class="title"><?php esc_html_e( 'Content Types', 'amp' ); ?></h4>
			<p>
				<?php esc_html_e( 'The following content types will be available as AMP:', 'amp' ); ?>
			</p>
			<ul>
			<?php foreach ( array_map( 'get_post_type_object', AMP_Post_Type_Support::get_eligible_post_types() ) as $post_type ) : ?>
				<?php
				$checked = (
					post_type_supports( $post_type->name, AMP_Post_Type_Support::SLUG )
					||
					in_array( $post_type->name, $supported_post_types, true )
				);
				?>
				<li>
					<?php $element_id = AMP_Options_Manager::OPTION_NAME . "-supported_post_types-{$post_type->name}"; ?>
					<input
						type="checkbox"
						id="<?php echo esc_attr( $element_id ); ?>"
						name="<?php echo esc_attr( $element_name ); ?>"
						value="<?php echo esc_attr( $post_type->name ); ?>"
						<?php checked( $checked ); ?>
						>
					<label for="<?php echo esc_attr( $element_id ); ?>">
						<?php echo esc_html( $post_type->label ); ?>
					</label>
				</li>
			<?php endforeach; ?>
			</ul>
		</fieldset>

		<?php if ( ! isset( $theme_support_args['available_callback'] ) ) : ?>
			<fieldset id="supported_templates_fieldset" class="hidden">
				<style>
					#supported_templates_fieldset ul ul {
						margin-left: 40px;
					}
				</style>
				<h4 class="title"><?php esc_html_e( 'Templates', 'amp' ); ?></h4>
				<?php
				$this->list_template_conditional_options( AMP_Theme_Support::get_supportable_templates() );
				?>
			</fieldset>
		<?php endif; ?>
		<?php
	}

	/**
	 * List template conditional options.
	 *
	 * @param array       $options Options.
	 * @param string|null $parent  Optional. ID of the parent option.
	 */
	private function list_template_conditional_options( $options, $parent = null ) {
		$element_name = AMP_Options_Manager::OPTION_NAME . '[supported_templates][]';
		?>
		<ul>
			<?php foreach ( $options as $id => $option ) : ?>
				<?php
				$element_id = AMP_Options_Manager::OPTION_NAME . '-supported-templates-' . $id;
				if ( $parent ? empty( $option['parent'] ) || $parent !== $option['parent'] : ! empty( $option['parent'] ) ) {
					continue;
				}

				// Skip showing an option if it doesn't have a label.
				if ( empty( $option['label'] ) ) {
					continue;
				}

				?>
				<li>
					<?php if ( empty( $option['immutable'] ) ) : ?>
						<input
							type="checkbox"
							id="<?php echo esc_attr( $element_id ); ?>"
							name="<?php echo esc_attr( $element_name ); ?>"
							value="<?php echo esc_attr( $id ); ?>"
							<?php checked( ! empty( $option['user_supported'] ) ); ?>
						>
					<?php else : // Persist user selection even when checkbox disabled, when selection forced by theme/filter. ?>
						<input
							type="checkbox"
							id="<?php echo esc_attr( $element_id ); ?>"
							<?php checked( ! empty( $option['supported'] ) ); ?>
							<?php disabled( true ); ?>
						>
						<?php if ( ! empty( $option['user_supported'] ) ) : ?>
							<input type="hidden" name="<?php echo esc_attr( $element_name ); ?>" value="<?php echo esc_attr( $id ); ?>">
						<?php endif; ?>
					<?php endif; ?>
					<label for="<?php echo esc_attr( $element_id ); ?>">
						<?php echo esc_html( $option['label'] ); ?>
					</label>

					<?php if ( ! empty( $option['description'] ) ) : ?>
						<span class="description">
							&mdash; <?php echo wp_kses_post( $option['description'] ); ?>
						</span>
					<?php endif; ?>

					<?php $this->list_template_conditional_options( $options, $id ); ?>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php
	}
}
