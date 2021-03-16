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
						if ( 'release' === $param || 'develop' === $param ) {
							return true;
						}

						// Return true if the value matches the patter of a release version.
						if ( preg_match( '/^[0-9]+\.[0-9]+$/', $param ) ) {
							return true;
						}

						// Otherwise, validate if the value is a PR number.
						return filter_var( $param, FILTER_VALIDATE_INT );
					},

					'sanitize_callback' => static function ( $param ) {
						if ( 'release' === $param || 'develop' === $param ) {
							return $param;
						}

						if ( preg_match( '/[0-9]+\.[0-9]+/', $param, $matches ) ) {
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
	 * @return array|false|WP_Error
	 */
	public function install_callback( WP_REST_Request $request ) {
		$build_id     = $request->get_param( 'id' );
		$build_origin = $request->get_param( 'origin' );
		$is_dev_build = $request->get_param( 'isDev' );

		$download_url     = null;
		$download_id      = null;
		$plugin_installer = new PluginInstaller( $build_origin );

		// If the request is for the release version, retrieve the latest version from the wordpress.org API.
		if ( 'release' === $build_origin ) {
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
						'request' => serialize( (object) $args ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
					],
				]
			);

			if ( is_wp_error( $response ) ) {
				return $response;
			}

			$returned_object = unserialize( wp_remote_retrieve_body( $response ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize

			if ( ! isset( $returned_object->download_link, $returned_object->version ) ) {
				return new WP_Error(
					'amp_plugin_latest_release',
					__( 'Failed to retrieve the download information for the latest release of the AMP plugin on WordPress.org', 'amp-qa-tester' )
				);
			}

			$download_url = $returned_object->download_link;
			$download_id  = $returned_object->version;
		} else {
			$ref   = 'branch' === $build_origin ? 'heads/' . $build_id : "pull/{$build_id}/merge";
			$build = ( $is_dev_build ? 'dev' : 'prod' );

			$download_id  = $build_id;
			$download_url = str_replace( [ '{ref}', '{build}' ], [ $ref, $build ], Plugin::DOWNLOAD_BASE );
		}

		$result = $plugin_installer->install( $download_url, $download_id );

		if ( ! empty( $result ) && ! $result instanceof WP_Error ) {
			// Store the ID so that it can be used as a reference to display the currently active build in the admin bar.
			update_site_option( Plugin::ID_STORAGE_KEY, $build_id );
		}

		return $result;
	}
}
