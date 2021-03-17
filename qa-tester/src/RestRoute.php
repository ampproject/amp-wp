<?php
/**
 * Class RestRoute.
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
class RestRoute {

	const REST_NAMESPACE = 'amp-qa-tester/v1';

	/**
	 * Registers functionality through WordPress hooks.
	 */
	public function register() {
		add_action( 'rest_api_init', [ $this, 'register_route' ] );
	}

	/**
	 * Registers the `/install` route that is used to install the specified plugin build.
	 */
	public function register_route() {
		$route_uri = '/install';
		$args      = [
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'install_callback' ],
			'permission_callback' => static function () {
				return current_user_can( 'update_plugins' );
			},
			'args'                => [
				'id'     => [
					'validate_callback' => static function ( $param ) {
						if ( 'develop' === $param ) {
							return true;
						}

						// Return true if the value matches the pattern of a release version.
						if ( preg_match( '/^\d+\.\d+(?:\.\d+)?(?:-\w+)?$/', $param ) ) {
							return true;
						}

						// Otherwise, validate if the value is a PR number.
						return filter_var( $param, FILTER_VALIDATE_INT );
					},
					'sanitize_callback' => static function ( $param ) {
						if ( 'develop' === $param ) {
							return $param;
						}

						if ( preg_match( '/\d+\.\d+(?:\.\d+)?(?:-\w+)?/', $param, $matches ) ) {
							return $matches[0];
						}

						return filter_var( $param, FILTER_SANITIZE_NUMBER_INT );
					},
					'required'          => true,
				],

				'origin' => [
					'validate_callback' => static function ( $param ) {
						return in_array( $param, [ 'release', 'branch', 'pr' ], true );
					},
					'required'          => true,
				],

				'url'    => [
					'validate_callback' => 'wp_http_validate_url',
					'sanitize_callback' => 'esc_url_raw',
					'required'          => false,
				],

				'isDev'  => [
					'validate_callback' => static function ( $param ) {
						return is_bool( $param );
					},
					'sanitize_callback' => static function ( $param ) {
						return rest_sanitize_boolean( $param );
					},
					'required'          => true,
				],
			],
		];

		register_rest_route( self::REST_NAMESPACE, $route_uri, $args );
	}

	/**
	 * Handle `install` REST route.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return bool|WP_Error
	 */
	public function install_callback( WP_REST_Request $request ) {
		$build_id     = $request->get_param( 'id' );
		$build_origin = $request->get_param( 'origin' );
		$build_url    = $request->get_param( 'url' );
		$is_dev_build = $request->get_param( 'isDev' );

		$build_installer = new BuildInstaller( $build_id, $build_origin );

		if ( 'pr' === $build_origin || 'branch' === $build_origin ) {
			$build     = ( $is_dev_build ? 'dev' : 'prod' );
			$ref       = 'branch' === $build_origin ? 'heads/' . $build_id : "pull/{$build_id}/merge";
			$build_url = str_replace( [ '{ref}', '{build}' ], [ $ref, $build ], Plugin::DOWNLOAD_BASE );
		}

		$result = $build_installer->install( $build_url );

		if ( ! empty( $result ) && ! $result instanceof WP_Error ) {
			// Store information about the currently installed build so that it can be used as reference to display
			// the currently active build in the admin bar.
			$build_info = compact( 'build_id', 'build_origin' );
			update_site_option( Plugin::ID_STORAGE_KEY, $build_info );
		}

		return rest_ensure_response( $result );
	}
}
