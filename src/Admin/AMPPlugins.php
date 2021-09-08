<?php
/**
 * Add new tab (AMP) in plugin install screen in WordPress admin.
 *
 * @package Ampproject\Ampwp
 */

namespace AmpProject\AmpWP\Admin;

use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use stdClass;
use function get_current_screen;

/**
 * Add new tab (AMP) in plugin install screen in WordPress admin.
 *
 * @since 2.2
 * @internal
 */
class AMPPlugins implements Conditional, Delayed, Service, Registerable {

	/**
	 * Assets handle.
	 *
	 * @var string
	 */
	const ASSET_HANDLE = 'amp-plugin-install';

	/**
	 * @var array List AMP plugins.
	 */
	protected $plugins = [];

	/**
	 * Get the action to use for registering the service.
	 *
	 * @return string Registration action to use.
	 */
	public static function get_registration_action() {

		return 'current_screen';
	}

	/**
	 * Check whether the conditional object is currently needed.
	 *
	 * @return bool Whether the conditional object is needed.
	 */
	public static function is_needed() {

		return ( ! wp_doing_ajax() && is_admin() );
	}

	/**
	 * Fetch AMP plugin data.
	 *
	 * @return void
	 */
	protected function set_plugins() {

		$plugin_json   = AMP__DIR__ . '/data/plugins.json';
		$json_data     = file_get_contents( $plugin_json );
		$this->plugins = json_decode( $json_data, true );
	}

	/**
	 * Adds hooks.
	 *
	 * @return void
	 */
	public function register() {

		$this->set_plugins();
		$screen = get_current_screen();

		if ( $screen instanceof \WP_Screen && in_array( $screen->id, [ 'plugins', 'plugin-install' ], true ) ) {
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		}

		add_filter( 'install_plugins_tabs', [ $this, 'add_tab' ] );
		add_filter( 'install_plugins_table_api_args_px_enhancing', [ $this, 'tab_args' ] );
		add_filter( 'plugins_api', [ $this, 'plugins_api' ], 10, 3 );
		add_filter( 'plugin_install_action_links', [ $this, 'action_links' ], 10, 2 );
		add_filter( 'plugin_row_meta', [ $this, 'plugin_row_meta' ], 10, 3 );

		add_action( 'install_plugins_px_enhancing', 'display_plugins_table' );
	}

	/**
	 * Enqueue style for plugin install page.
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

		foreach ( $this->plugins as $plugin ) {
			if ( true !== $plugin['wporg'] ) {
				$none_wporg[] = $plugin['slug'];
			}
		}

		$js_data = [
			'AMP_PLUGINS'        => wp_list_pluck( $this->plugins, 'slug' ),
			'NONE_WPORG_PLUGINS' => $none_wporg,
		];

		wp_add_inline_script(
			self::ASSET_HANDLE,
			sprintf(
				'var ampPlugins = %s;',
				wp_json_encode( $js_data )
			),
			'before'
		);
	}

	/**
	 * Add extra tab in plugin install screen.
	 *
	 * @param array $tabs List of tab in plugin install screen.
	 *
	 * @return array List of tab in plugin install screen.
	 */
	public function add_tab( $tabs ) {

		return array_merge(
			[
				'px_enhancing' => '<span class="amp-logo-icon"></span> ' . esc_html__( 'PX Enhancing', 'amp' ),
			],
			$tabs
		);
	}

	/**
	 * To modify args for AMP tab in plugin install screen.
	 *
	 * @return array
	 */
	public function tab_args() {

		$per_page   = 36;
		$total_page = ceil( count( $this->plugins ) / $per_page );
		$pagenum    = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$pagenum    = ( $pagenum > $total_page ) ? $total_page : $pagenum;
		$page       = max( 1, $pagenum );

		return [
			'px_enhancing' => true,
			'per_page'     => $per_page,
			'page'         => $page,
		];
	}

	/**
	 * Filter the response of API call to wordpress.org for plugin data.
	 *
	 * @param bool|array $response List of AMP compatible plugins.
	 * @param string     $action   API Action.
	 * @param array      $args     Args for plugin list.
	 *
	 * @return stdClass|array List of AMP compatible plugins.
	 */
	public function plugins_api( $response, $action, $args ) {

		$args = (array) $args;
		if ( ! isset( $args['px_enhancing'] ) ) {
			return $response;
		}

		$total_page    = ceil( count( $this->plugins ) / $args['per_page'] );
		$page          = ( ! empty( $args['page'] ) && 0 < (int) $args['page'] ) ? (int) $args['page'] : 1;
		$plugin_chunks = array_chunk( (array) $this->plugins, $args['per_page'] );
		$plugins       = ( ! empty( $plugin_chunks[ $page - 1 ] ) && is_array( $plugin_chunks[ $page - 1 ] ) ) ? $plugin_chunks[ $page - 1 ] : [];

		$response          = new stdClass();
		$response->plugins = $plugins;
		$response->info    = [
			'page'    => $page,
			'pages'   => $total_page,
			'results' => count( $this->plugins ),
		];

		return $response;
	}

	/**
	 * Update action links for plugin card in plugin install screen.
	 *
	 * @param array $actions List of action button's markup for plugin card.
	 * @param array $plugin  Plugin detail.
	 *
	 * @return array List of action button's markup for plugin card.
	 */
	public function action_links( $actions, $plugin ) {

		if ( isset( $plugin['wporg'] ) && true !== $plugin['wporg'] ) {
			$actions = [];

			if ( ! empty( $plugin['homepage'] ) ) {
				$actions[] = sprintf(
					'<a href="%s" target="_blank" aria-label="Site link for %s">%s</a>',
					esc_url( $plugin['homepage'] ),
					esc_html( $plugin['name'] ),
					esc_html__( 'Visit site', 'amp' )
				);
			}
		}

		return $actions;
	}

	/**
	 * Add plugin metadata for AMP compatibility in plugin listing page.
	 *
	 * @param string[] $plugin_meta An array of the plugin's metadata, including
	 *                              the version, author, author URI, and plugin URI.
	 * @param string   $plugin_file Path to the plugin file relative to the plugins directory.
	 * @param array    $plugin_data An array of plugin data.
	 *
	 * @return string[] An array of the plugin's metadata
	 */
	public function plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data ) {

		$amp_plugins = wp_list_pluck( $this->plugins, 'slug' );

		if ( ! empty( $plugin_data['slug'] ) && in_array( $plugin_data['slug'], $amp_plugins, true ) ) {
			$plugin_meta[] = '<span><span class="amp-logo-icon small"></span>&nbsp;Page Experience Enhancing</span>';
		}

		return $plugin_meta;
	}
}
