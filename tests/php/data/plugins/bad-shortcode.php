<?php
/**
 * Plugin Name: Bad Shortcode
 * Description: Shortcode which outputs a script.
 * Version: 0.4
 */

add_action(
	'wp_enqueue_scripts',
	function () {
		wp_register_script( 'bad-shortcode', home_url( '/bad-shortcode.js' ), [ 'jquery' ], '1', true );
	}
);

add_shortcode(
	'bad',
	function() {
		wp_enqueue_script( 'bad-shortcode' );
		return '<script>document.write("Bad shortcode!!!");</script><noscript>Bad shortcode fallback!</noscript>';
	}
);
