<?php
/**
 * Plugin Name: Bad Block
 * Description: Block that outputs a script in both its dynamic and static content.
 * Version: 0.1
 */

if ( ! function_exists( 'register_block_type' ) ) {
	return;
}

wp_register_script(
	'bad-block-editor',
	plugins_url( 'block.js', __FILE__ ),
	[ 'wp-element', 'wp-blocks' ],
	'0.1',
	true
);

wp_register_script(
	'bad-block',
	plugins_url( 'front.js', __FILE__ ),
	[],
	'0.1',
	true
);

wp_register_style(
	'bad-block',
	plugins_url( 'style.css', __FILE__ ),
	[],
	'0.1'
);

register_block_type(
	'bad/bad-block',
	[
		'editor_script'   => 'bad-block-editor',
		'script'          => 'bad-block',
		'style'           => ! is_admin() ? 'bad-block' : null,
		'render_callback' => function () {
			return '<span class="wp-block-bad-bad-block"><script>document.write("Bad dynamic block!!!");</script><noscript>Bad dynamic block fallback!</noscript></span>';
		},
	]
);
