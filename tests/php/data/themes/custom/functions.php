<?php

if ( ! function_exists( 'my_custom_after_setup_theme' ) ) {

	function my_custom_after_setup_theme() {
		add_theme_support( 'amp' );
	}
}

add_action(
	'trigger_action_to_execute',
	static function () {
		do_action( 'execute_from_within_theme' );
	}
);
