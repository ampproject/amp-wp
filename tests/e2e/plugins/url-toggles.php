<?php
/**
 * Plugin Name: URL Toggles
 * Plugin URI:  https://github.com/ampproject/amp-wp
 * Description: URL toggles to activate or deactivate features via URL arguments.
 * Author:      AMP Project Contributors
 * Author URI:  https://github.com/ampproject/amp-wp/graphs/contributors
 */

if ( isset( $_GET['amp_optimizer'] ) ) {
	add_filter(
		'amp_enable_optimizer',
		rest_sanitize_boolean( $_GET['amp_optimizer'] )
			? '__return_true'
			: '__return_false',
		100
	);
}

if ( isset( $_GET['amp_ssr'] ) ) {
	add_filter(
		'amp_enable_ssr',
		rest_sanitize_boolean( $_GET['amp_ssr'] )
			? '__return_true'
			: '__return_false',
		100
	);
}
