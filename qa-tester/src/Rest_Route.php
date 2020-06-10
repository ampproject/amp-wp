<?php
/**
 * Class Rest_Route.
 *
 * @package AmpProject\AmpWP_QA_Tester
 */

namespace AmpProject\AmpWP_QA_Tester;

use WP_Error;
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
	public function register_route() {
		$route_uri = '/switch';
		$args      = [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'switch_callback' ],
			'permission_callback' => static function () {
				return current_user_can( 'update_plugins' );
			},
			'args'                => [
				'isDev' => [
					'validate_callback' => static function ( $param ) {
						return filter_var( $param, FILTER_VALIDATE_BOOLEAN );
					},
					'sanitize_callback' => static function ( $param ) {
						return rest_sanitize_boolean( $param );
					},
					'required'          => true,
				],
				'id'    => [
					'validate_callback' => static function ( $param ) {
						if ( 'release' === $param || 'develop' === $param ) {
							return true;
						}
						return filter_var( $param, FILTER_VALIDATE_INT );
					},
					'sanitize_callback' => static function ( $param ) {
						if ( 'release' === $param || 'develop' === $param ) {
							return $param;
						}
						return (int) $param;
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
	 * @return array|false|WP_Error
	 */
	public function switch_callback( WP_REST_Request $request ) {
		$build_id     = $request->get_param( 'id' );
		$is_dev_build = $request->get_param( 'isDev' );

		// If the request is for the release version, retrieve the latest version from the wordpress.org API.
		if ( 'release' === $build_id ) {
			$args = [
				'slug'   => Plugin::PLUGIN_SLUG,
				'fields' => [
					'version'    => true,
					'downloaded' => true,
				],
			];

			$response = wp_remote_post(
				'https://api.wordpress.org/plugins/info/1.0/',
				[
					'body' => [
						'action'  => 'plugin_information',
						'request' => serialize( (object) $args ),
					],
				]
			);

			if ( ! is_wp_error( $response ) ) {
				$returned_object = unserialize( wp_remote_retrieve_body( $response ) );

				if ( $returned_object ) {
					update_site_option( Plugin::ID_STORAGE_KEY, '' );
					return $this->switch_version( $returned_object->download_link, $returned_object->version );
				}
			}
		} else {
			$ref   = 'develop' === $build_id ? 'heads/develop' : "{$build_id}/merge";
			$build = ( $is_dev_build ? 'dev' : 'prod' );

			$download_url = str_replace( [ '{ref}', '{build}' ], [ $ref, $build ], Plugin::DOWNLOAD_BASE );
			$result       = $this->switch_version( $download_url, $build_id );

			if ( ! empty( $result ) && ! $result instanceof WP_Error ) {
				// Store the ID so we can reference it later in the selector.
				update_site_option( Plugin::ID_STORAGE_KEY, $build_id );
			}

			return $result;
		}

		return false;
	}
}
