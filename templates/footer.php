<?php
do_action( 'amp_post_template_footer', $this );

if ( is_customize_preview() ) {
	global $wp_customize;
	$wp_customize->customize_preview_settings();
	wp_print_scripts( array( 'customize-preview' ) );
}