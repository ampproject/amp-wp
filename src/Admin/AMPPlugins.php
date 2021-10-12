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
use WP_Screen;
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
	 * List of AMP plugins.
	 *
	 * @var array|bool
	 */
	protected $plugins = false;

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

		return is_admin();
	}

	/**
	 * Get list of AMP plugins.
	 *
	 * @return array List of AMP plugins.
	 */
	public function get_plugins() {

		if ( ! is_array( $this->plugins ) ) {
			$file_path = AMP__DIR__ . '/includes/amp-plugins.php';

			if ( file_exists( $file_path ) ) {
				$this->plugins = include $file_path;
			}

			$this->plugins = ( ! empty( $this->plugins ) && is_array( $this->plugins ) ) ? $this->plugins : [];
			$this->plugins = array_map(
				static function ( $plugin ) {
					return self::normalize_plugin_data( $plugin );
				},
				$this->plugins
			);
		}

		return $this->plugins;
	}

	/**
	 * Normalize plugin data.
	 *
	 * @param array $plugin Plugin data.
	 *
	 * @return array Normalized plugin data.
	 */
	public static function normalize_plugin_data( $plugin = [] ) {

		$default = [
			'name'                     => '',
			'slug'                     => '',
			'version'                  => '',
			'author'                   => '',
			'author_profile'           => '',
			'requires'                 => '',
			'tested'                   => '',
			'requires_php'             => '',
			'rating'                   => 0,
			'ratings'                  => [
				1 => 0,
				2 => 0,
				3 => 0,
				4 => 0,
				5 => 0,
			],
			'num_ratings'              => 0,
			'support_threads'          => 0,
			'support_threads_resolved' => 0,
			'active_installs'          => 0,
			'downloaded'               => 0,
			'last_updated'             => '',
			'added'                    => '',
			'homepage'                 => '',
			'short_description'        => '',
			'description'              => '',
			'download_link'            => '',
			'tags'                     => [],
			'donate_link'              => '',
			'icons'                    => [
				'1x'  => '',
				'2x'  => '',
				'svg' => '',
			],
			'wporg'                    => false,
		];

		$plugin['ratings'] = ( ! empty( $plugin['ratings'] ) && is_array( $plugin['ratings'] ) ) ? $plugin['ratings'] : [];
		$plugin['ratings'] = $plugin['ratings'] + $default['ratings'];

		$plugin['icons'] = ( ! empty( $plugin['icons'] ) && is_array( $plugin['icons'] ) ) ? $plugin['icons'] : [];
		$plugin['icons'] = wp_parse_args( $plugin['icons'], $default['icons'] );

		return wp_parse_args( $plugin, $default );
	}

	/**
	 * Adds hooks.
	 *
	 * @return void
	 */
	public function register() {

		$screen = get_current_screen();

		if ( $screen instanceof WP_Screen && in_array( $screen->id, [ 'plugins', 'plugin-install' ], true ) ) {
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

		foreach ( $this->get_plugins() as $plugin ) {
			if ( true !== $plugin['wporg'] ) {
				$none_wporg[] = $plugin['slug'];
			}
		}

		$js_data = [
			'AMP_PLUGINS'        => wp_list_pluck( $this->get_plugins(), 'slug' ),
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
		$total_page = ceil( count( $this->get_plugins() ) / $per_page );
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
	 * @param bool|array|stdClass $response List of AMP compatible plugins.
	 * @param string              $action   API Action.
	 * @param array               $args     Args for plugin list.
	 *
	 * @return stdClass|array List of AMP compatible plugins.
	 */
	public function plugins_api( $response, $action, $args ) {

		$args = (array) $args;
		if ( ! isset( $args['px_enhancing'] ) ) {
			return $response;
		}

		$total_page    = ceil( count( $this->get_plugins() ) / $args['per_page'] );
		$page          = ( ! empty( $args['page'] ) && 0 < (int) $args['page'] ) ? (int) $args['page'] : 1;
		$plugin_chunks = array_chunk( (array) $this->get_plugins(), $args['per_page'] );
		$plugins       = ( ! empty( $plugin_chunks[ $page - 1 ] ) && is_array( $plugin_chunks[ $page - 1 ] ) ) ? $plugin_chunks[ $page - 1 ] : [];

		$response          = new stdClass();
		$response->plugins = $plugins;
		$response->info    = [
			'page'    => $page,
			'pages'   => $total_page,
			'results' => count( $this->get_plugins() ),
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
					'<a href="%s" target="_blank" aria-label="%s">%s</a>',
					esc_url( $plugin['homepage'] ),
					esc_attr(
						/* translators: %s: Plugin name */
						sprintf( __( 'Site link of %s', 'amp' ), $plugin['name'] )
					),
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

		$amp_plugins = wp_list_pluck( $this->get_plugins(), 'slug' );

		if ( ! empty( $plugin_data['slug'] ) && in_array( $plugin_data['slug'], $amp_plugins, true ) ) {
			$plugin_meta[] = sprintf(
				'<span><span class="amp-logo-icon small"></span>&nbsp;%s</span>',
				esc_html__( 'AMP Compatible', 'amp' )
			);
		}

		return $plugin_meta;
	}
}
