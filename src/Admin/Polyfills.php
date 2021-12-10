<?php
/**
 * Handles backward compatibility of assets for older versions of WP.
 *
 * @since 2.0
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Admin;

use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\HasRequirements;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Services;
use WP_Scripts;
use WP_Styles;

/**
 * Registers assets that may not be available in the current site's version of core.
 *
 * @since 2.0
 * @internal
 */
final class Polyfills implements Conditional, Delayed, Service, Registerable, HasRequirements {

	/**
	 * Get the action to use for registering the service.
	 *
	 * @return string Registration action to use.
	 */
	public static function get_registration_action() {
		return 'amp_register_polyfills';
	}

	/**
	 * Check whether the conditional object is currently needed.
	 *
	 * @return bool Whether the conditional object is needed.
	 */
	public static function is_needed() {
		return ! Services::get( 'dependency_support' )->has_support();
	}

	/**
	 * Get the list of service IDs required for this service to be registered.
	 *
	 * @return string[] List of required services.
	 */
	public static function get_requirements() {
		return [
			'dependency_support',
		];
	}

	/**
	 * Runs on instantiation.
	 */
	public function register() {
		if ( function_exists( 'get_current_screen' ) ) {
			$screen = get_current_screen();
			if ( ! empty( $screen->is_block_editor ) ) {
				return;
			}
		}

		// Applicable to Gutenberg v5.5.0 and older.
		if ( function_exists( 'is_gutenberg_page' ) && is_gutenberg_page() ) {
			return;
		}

		$this->register_shimmed_scripts( wp_scripts() );
		$this->register_shimmed_styles( wp_styles() );
	}

	/**
	 * Registers scripts not guaranteed to be available in core.
	 *
	 * @param WP_Scripts $wp_scripts The WP_Scripts instance for the current page.
	 */
	public function register_shimmed_scripts( $wp_scripts ) {
		$was_overridden = $this->override_script(
			$wp_scripts,
			'lodash',
			amp_get_asset_url( 'js/vendor/lodash.js' ),
			[],
			'4.17.21',
			true
		);

		if ( ! $was_overridden ) {
			$wp_scripts->add_inline_script( 'lodash', 'window.lodash = _.noConflict();' );
		}

		/*
		 * Polyfill dependencies that are registered in Gutenberg and WordPress 5.0.
		 * Note that Gutenberg will override these at wp_enqueue_scripts if it is active.
		 */
		$handles = [ 'wp-i18n', 'wp-dom-ready', 'wp-hooks', 'wp-html-entities', 'wp-polyfill', 'wp-url' ];
		foreach ( $handles as $handle ) {
			$asset_file   = AMP__DIR__ . "/assets/js/{$handle}.asset.php";
			$asset        = require $asset_file;
			$dependencies = $asset['dependencies'];
			$version      = $asset['version'];

			$this->override_script(
				$wp_scripts,
				$handle,
				amp_get_asset_url( "js/{$handle}.js" ),
				$dependencies,
				$version,
				true
			);
		}

		$asset_handle = 'wp-api-fetch';
		$asset_file   = AMP__DIR__ . "/assets/js/{$asset_handle}.asset.php";
		$asset        = require $asset_file;
		$dependencies = $asset['dependencies'];
		$version      = $asset['version'];

		$was_overridden = $this->override_script(
			$wp_scripts,
			$asset_handle,
			amp_get_asset_url( "js/{$asset_handle}.js" ),
			$dependencies,
			$version,
			true
		);

		if ( ! $was_overridden ) {
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
		$this->override_style(
			$wp_styles,
			'wp-components',
			amp_get_asset_url( 'css/wp-components.css' ),
			[],
			AMP__VERSION
		);
	}

	/**
	 * Registers a script according to `wp_register_script()`. Honors this request by reassigning internal dependency
	 * properties of any script handle already registered by that name. It does not deregister the original script, to
	 * avoid losing inline scripts which may have been attached.
	 *
	 * Adapted from `gutenberg_override_script()` in the Gutenberg plugin.
	 *
	 * @link https://github.com/WordPress/gutenberg/blob/132fec1fb5b4ab6af1d7696cbfe0574597644f18/lib/client-assets.php#L56-L105
	 *
	 * @param WP_Scripts       $scripts   WP_Scripts instance.
	 * @param string           $handle    Name of the script. Should be unique.
	 * @param string           $src       Full URL of the script, or path of the script relative to the WordPress root directory.
	 * @param array            $deps      Optional. An array of registered script handles this script depends on. Default empty array.
	 * @param string|bool|null $ver       Optional. String specifying script version number, if it has one, which is added to the URL
	 *                                    as a query string for cache busting purposes. If version is set to false, a version
	 *                                    number is automatically added equal to current installed WordPress version.
	 *                                    If set to null, no version is added.
	 * @param bool             $in_footer Optional. Whether to enqueue the script before </body> instead of in the <head>.
	 *                                    Default `false`.
	 *
	 * @return bool Whether or not the script was overridden.
	 */
	public function override_script( $scripts, $handle, $src, $deps = [], $ver = false, $in_footer = false ) {
		$script = $scripts->query( $handle, 'registered' );

		if ( $script ) {
			/*
			 * In many ways, this is a reimplementation of `wp_register_script` but
			 * bypassing consideration of whether a script by the given handle had
			 * already been registered.
			 */

			// See: `_WP_Dependency::__construct()`.
			$script->src  = $src;
			$script->deps = $deps;
			$script->ver  = $ver;
			$script->args = $in_footer;

			/*
			 * The script's `group` designation is an indication of whether it is
			 * to be printed in the header or footer. The behavior here defers to
			 * the arguments as passed. Specifically, group data is not assigned
			 * for a script unless it is designated to be printed in the footer.
			 */

			// See: `wp_register_script()` .
			unset( $script->extra['group'] );
			if ( $in_footer ) {
				$script->add_data( 'group', 1 );
			}
		} else {
			$scripts->add( $handle, $src, $deps, $ver, $in_footer );
		}

		return (bool) $script;
	}

	/**
	 * Registers a style according to `wp_register_style`. Honors this request by deregistering any style by the same
	 * handler before registration.
	 *
	 * Adapted from `gutenberg_override_style()` in the Gutenberg plugin.
	 *
	 * @link https://github.com/WordPress/gutenberg/blob/132fec1fb5b4ab6af1d7696cbfe0574597644f18/lib/client-assets.php#L177-L182
	 *
	 * @param WP_Styles        $styles WP_Styles instance.
	 * @param string           $handle Name of the stylesheet. Should be unique.
	 * @param string           $src    Full URL of the stylesheet, or path of the stylesheet relative to the WordPress root directory.
	 * @param array            $deps   Optional. An array of registered stylesheet handles this stylesheet depends on. Default empty array.
	 * @param string|bool|null $ver    Optional. String specifying stylesheet version number, if it has one, which is added to the URL
	 *                                 as a query string for cache busting purposes. If version is set to false, a version
	 *                                 number is automatically added equal to current installed WordPress version.
	 *                                 If set to null, no version is added.
	 * @param string           $media  Optional. The media for which this stylesheet has been defined.
	 *                                 Default 'all'. Accepts media types like 'all', 'print' and 'screen', or media queries like
	 *                                 '(orientation: portrait)' and '(max-width: 640px)'.
	 */
	public function override_style( $styles, $handle, $src, $deps = [], $ver = false, $media = 'all' ) {
		$style = $styles->query( $handle, 'registered' );
		if ( $style ) {
			$styles->remove( $handle );
		}
		$styles->add( $handle, $src, $deps, $ver, $media );
	}
}
