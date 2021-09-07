<?php
/**
 * Add new tab (AMP) in theme install screen in WordPress admin.
 *
 * @package Ampproject\Ampwp
 */

namespace AmpProject\AmpWP\Admin;

use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;

/**
 * Add new tab (AMP) in theme install screen in WordPress admin.
 *
 * @since 2.2
 * @internal
 */
class ThemeInstallTab implements Service, Registerable {

	/**
	 * Assets handle.
	 *
	 * @var string
	 */
	const ASSETS_HANDLE = 'amp-theme-install';

	/**
	 * @var array List AMP plugins.
	 */
	protected $themes = [];

	/**
	 * Fetch AMP themes data.
	 *
	 * @return void
	 */
	protected function set_themes() {

		$file_path    = AMP__DIR__ . '/data/themes.json';
		$json_data    = file_get_contents( $file_path );
		$this->themes = json_decode( $json_data, true );
	}

	/**
	 * Adds hooks.
	 *
	 * @return void
	 */
	public function register() {

		$this->set_themes();

		add_filter( 'themes_api', [ $this, 'themes_api' ], 10, 3 );

		if ( ! wp_doing_ajax() && is_admin() ) {
			add_action( 'current_screen', [ $this, 'register_hooks' ] );
		}
	}

	/**
	 * Register all hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {

		$screen = get_current_screen();

		if ( $screen instanceof \WP_Screen && 'theme-install' === $screen->id ) {
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		}
	}

	/**
	 * Enqueue scripts and style for install theme screen.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {

		$asset_file   = AMP__DIR__ . '/assets/js/' . self::ASSETS_HANDLE . '.asset.php';
		$asset        = require $asset_file;
		$dependencies = $asset['dependencies'];
		$version      = $asset['version'];

		wp_enqueue_script(
			self::ASSETS_HANDLE,
			amp_get_asset_url( 'js/' . self::ASSETS_HANDLE . '.js' ),
			$dependencies,
			$version,
			true
		);

		wp_enqueue_style(
			'amp-admin-plugin-install',
			amp_get_asset_url( 'css/admin-plugin-install.css' ),
			[ 'amp-icons' ],
			AMP__VERSION
		);
	}

	/**
	 * Filter the response of API call to wordpress.org for theme data.
	 *
	 * @param bool|object $response List of AMP compatible theme.
	 * @param string      $action   API Action.
	 * @param array       $args     Args for plugin list.
	 *
	 * @return object List of AMP compatible plugins.
	 */
	public function themes_api( $response, $action, $args ) {

		$args = (array) $args;
		if ( ! isset( $args['browse'] ) || 'px_enhancing' !== $args['browse'] ) {
			return $response;
		}

		$response         = new \stdClass();
		$response->themes = [];

		$page         = ( ! empty( $args['page'] ) && 0 < (int) $args['page'] ) ? (int) $args['page'] : 1;
		$theme_chunks = array_chunk( (array) $this->themes, $args['per_page'] );
		$themes       = ( ! empty( $theme_chunks[ $page - 1 ] ) && is_array( $theme_chunks[ $page - 1 ] ) ) ? $theme_chunks[ $page - 1 ] : [];

		if ( 'query_themes' === $action ) {
			foreach ( $themes as $i => $theme ) {
				$response->themes[ $i ] = (object) $theme;
			}
		}

		$response->info = [
			'page'    => $page,
			'pages'   => count( $theme_chunks ),
			'results' => count( (array) $this->themes ),
		];

		return $response;
	}
}
