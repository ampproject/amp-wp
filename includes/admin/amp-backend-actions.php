<?php
// Callbacks for adding AMP-related things to the admin.

/** AMP_Template_Customizer class */
require_once( AMP__DIR__ . '/includes/admin/class-amp-customizer.php' );

/**
 * Sets up the AMP template editor for the Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Customizer instance.
 */
function amp_init_customizer( $wp_customize ) {
	AMP_Template_Customizer::init( $wp_customize );
}

/**
 * Registers a submenu page to access the AMP template editor panel in the Customizer.
 */
function amp_add_customizer_link() {
	/**
	 * Filter the post type to retrieve the latest of for use in the AMP template customizer.
	 *
	 * @param string $post_type Post type slug. Default 'post'.
	 */
	$post_type = (string) apply_filters( 'amp_customizer_post_type', 'post' );

	// If the post type doesn't support AMP, bail.
	if ( ! post_type_supports( $post_type, 'amp' ) ) {
		return;
	}

	$post_id = get_posts( array(
		'post_status'      => 'publish',
		'post_type'        => $post_type,
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
