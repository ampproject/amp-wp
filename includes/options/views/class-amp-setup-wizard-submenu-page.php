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
	 * The minimum core WP version whose core assets are compatible with the setup feature.
	 *
	 * @var string
	 */
	const TARGET_WP_VERSION = '5.4';

	/**
	 * Sets up hooks.
	 */
	public function init() {
		add_action( 'admin_enqueue_scripts', [ $this, 'override_scripts' ], 99 );
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
	 * Registers single script.
	 *
	 * @param string  $handle  Script handle.
	 * @param boolean $enqueue Whether to enqueue the asset immediately. Default false.
	 */
	public function add_setup_script( $handle, $enqueue = false ) {
		$asset = $this->get_asset( $handle, AMP__DIR__ . "/assets/js/{$handle}.asset.php" );

		wp_register_script(
			$handle,
			amp_get_asset_url( "js/{$handle}.js" ),
			$asset['dependencies'],
			$asset['version'],
			true
		);

		if ( $enqueue ) {
			wp_enqueue_script( $handle );
		}
	}

	/**
	 * Returns an asset's dependencies and version.
	 *
	 * @param string $handle Asset handle.
	 * @param string $path   Path to file containing asset details.
	 *
	 * @return array Associative array containing the asset's dependencies and version.
	 */
	public function get_asset( $handle, $path = null ) {
		/**
		 * Filters AMP asset details.
		 *
		 * @param null|array $asset  Null or, to override, an array containing the asset's dependencies and version string.
		 * @param string     $handle The asset handle.
		 */
		$asset = apply_filters( 'amp_setup_asset', null, $handle );

		if ( is_null( $asset ) ) {
			$asset_file = $path;
			$asset      = require $asset_file;
		}

		return $asset;
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

		$this->add_setup_script( self::JS_HANDLE, true );

		wp_localize_script(
			self::JS_HANDLE,
			'ampSetup',
			[
				'APP_ROOT_ID' => self::APP_ROOT_ID,
			]
		);
	}

	/**
	 * Whether the plugin should override scripts for the onboarding screen.
	 *
	 * @return boolean
	 */
	public function should_override_scripts() {
		global $wp_version;

		$should_override_scripts = version_compare( $wp_version, self::TARGET_WP_VERSION, '<' ) && version_compare( $wp_version, '5.0', '>=' );

		/**
		 * Filters whether the plugin should override scripts for the onboarding screen.
		 *
		 * @param boolean $should_override_scripts Default true if WP version is between 5.0 and the current target version.
		 */
		return apply_filters( 'amp_should_override_setup_scripts', $should_override_scripts );
	}

	/**
	 * Overrides core assets with required versions if needed.
	 *
	 * @param string $hook_suffix The current admin page.
	 */
	public function override_scripts( $hook_suffix ) {
		global $wp_scripts;

		if ( $this->screen_handle() !== $hook_suffix ) {
			return;
		}

		if ( ! $this->should_override_scripts() ) {
			return;
		}

		$bundled_core_packages = [
			'wp-dom-ready',
			'wp-element',
			'wp-escape-html',
			'wp-i18n',
			'wp-polyfill',
			'wp-url',
		];

		$bundled_external_libraries = [ 'react', 'react-dom', 'lodash' ];

		foreach ( $bundled_core_packages as $package ) {
			if ( ! array_key_exists( $package, $wp_scripts->registered ) ) {
				continue;
			}

			$wp_scripts->registered[ $package ]->src = amp_get_asset_url( "js/{$package}.js" );

			$asset = $this->get_asset( $package, AMP__DIR__ . "/assets/js/{$package}.asset.php" );

			$wp_scripts->registered[ $package ]->ver = $asset['version'];
		}

		foreach ( $bundled_external_libraries as $library ) {
			if ( ! array_key_exists( $library, $wp_scripts->registered ) ) {
				continue;
			}

			$wp_scripts->registered[ $library ]->ver = AMP__VERSION;
			$wp_scripts->registered[ $library ]->src = amp_get_asset_url( "js/vendor/{$library}.js" );

		}
	}
}
