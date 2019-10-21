<?php
/**
 * Plugin Name: AMP Beta Tester
 * Description: Opt-in to receive non-stable release builds for the AMP plugin.
 * Plugin URI: https://amp-wp.org
 * Author: AMP Project Contributors
 * Author URI: https://github.com/ampproject/amp-wp/graphs/contributors
 * Version: 0.1
 * Text Domain: amp-beta-tester
 * Domain Path: /languages/
 * License: GPLv2 or later
 *
 * @package AMP Beta Tester
 */

namespace AMP_Beta_Tester;

define( 'AMP__BETA_TESTER__DIR__', dirname( __FILE__ ) );

// DEV_CODE. This block of code is removed during the build process.
if ( file_exists( AMP__BETA_TESTER__DIR__ . '/amp.php' ) ) {
	add_filter(
		'site_transient_update_plugins',
		function ( $updates ) {
			if ( isset( $updates->response ) && is_array( $updates->response ) ) {
				if ( array_key_exists( 'amp/amp-beta-tester.php', $updates->response ) ) {
					unset( $updates->response['amp/amp-beta-tester.php'] );
				}
			}

			return $updates;
		}
	);
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\init' );

/**
 * Hook into WP.
 *
 * @return void
 */
function init() {
	// Abort init if AMP plugin is not active.
	if ( ! defined( 'AMP__FILE__' ) ) {
		add_action( 'admin_notices', __NAMESPACE__ . '\show_amp_not_active_notice' );
		return;
	}
}

/**
 * Display an admin notice if the AMP plugin is not active.
 *
 * @return void
 */
function show_amp_not_active_notice() {
	$error = esc_html__( 'AMP Beta Tester requires AMP to be active.', 'amp-beta-tester' );

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo "<div class='notice notice-error'><p><strong>{$error}</strong></p></div>";
}
