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
class AMPThemes implements Service, Registerable {

	/**
	 * Assets handle.
	 *
	 * @var string
	 */
	const ASSET_HANDLE = 'amp-theme-install';

	/**
	 * @var array List of AMP themes.
	 */
	public static $themes = [];

	/**
	 * Fetch AMP themes data.
	 *
	 * @return void
	 */
	public static function set_themes() {

		$file_path    = AMP__DIR__ . '/data/themes.json';
		$json_data    = file_get_contents( $file_path );
		self::$themes = json_decode( $json_data, true );
	}

	/**
	 * Adds hooks.
	 *
	 * @return void
	 */
	public function register() {

		self::set_themes();

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

		if ( $screen instanceof \WP_Screen && in_array( $screen->id, [ 'themes', 'theme-install' ], true ) ) {
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		}
	}

	/**
	 * Enqueue scripts and style for install theme screen.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {

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
			'amp-admin',
			amp_get_asset_url( 'css/amp-admin.css' ),
			[],
			AMP__VERSION
		);

		$none_wporg = [];

		foreach ( self::$themes as $theme ) {
			if ( true !== $theme['wporg'] ) {
				$none_wporg[] = $theme['slug'];
			}
		}

		$js_data = [
			'AMP_THEMES'        => wp_list_pluck( self::$themes, 'slug' ),
			'NONE_WPORG_THEMES' => $none_wporg,
		];

		wp_add_inline_script(
			self::ASSET_HANDLE,
			sprintf(
				'var ampThemes = %s;',
				wp_json_encode( $js_data )
			),
			'before'
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

		$args['per_page'] = ( ! empty( $args['per_page'] ) ) ? $args['per_page'] : 36;

		$page         = ( ! empty( $args['page'] ) && 0 < (int) $args['page'] ) ? (int) $args['page'] : 1;
		$theme_chunks = array_chunk( (array) self::$themes, $args['per_page'] );
		$themes       = ( ! empty( $theme_chunks[ $page - 1 ] ) && is_array( $theme_chunks[ $page - 1 ] ) ) ? $theme_chunks[ $page - 1 ] : [];

		if ( 'query_themes' === $action ) {
			foreach ( $themes as $i => $theme ) {
				$response->themes[ $i ] = (object) $theme;
			}
		} else {
			$response->themes = $themes;
		}

		$response->info = [
			'page'    => $page,
			'pages'   => count( $theme_chunks ),
			'results' => count( (array) self::$themes ),
		];

		return $response;
	}
}
