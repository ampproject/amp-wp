<?php
/**
 * AMP setup wizard page.
 *
 * @package AMP
 * @since @todo NEW_ONBOARDING_RELEASE_VERSION
 */

/**
 * AMP setup wizard class.
 *
 * @since @todo NEW_ONBOARDING_RELEASE_VERSION
 */
class AMP_Setup_Wizard_Submenu_Page {
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
	 * The minimum core WP version whose core assets are compatibile with the setup feature.
	 *
	 * @var float
	 */
	const TARGET_WP_VERSION = 5.4;

	/**
	 * Sets up hooks.
	 */
	public function init() {
		add_action( 'admin_enqueue_scripts', [ $this, 'override_scripts' ] );
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
		$asset = $this->get_asset( $handle, sprintf( '%s/assets/js/%s.asset.php', AMP__DIR__, $handle ) );

		wp_register_script(
			$handle,
			amp_get_asset_url( sprintf( 'js/%s.js', $handle ) ),
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

		$this->add_setup_script( static::JS_HANDLE, true );

		wp_localize_script(
			static::JS_HANDLE,
			'ampSetup',
			[
				'APP_ROOT_ID' => static::APP_ROOT_ID,
			]
		);
	}

	/**
	 * Overrides core assets with required versions if needed.
	 *
	 * @param string $hook_suffix The current admin page.
	 */
	public function override_scripts( $hook_suffix ) {
		global $wp_version;

		if ( $this->screen_handle() !== $hook_suffix ) {
			return;
		}

		if ( floatval( $wp_version ) >= static::TARGET_WP_VERSION && wp_script_is( 'react', 'registered' ) ) {
			return;
		}

		$scripts = wp_scripts();

		$main_asset_dependencies = $this->get_asset( static::JS_HANDLE, sprintf( '%s/assets/js/%s.asset.php', AMP__DIR__, static::JS_HANDLE ) )['dependencies'];
		$wp_script_dependencies  = [];
		$external_dependencies   = [ 'react', 'react-dom' ];

		foreach ( $main_asset_dependencies as $dependency ) {
			if ( 0 === strpos( $dependency, 'wp-' ) ) {
				$wp_script_dependencies[] = $dependency;
			} else {
				$external_dependencies[] = $dependency;
			}
		}

		foreach ( $wp_script_dependencies as $package ) {
			if ( array_key_exists( $package, $scripts->registered ) ) {
				$scripts->registered[ $package ]->src = amp_get_asset_url( sprintf( 'js/%s.js', $package ) );

				$asset = $this->get_asset( $package, sprintf( '%s/assets/js/%s.asset.php', AMP__DIR__, $package ) );

				$scripts->registered[ $package ]->ver = $asset['version'];
			} else {
				$this->add_setup_script( $package );

				// Add inline scripts for 4.9.
				// @see https://github.com/WordPress/WordPress/blob/master/wp-includes/script-loader.php#L276.
				switch ( $package ) {
					case 'wp-api-fetch':
						$scripts->add_inline_script(
							'wp-api-fetch',
							sprintf(
								'wp.apiFetch.use( wp.apiFetch.createRootURLMiddleware( "%s" ) );',
								esc_url_raw( get_rest_url() )
							),
							'after'
						);

						$scripts->add_inline_script(
							'wp-api-fetch',
							implode(
								"\n",
								[
									sprintf(
										'wp.apiFetch.nonceMiddleware = wp.apiFetch.createNonceMiddleware( "%s" );',
										( wp_installing() && ! is_multisite() ) ? '' : wp_create_nonce( 'wp_rest' )
									),
									'wp.apiFetch.use( wp.apiFetch.nonceMiddleware );',
									'wp.apiFetch.use( wp.apiFetch.mediaUploadMiddleware );',
									sprintf(
										'wp.apiFetch.nonceEndpoint = "%s";',
										admin_url( 'admin-ajax.php?action=rest-nonce' )
									),
								]
							),
							'after'
						);
				}
			}
		}

		foreach ( $external_dependencies as $library ) {
			$src = amp_get_asset_url( sprintf( 'js/vendor/%s.js', $library ) );

			if ( array_key_exists( $library, $scripts->registered ) ) {
				$scripts->registered[ $library ]->ver = AMP__VERSION;
				$scripts->registered[ $library ]->src = $src;
			} else {
				wp_register_script(
					$library,
					$src,
					[],
					AMP__VERSION,
					true
				);
			}
		}
	}
}
