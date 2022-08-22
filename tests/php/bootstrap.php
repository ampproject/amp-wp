<?php
/**
 * PHPUnit bootstrap file.
 */

use Yoast\WPTestUtils\WPIntegration;

define( 'TESTS_PLUGIN_DIR', dirname( dirname( __DIR__ ) ) );

// Detect where to load the WordPress tests environment from.
if ( false !== getenv( 'WP_TESTS_DIR' ) ) {
	$_test_root = getenv( 'WP_TESTS_DIR' );
} elseif ( false !== getenv( 'WP_DEVELOP_DIR' ) ) {
	$_test_root = getenv( 'WP_DEVELOP_DIR' ) . '/tests/phpunit';
} elseif ( file_exists( '/tmp/wordpress-tests/includes/bootstrap.php' ) ) {
	$_test_root = '/tmp/wordpress-tests';
} elseif ( file_exists( '/var/www/wordpress-develop/tests/phpunit' ) ) {
	$_test_root = '/var/www/wordpress-develop/tests/phpunit';
} else {
	$_test_root = dirname( dirname( dirname( dirname( TESTS_PLUGIN_DIR ) ) ) ) . '/tests/phpunit';
}

// When run in wp-env context, set the test config file path.
if ( ! defined( 'WP_TESTS_CONFIG_FILE_PATH' ) && false !== getenv( 'WP_PHPUNIT__TESTS_CONFIG' ) ) {
	define( 'WP_TESTS_CONFIG_FILE_PATH', getenv( 'WP_PHPUNIT__TESTS_CONFIG' ) );
}

require_once TESTS_PLUGIN_DIR . '/vendor/yoast/wp-test-utils/src/WPIntegration/bootstrap-functions.php';
$_tests_dir = Yoast\WPTestUtils\WPIntegration\get_path_to_wp_test_dir();
require_once $_test_root . '/includes/functions.php';

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

// Fix up the SERVER_PORT which was just clobbered by tests_reset__SERVER().
if ( defined( 'WP_HOME' ) ) {
	$port = wp_parse_url( WP_HOME, PHP_URL_PORT );
	if ( ! $port ) {
		$port = '80';
	}
	$_SERVER['SERVER_PORT'] = $port;
}
