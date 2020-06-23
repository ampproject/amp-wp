<?php
/**
 * AMP setup wizard page.
 *
 * @package AMP
 * @since 1.6.0
 */

use AmpProject\AmpWP\Admin\DevToolsUserAccess;

/**
 * AMP setup wizard submenu page class.
 *
 * @since 1.6.0
 */
final class AMP_Setup_Wizard_Submenu_Page {
	/**
	 * Handle for JS file.
	 *
	 * @since 1.6.0
	 *
	 * @var string
	 */
	const ASSET_HANDLE = 'amp-setup';

	/**
	 * HTML ID for the app root element.
	 *
	 * @since 1.6.0
	 *
	 * @var string
	 */
	const APP_ROOT_ID = 'amp-setup';

	/**
	 * Sets up hooks.
	 *
	 * @since 1.6.0
	 */
	public function init() {
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

		// <head> tag was opened prior to this action and hasn't been closed.
		?>
		</head>
		<body>
			<?php // The admin footer template closes three divs. ?>
			<div>
			<div>
			<div>
			<div id="<?php echo esc_attr( static::APP_ROOT_ID ); ?>"></div>
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
		return sprintf( 'amp_page_%s', AMP_Setup_Wizard_Submenu::SCREEN_ID );
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

		$fonts_handle = self::ASSET_HANDLE . '-google-fonts';

		// PHPCS ignore reason: WP will strip multiple `family` args from the Google fonts URL while adding the version string,
		// so we need to avoid specifying a version at all.
		wp_enqueue_style( // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
			$fonts_handle,
			'https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;700&family=Poppins:wght@400;600&display=swap',
			[],
			null
		);

		wp_enqueue_style(
			self::ASSET_HANDLE,
			amp_get_asset_url( 'css/amp-setup.css' ),
			[ $fonts_handle ],
			AMP__VERSION
		);

		wp_styles()->add_data( self::ASSET_HANDLE, 'rtl', 'replace' );

		$setup_wizard_data = [
			'AMP_OPTIONS_KEY'                    => AMP_Options_Manager::OPTION_NAME,
			'APP_ROOT_ID'                        => self::APP_ROOT_ID,
			'EXIT_LINK'                          => admin_url( 'admin.php?page=' . AMP_Options_Manager::OPTION_NAME ),
			'OPTIONS_REST_ENDPOINT'              => rest_url( 'amp/v1/options' ),
			'READER_THEMES_REST_ENDPOINT'        => rest_url( 'amp/v1/reader-themes' ),
			'UPDATES_NONCE'                      => wp_create_nonce( 'updates' ),
			'USER_FIELD_DEVELOPER_TOOLS_ENABLED' => DevToolsUserAccess::USER_FIELD_DEVELOPER_TOOLS_ENABLED,
			'USER_REST_ENDPOINT'                 => rest_url( 'wp/v2/users/me' ),
		];

		wp_add_inline_script(
			self::ASSET_HANDLE,
			sprintf(
				'var ampSetup = %s;',
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
