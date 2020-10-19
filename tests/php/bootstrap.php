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
 * Load WP CLI.
 */
require_once $amp_plugin_root . '/vendor/wp-cli/wp-cli/tests/bootstrap.php';
