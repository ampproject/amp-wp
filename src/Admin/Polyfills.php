<?php
/**
 * Handles backward compatibility of assets for older versions of WP.
 *
 * @since 2.0
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Admin;

use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use WP_Scripts;
use WP_Styles;

/**
 * Registers assets that may not be available in the current site's version of core.
 *
 * @since 2.0
 * @internal
 */
final class Polyfills implements Delayed, Service, Registerable {

	/**
	 * Get the action to use for registering the service.
	 *
	 * @return string Registration action to use.
	 */
	public static function get_registration_action() {
		return 'amp_register_polyfills';
	}

	/**
	 * Runs on instantiation.
	 */
	public function register() {
		$this->register_shimmed_scripts( wp_scripts() );
		$this->register_shimmed_styles( wp_styles() );
	}

	/**
	 * Registers scripts not guaranteed to be available in core.
	 *
	 * @param WP_Scripts $wp_scripts The WP_Scripts instance for the current page.
	 */
	public function register_shimmed_scripts( $wp_scripts ) {
		if ( ! isset( $wp_scripts->registered['lodash'] ) ) {
			$wp_scripts->add(
				'lodash',
				amp_get_asset_url( 'js/vendor/lodash.js' ),
				[],
				'4.17.19',
				true
			);

			$wp_scripts->add_inline_script( 'lodash', 'window.lodash = _.noConflict();' );
		}

		/*
		 * Polyfill dependencies that are registered in Gutenberg and WordPress 5.0.
		 * Note that Gutenberg will override these at wp_enqueue_scripts if it is active.
		 */
		$handles = [ 'wp-i18n', 'wp-dom-ready', 'wp-polyfill', 'wp-url' ];
		foreach ( $handles as $handle ) {
			if ( ! isset( $wp_scripts->registered[ $handle ] ) ) {
				$asset_file   = AMP__DIR__ . "/assets/js/{$handle}.asset.php";
				$asset        = require $asset_file;
				$dependencies = $asset['dependencies'];
				$version      = $asset['version'];

				$wp_scripts->add(
					$handle,
					amp_get_asset_url( "js/{$handle}.js" ),
					$dependencies,
					$version
				);
			}
		}

		if ( ! isset( $wp_scripts->registered['wp-api-fetch'] ) ) {
			$asset_handle = 'wp-api-fetch';
			$asset_file   = AMP__DIR__ . "/assets/js/{$asset_handle}.asset.php";
			$asset        = require $asset_file;
			$version      = $asset['version'];

			$wp_scripts->add(
				$asset_handle,
				amp_get_asset_url( "js/{$asset_handle}.js" ),
				[],
				$version,
				true
			);

			$wp_scripts->add_inline_script(
				$asset_handle,
				sprintf(
					'wp.apiFetch.use( wp.apiFetch.createRootURLMiddleware( "%s" ) );',
					esc_url_raw( get_rest_url() )
				),
				'after'
			);
			$wp_scripts->add_inline_script(
				$asset_handle,
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

	/**
	 * Registers shimmed assets not guaranteed to be available in core.
	 *
	 * @param WP_Styles $wp_styles The WP_Styles instance for the current page.
	 */
	public function register_shimmed_styles( $wp_styles ) {
		if ( version_compare( get_bloginfo( 'version' ), '5.4', '<' ) ) {
			$wp_styles->remove( 'wp-components' );
		}

		if ( ! isset( $wp_styles->registered['wp-components'] ) ) {
			$wp_styles->add(
				'wp-components',
				amp_get_asset_url( 'css/wp-components.css' ),
				[],
				'10.0.2'
			);
		}
	}
}
