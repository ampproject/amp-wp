<?php
/**
 * PHPUnit bootstrap file.
 */

use Yoast\WPTestUtils\WPIntegration;

require_once dirname( __DIR__, 2 ) . '/vendor/yoast/wp-test-utils/src/WPIntegration/bootstrap-functions.php';

$_tests_dir = Yoast\WPTestUtils\WPIntegration\get_path_to_wp_test_dir();

// Give access to tests_add_filter() function.
require_once $_tests_dir . 'includes/functions.php';

// Force plugins defined in a constant (supplied by phpunit.xml) to be active at runtime.
function amp_filter_active_plugins_for_phpunit( $active_plugins ) {
	if ( defined( 'WP_TEST_ACTIVATED_PLUGINS' ) ) {
		$forced_active_plugins = preg_split( '/\s*,\s*/', WP_TEST_ACTIVATED_PLUGINS );
	}

	if ( ! empty( $forced_active_plugins ) ) {
		foreach ( $forced_active_plugins as $forced_active_plugin ) {
			$active_plugins[] = $forced_active_plugin;
		}
	}
	return $active_plugins;
}

tests_add_filter( 'site_option_active_sitewide_plugins', 'amp_filter_active_plugins_for_phpunit' );
tests_add_filter( 'option_active_plugins', 'amp_filter_active_plugins_for_phpunit' );

// Ensure plugin is always activated.
function amp_unit_test_load_plugin_file() {
	require_once TESTS_PLUGIN_DIR . '/amp.php';
}

tests_add_filter( 'muplugins_loaded', 'amp_unit_test_load_plugin_file' );

/*
 * Load WP CLI. Its test bootstrap file can't be required as it will load
 * duplicate class names which are already in use.
 */
define( 'WP_CLI_ROOT', TESTS_PLUGIN_DIR . '/vendor/wp-cli/wp-cli' );
define( 'WP_CLI_VENDOR_DIR', TESTS_PLUGIN_DIR . '/vendor' );
require_once WP_CLI_ROOT . '/php/utils.php';

$logger = new WP_CLI\Loggers\Regular( true );
WP_CLI::set_logger( $logger );

/*
 * Load WordPress, which will load the Composer autoload file, and load the MockObject autoloader after that.
 */
WPIntegration\bootstrap_it();
