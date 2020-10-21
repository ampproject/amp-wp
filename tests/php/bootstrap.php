// bootstrap.php
<?php
/**
 * Tbootstrap.
 *
 * @since 1.0.0
 */

define( 'TESTS_PLUGIN_DIR', __DIR__ );

if ( false !== getenv( 'WP_PLUGIN_DIR' ) ) {
	define( 'WP_PLUGIN_DIR', getenv( 'WP_PLUGIN_DIR' ) );
} else {
	define( 'WP_PLUGIN_DIR', dirname( TESTS_PLUGIN_DIR ) );
}

if ( ! defined( 'AMP__VERSION' ) ) {
	define( 'AMP__VERSION', 1 );
}

// Detect where to load the WordPress tests environment from.
if ( false !== getenv( 'WP_TESTS_DIR' ) ) {
	$_test_root = getenv( 'WP_TESTS_DIR' );
} elseif ( false !== getenv( 'WP_DEVELOP_DIR' ) ) {
	$_test_root = getenv( 'WP_DEVELOP_DIR' ) . '/tests/phpunit';
} elseif ( file_exists( '/tmp/wordpress-tests-lib/includes/bootstrap.php' ) ) {
	$_test_root = '/tmp/wordpress-tests-lib';
} else {
	$_test_root = dirname( dirname( dirname( dirname( TESTS_PLUGIN_DIR ) ) ) ) . '/tests/phpunit';
}

$GLOBALS['wp_tests_options'] = array(
	'active_plugins' => array( basename( TESTS_PLUGIN_DIR ) . '/amp.php' ),
);

// Start up the WP testing environment.
require $_test_root . '/includes/bootstrap.php';