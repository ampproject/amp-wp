<?php
/**
 * PHPUnit bootstrap file.
 */

$amp_plugin_root = dirname( dirname( __DIR__ ) );

/**
 * Include bootstrap file from wp-dev-lib.
 */
require_once $amp_plugin_root . '/vendor/xwp/wp-dev-lib/sample-config/phpunit-plugin-bootstrap.php';

/**
 * Load WP CLI. Its test bootstrap file can't be required as it will load
 * duplicate class names which are already in use.
 */
define( 'WP_CLI_ROOT', $amp_plugin_root . '/vendor/wp-cli/wp-cli' );
define( 'WP_CLI_VENDOR_DIR', $amp_plugin_root . '/vendor' );
require_once WP_CLI_ROOT . '/php/utils.php';
