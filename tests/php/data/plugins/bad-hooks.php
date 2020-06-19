<?php
/**
 * Plugin Name: Bad Hooks
 * Description: Action and filter which add bad markup.
 */

add_action(
	'wp_footer',
	function () {
		echo '<script>document.write("Bad action!!!");</script><noscript>Bad action!</noscript>';
	}
);

add_filter(
	'the_content',
	function ( $content ) {
		return $content . '<script>document.write("Bad filter!!!");</script><noscript>Bad filter!</noscript>';
	}
);
