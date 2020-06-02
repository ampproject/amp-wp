<?php
/**
 * Plugin Name: Add meta description
 * Plugin URI:  https://github.com/ampproject/amp-wp
 * Description: Adds a <meta> element with the site description, to fix an SEO issue in Lighthouse tests.
 * Author:      AMP Project Contributors
 * Author URI:  https://github.com/ampproject/amp-wp/graphs/contributors
 */

add_action( 'wp_head', static function () {
	echo '<meta name="description" content="Dummy description to make Lighthouse happy.">';
} );
