<?php
/**
 * Plugin Name: URL Toggles
 * Plugin URI:  https://github.com/ampproject/amp-wp
 * Description: URL toggles to activate or deactivate features via URL arguments.
 * Author:      AMP Project Contributors
 * Author URI:  https://github.com/ampproject/amp-wp/graphs/contributors
 */

$amp_optimizer = filter_input( INPUT_GET, 'amp_optimizer', FILTER_VALIDATE_BOOLEAN );
$amp_ssr       = filter_input( INPUT_GET, 'amp_ssr', FILTER_VALIDATE_BOOLEAN );

if ( null !== $amp_optimizer ) {
	add_filter(
		'amp_enable_optimizer',
		$amp_optimizer
			? '__return_true'
			: '__return_false',
		100
	);
}

if ( null !== $amp_ssr ) {
	add_filter(
		'amp_enable_ssr',
		$amp_ssr
			? '__return_true'
			: '__return_false',
		100
	);
}
