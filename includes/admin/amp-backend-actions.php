<?php
// Callbacks for adding AMP-related things to the admin.

/** AMP_Template_Customizer class */
require_once( AMP__DIR__ . '/includes/admin/class-amp-customizer.php' );

/**
 * Filter whether to enable the AMP Customizer features.
 *
 * @param bool $enabled Whether the Customizer panel should be enabled.
 */
$enabled = apply_filters( 'amp_customizer_is_enabled', true );

if ( true === $enabled && class_exists( 'AMP_Template_Customizer' ) ) {
	add_action( 'customize_register', 'init_amp_template_customizer', 500 );
	add_action( 'admin_menu',         'amp_customizer_editor_link'        );
}

/**
 * Instantiates the AMP template editor for the Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Customizer instance.
 */
function init_amp_template_customizer( $wp_customize ) {
	AMP_Template_Customizer::init( $wp_customize );
}

/**
 * Registers a submenu page to access the AMP template editor panel in the Customizer.
 */
function amp_customizer_editor_link() {
	$post_id = get_posts( array(
		'post_status'      => 'publish',
		'post_type'        => 'post',
		'orderby'          => 'rand',
		'posts_per_page'   => 1,
		'fields'           => 'ids',
		'suppress_filters' => false
	) );

	// Teensy little hack on menu_slug, but it works. No redirect!
	$menu_slug = add_query_arg( array(
		'autofocus[panel]' => 'amp_template_editor',
		'url'              => rawurlencode( amp_get_permalink( $post_id ) ),
		'return'           => rawurlencode( admin_url() ),
		'amp'              => true
	), 'customize.php' );

	// Add the theme page.
	$page = add_theme_page(
		__( 'AMP', 'amp' ),
		__( 'AMP', 'amp' ),
		'edit_theme_options',
		$menu_slug
	);
}
