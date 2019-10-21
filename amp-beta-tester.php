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
