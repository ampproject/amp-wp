<?php
/**
 * Plugin Name: AMP QA Tester
 * Description: Test pre-release versions of the AMP plugin.
 * Plugin URI: https://amp-wp.org
 * Author: AMP Project Contributors
 * Author URI: https://github.com/ampproject/amp-wp/graphs/contributors
 * Version: 1.0.0
 * Text Domain: amp-qa-tester
 * License: GPLv2 or later
 * Requires at least: 5.0
 * Requires PHP: 5.6
 *
 * @package AmpProject\AmpWP_QA_Tester
 */

namespace AmpProject\AmpWP_QA_Tester;

use WP_Error;

global $load_errors;

/**
 * Loads the plugin.
 *
 * @since 1.0.0
 */
function load_plugin() {
	global $load_errors;

	$min_php_version = '5.6';
	$min_wp_version  = '5.0';
	$load_errors = new WP_Error();

	// If the AMP plugin is not active, we simply bail.
	if ( ! defined( 'AMP__VERSION' ) ) {
		return;
	}

	if ( version_compare( phpversion(), $min_php_version, '<' ) ) {
		$load_errors->add(
			'insufficient_php_version',
			sprintf(
				/* translators: 1: required version, 2: currently used version */
				__( 'At least PHP version %1$s is required. Your site is currently running on PHP %2$s.', 'amp-qa-tester' ),
				$min_php_version,
				PHP_VERSION
			)
		);
	}

	if ( version_compare( get_bloginfo( 'version' ), $min_wp_version, '<' ) ) {
		$load_errors->add(
			'insufficient_wp_version',
			sprintf(
				/* translators: 1: required version, 2: currently used version */
				__( 'At least WordPress version %1$s is required. Your site is currently running on WordPress %2$s.', 'amp-qa-tester' ),
				$min_wp_version,
				get_bloginfo( 'version' )
			)
		);
	}

	if ( ! file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
		$load_errors->add(
			'build_required',
			sprintf(
				/* translators: %s: composer install && npm install && npm run build */
				__( 'You appear to be running the AMP QA Tester plugin from source. Please do %s to finish installation.', 'amp-qa-tester' ),
				'<code>composer install &amp;&amp; npm install &amp;&amp; npm run build</code>'
			)
		);
	}

	// Bail if there were errors loading the plugin.
	if ( ! empty( $load_errors->errors ) ) {
		add_action( 'admin_notices', __NAMESPACE__ . '\display_errors_admin_notice' );
		return;
	}

	define( 'AMP_QA_TESTER_VERSION', '1.0.0' );

	require __DIR__ . '/vendor/autoload.php';

	Plugin::load( __FILE__ );
}

/**
 * Displays an admin notice about an unmet PHP version requirement.
 *
 * @since 1.0.0
 */
function display_errors_admin_notice() {
	global $load_errors;
	?>
	<div class="notice notice-error">
		<p>
			<strong><?php esc_html_e( 'AMP QA Tester failed to initialize.', 'amp-qa-tester' ); ?></strong>
			<ul>
				<?php foreach ( array_keys( $load_errors->errors ) as $error_code ) : ?>
					<?php foreach ( $load_errors->get_error_messages( $error_code ) as $message ) : ?>
						<li>
							<?php echo wp_kses_post( $message ); ?>
						</li>
					<?php endforeach; ?>
				<?php endforeach; ?>
			</ul>
		</p>
	</div>
	<?php
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\load_plugin' );
