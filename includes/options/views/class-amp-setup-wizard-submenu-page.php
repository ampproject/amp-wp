<?php
/**
 * AMP setup wizard page.
 *
 * @package AMP
 * @since @todo NEW_ONBOARDING_RELEASE_VERSION
 */

/**
 * AMP setup wizard submenu page class.
 *
 * @since @todo NEW_ONBOARDING_RELEASE_VERSION
 */
final class AMP_Setup_Wizard_Submenu_Page {
	/**
	 * Handle for JS file.
	 *
	 * @var string
	 */
	const JS_HANDLE = 'amp-setup';

	/**
	 * HTML ID for the app root element.
	 *
	 * @var string
	 */
	const APP_ROOT_ID = 'amp-setup';

	/**
	 * Sets up hooks.
	 */
	public function init() {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	/**
	 * Renders the setup screen markup.
	 */
	public function render() {
		?>
			<div id="<?php echo esc_attr( static::APP_ROOT_ID ); ?>"></div>
		<?php
	}

	/**
	 * Provides the setup screen handle.
	 *
	 * @return string
	 */
	public function screen_handle() {
		return sprintf( 'amp_page_%s', AMP_Setup_Wizard_Submenu::SCREEN_ID );
	}

	/**
	 * Enqueues setup assets.
	 *
	 * @param string $hook_suffix The current admin page.
	 */
	public function enqueue_assets( $hook_suffix ) {
		if ( $this->screen_handle() !== $hook_suffix ) {
			return;
		}

		$asset_file   = AMP__DIR__ . '/assets/js/' . self::JS_HANDLE . '.asset.php';
		$asset        = require $asset_file;
		$dependencies = $asset['dependencies'];
		$version      = $asset['version'];

		wp_enqueue_script(
			self::JS_HANDLE,
			amp_get_asset_url( 'js/' . self::JS_HANDLE . '.js' ),
			$dependencies,
			$version,
			true
		);

		wp_add_inline_script(
			self::JS_HANDLE,
			sprintf(
				'var ampSetup = %s;',
				wp_json_encode(
					[
						'APP_ROOT_ID' => self::APP_ROOT_ID,
					]
				)
			),
			'before'
		);

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( self::JS_HANDLE, 'amp' );
		} elseif ( function_exists( 'wp_get_jed_locale_data' ) || function_exists( 'gutenberg_get_jed_locale_data' ) ) {
			$locale_data  = function_exists( 'wp_get_jed_locale_data' ) ? wp_get_jed_locale_data( 'amp' ) : gutenberg_get_jed_locale_data( 'amp' );
			$translations = wp_json_encode( $locale_data );

			wp_add_inline_script(
				self::JS_HANDLE,
				'wp.i18n.setLocaleData( ' . $translations . ', "amp" );',
				'after'
			);
		}
	}
}
