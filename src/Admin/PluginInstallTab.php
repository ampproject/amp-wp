<?php
/**
 * Add new tab (AMP) in plugin install screen in WordPress admin.
 *
 * @package Ampproject\Ampwp
 */

namespace AmpProject\AmpWP\Admin;

use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;

/**
 * Add new tab (AMP) in plugin install screen in WordPress admin.
 *
 * @since 2.2
 * @internal
 */
class PluginInstallTab implements Conditional, Service, Registerable {

	/**
	 * Check whether the conditional object is currently needed.
	 *
	 * @return bool Whether the conditional object is needed.
	 */
	public static function is_needed() {

		return ( ! wp_doing_ajax() && is_admin() );
	}

	/**
	 * Adds hooks.
	 *
	 * @return void
	 */
	public function register() {

		add_filter( 'install_plugins_tabs', [ $this, 'add_tab' ] );
		add_filter( 'install_plugins_table_api_args_amp', [ $this, 'amp_tab_args' ] );
		add_filter( 'plugins_api', [ $this, 'plugins_api' ], 10, 3 );

		add_action( 'install_plugins_amp', [ $this, 'install_plugin_amp' ] );
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
				'amp' => esc_html__( 'AMP', 'amp' ),
			],
			$tabs
		);
	}

	/**
	 * To modify args for AMP tab in plugin install screen.
	 *
	 * @return array
	 */
	public function amp_tab_args() {

		return [
			'amp' => true,
		];
	}

	/**
	 * Filter the response of API call to wordpress.org for plugin data.
	 *
	 * @param bool|array $response List of AMP compatible plugins.
	 * @param string     $action   API Action.
	 * @param array      $args     Args for plugin list.
	 *
	 * @return \stdClass|array List of AMP compatible plugins.
	 */
	public function plugins_api( $response, $action, $args ) {

		$args = (array) $args;
		if ( ! isset( $args['amp'] ) ) {
			return $response;
		}

		$plugin_json = AMP__DIR__ . '/data/plugins.json';
		$json_data   = file_get_contents( $plugin_json );

		$response          = new \stdClass();
		$response->plugins = json_decode( $json_data, true );
		$response->info    = [
			'page'    => 1,
			'pages'   => 1,
			'results' => count( $response->plugins ),
		];

		return $response;
	}

	/**
	 * Content for AMP tab in plugin install screen.
	 *
	 * @return void
	 */
	public function install_plugin_amp() {
		display_plugins_table();
	}
}
