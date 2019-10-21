<?php
/**
 * This is a wrapper for the actual bootstrapper. The original one determines the plugin to require
 * by looking in the root folder and finding the first PHP file that has a WordPress Header block
 * comment, which unfortunately will be `amp-beta-tester.php`.
 *
 * Thankfully (for us), it keeps track of the plugin file to require in a global variable called
 * `$_plugin_file`. One solution (hack) to fix this is to change the current directory to one where
 * no plugin files can be found, then provide the path to the actual plugin file to require through
 * the aforementioned global variable :D .
 */

global $_plugin_file;
$current_dir = __DIR__;

// Path to plugin to require.
$_plugin_file = $current_dir . '/../../amp.php';

chdir( 'bin' );
require $current_dir . '/../../vendor/xwp/wp-dev-lib/sample-config/phpunit-plugin-bootstrap.php';
