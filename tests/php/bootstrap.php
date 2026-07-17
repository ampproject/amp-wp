<?php
/**
 * PHPUnit bootstrap file.
 */

use Yoast\WPTestUtils\WPIntegration;

define( 'TESTS_PLUGIN_DIR', dirname( dirname( __DIR__ ) ) );

// Force script debugging to load unminified assets (e.g. view-transitions.css) since minified assets are not compiled in raw checkout environments.
if ( ! defined( 'SCRIPT_DEBUG' ) ) {
	define( 'SCRIPT_DEBUG', true );
}

// When run in wp-env context, set the test config file path.
if ( ! defined( 'WP_TESTS_CONFIG_FILE_PATH' ) && false !== getenv( 'WP_PHPUNIT__TESTS_CONFIG' ) ) {
	define( 'WP_TESTS_CONFIG_FILE_PATH', getenv( 'WP_PHPUNIT__TESTS_CONFIG' ) );
}

require_once TESTS_PLUGIN_DIR . '/vendor/yoast/wp-test-utils/src/WPIntegration/bootstrap-functions.php';
$_tests_dir = Yoast\WPTestUtils\WPIntegration\get_path_to_wp_test_dir();
require_once $_tests_dir . '/includes/functions.php';

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
	$plugin_slug = basename( TESTS_PLUGIN_DIR );
	$plugin_dir  = WP_PLUGIN_DIR . '/' . $plugin_slug;
	if ( ! file_exists( $plugin_dir ) && ! is_link( $plugin_dir ) && is_dir( WP_PLUGIN_DIR ) ) {
		symlink( TESTS_PLUGIN_DIR, $plugin_dir );
	}
	if ( 'amp' !== $plugin_slug && ! file_exists( WP_PLUGIN_DIR . '/amp' ) && ! is_link( WP_PLUGIN_DIR . '/amp' ) && is_dir( WP_PLUGIN_DIR ) ) {
		symlink( TESTS_PLUGIN_DIR, WP_PLUGIN_DIR . '/amp' );
	}
	require_once TESTS_PLUGIN_DIR . '/amp.php';
}

tests_add_filter( 'muplugins_loaded', 'amp_unit_test_load_plugin_file' );

// Normalize plugins_url for tests when repository directory is outside WP_PLUGIN_DIR.
tests_add_filter(
	'plugins_url',
	static function ( $url, $path, $plugin ) {
		if ( ! empty( $plugin ) && defined( 'TESTS_PLUGIN_DIR' ) ) {
			$norm_plugin    = wp_normalize_path( $plugin );
			$norm_tests_dir = wp_normalize_path( TESTS_PLUGIN_DIR );
			if ( 0 === strpos( $norm_plugin, $norm_tests_dir ) ) {
				$rel_path    = ltrim( substr( $norm_plugin, strlen( $norm_tests_dir ) ), '/' );
				$plugin_slug = basename( TESTS_PLUGIN_DIR );
				$base_url    = content_url( 'plugins/' . $plugin_slug );
				if ( $rel_path && is_file( $norm_plugin ) ) {
					$base_url = dirname( $base_url . '/' . $rel_path );
				}
				$url = $base_url . ( $path ? '/' . ltrim( $path, '/' ) : '' );
			}
		}
		return $url;
	},
	10,
	3
);

/*
 * Load WP CLI. Its test bootstrap file can't be required as it will load
 * duplicate class names which are already in use.
 */
define( 'WP_CLI_ROOT', TESTS_PLUGIN_DIR . '/vendor/wp-cli/wp-cli' );
define( 'WP_CLI_VENDOR_DIR', TESTS_PLUGIN_DIR . '/vendor' );

/*
 * Load WordPress, which will load the Composer autoload file, and load the MockObject autoloader after that.
 */
WPIntegration\bootstrap_it();

if ( file_exists( WP_CLI_ROOT . '/php/utils.php' ) ) {
	require_once WP_CLI_ROOT . '/php/utils.php';

	$logger = new WP_CLI\Loggers\Regular( true );
	WP_CLI::set_logger( $logger );
}

// Fix up the SERVER_PORT which was just clobbered by tests_reset__SERVER().
if ( defined( 'WP_HOME' ) ) {
	$port = wp_parse_url( WP_HOME, PHP_URL_PORT );
	if ( ! $port ) {
		$port = '80';
	}
	$_SERVER['SERVER_PORT'] = $port;
}
