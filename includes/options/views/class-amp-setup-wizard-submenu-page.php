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

		wp_enqueue_script(
			self::JS_HANDLE,
			amp_get_asset_url( 'js/' . self::JS_HANDLE . '.js' ),
			[],
			AMP__VERSION,
			true
		);

		wp_localize_script(
			self::JS_HANDLE,
			'ampSetup',
			[
				'APP_ROOT_ID' => self::APP_ROOT_ID,
			]
		);
	}
}
