<?php
// Callbacks for adding AMP-related things to the admin.

/** AMP_Template_Customizer class */
require_once( AMP__DIR__ . '/includes/admin/class-amp-customizer.php' );

/**
 * Instantiates the AMP Template editor for the Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Customizer instance.
 */
function init_amp_template_customizer( $wp_customize ) {
	AMP_Template_Customizer::init( $wp_customize );
}
add_action( 'customize_register', 'init_amp_template_customizer', 500 );

/**
 * Registers a submenu page to access the AMP template editor panel in the Customizer.
 */
function amp_customizer_editor_link() {
	$post_id = get_posts( array(
		'post_status'     => 'publish',
		'post_type'       => 'post',
		'orderby'         => 'rand',
		'posts_per_page'  => 1,
		'fields'          => 'ids',
		'supress_filters' => false
	) );

	// Teensy little hack on menu_slug, but it works. No redirect!
	$menu_slug = add_query_arg( array(
		'autofocus[panel]' => 'amp_template_editor',
		'url'              => rawurlencode( amp_get_permalink( $post_id ) ),
		'return'           => rawurlencode( admin_url() )
	), 'customize.php' );

	$page = add_theme_page(
		__( 'AMP Templates', 'amp' ),
		__( 'AMP Templates', 'amp' ),
		'edit_theme_options',
		$menu_slug
	);
}
add_action( 'admin_menu', 'amp_customizer_editor_link' );
