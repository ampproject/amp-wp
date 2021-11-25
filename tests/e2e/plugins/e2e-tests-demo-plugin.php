<?php
/**
 * Plugin Name: E2E Tests Demo Plugin
 * Plugin URI:  https://github.com/ampproject/amp-wp
 * Description: Demo Plugin that can be installed during E2E tests.
 * Author:      AMP Project Contributors
 * Author URI:  https://github.com/ampproject/amp-wp/graphs/contributors
 */

add_action( 'wp_footer', function () {
	?>
	<bad-tag></bad-tag>
	<?php
} );

add_shortcode( 'bad-tag-shortcode', function () {
	return '<bad-tag-shortcode></bad-tag-shortcode>';
} );
