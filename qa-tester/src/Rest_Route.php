<?php

namespace AmpProject\AmpWP_QA_Tester;

use WP_REST_Request;
use WP_REST_Server;

/**
 * Class handling the plugin's rest route.
 *
 * @since 1.0.0
 */
class Rest_Route {
	use Version_Switcher;

	const REST_ROOT = 'amp-qa-tester/v1';

	/**
	 * Registers functionality through WordPress hooks.
	 *
	 * @since 1.0.0
	 */
	public function register() {
		add_action( 'rest_api_init', [ $this, 'register_route' ] );
	}

	/**
	 * Registers the `/switch` route that is used to switch the plugin version.
	 *
	 * @since 1.0.0
	 */
	protected function register_route() {
		$route_uri = '/switch';
		$args      = [
			'methods'  => WP_REST_Server::CREATABLE,
			'callback' => [ $this, 'switch_callback' ],
			'permission_callback' => static function() {
				return current_user_can( 'update_plugins' );
			},
			'args' => [
				'developBuild' => [
					'validate_callback' => static function( $param ) { return is_bool( $param ); },
					'sanitize_callback' => static function( $param ) { return sanitize_key( $param ); },
					'default'           => false,
				],
				'url' => [
					'validate_callback' => static function( $param ) {
						if ( 'release' === $param || 'develop' === $param ) {
							return true;
						}
						return wp_http_validate_url( $param );
					},
					'sanitize_callback' => static function( $param ) {
						if ( 'release' === $param || 'develop' === $param ) {
							return $param;
						}
						return esc_url_raw( $param );
					},
					'required'          => true,
				],
			],
		];

		register_rest_route( self::REST_ROOT, $route_uri, $args );
	}

	/**
	 * Handle `switch` REST route.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return array|false
	 */
	public function switch_callback( WP_REST_Request $request ) {
		$url = $request->get_param( 'url' );

		// If the request is for the release version, retrieve the latest version from the wordpress.org API.
		if ( $url === 'release') {
			$args = [
				'slug' => Plugin::PLUGIN_SLUG,
				'fields' => [
					'version'    => true,
					'downloaded' => true,
				]
			];

			$response = wp_remote_post(
				'https://api.wordpress.org/plugins/info/1.0/',
				[
					'body' => [
						'action'  => 'plugin_information',
						'request' => serialize( ( object ) $args ),
					]
				]
			);

			if ( ! is_wp_error( $response ) ) {
				$returned_object = unserialize( wp_remote_retrieve_body( $response ) );

				if ( $returned_object ) {
					update_site_option( Plugin::URL_STORAGE_KEY, '' );
					return $this->switch_version( $returned_object->download_link, $returned_object->version );
				}
			}
		} else {
			// Store the url so we can reference it later in the selector.
			update_site_option( Plugin::URL_STORAGE_KEY, $url );
			$develop = $request['developBuild'];

			// If the develop version is requested, the download url is different.
			if ( $url === 'develop') {
				$version      = 'develop';
				$download_url = str_replace( '{PR}/merge', 'heads/develop', Plugin::DOWNLOAD_BASE ) . ( $develop ? '-dev' : '' ) . '.zip';
			} else {
				$url          = str_replace( Plugin::REPO_BASE, '', $url );
				$url          = str_replace( 'pulls/', 'pull/', $url );
				$version      = str_replace( 'pull/', '', $url );
				$download_url = str_replace( '{PR}', urlencode( $url ), Plugin::DOWNLOAD_BASE ) . ( $develop ? '-dev' : '' ) . '.zip';
			}

			return $this->switch_version( $download_url, $version );
		}

		return false;
	}
}
