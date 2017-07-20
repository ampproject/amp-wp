<?php
// Callbacks for adding AMP-related things to the admin.

require_once( AMP__DIR__ . '/includes/options/class-amp-options-menu.php' );
require_once( AMP__DIR__ . '/includes/options/views/class-amp-options-manager.php' );

define( 'AMP_CUSTOMIZER_QUERY_VAR', 'customize_amp' );

/**
 * Sets up the AMP template editor for the Customizer.
 */
function amp_init_customizer() {
	require_once( AMP__DIR__ . '/includes/admin/class-amp-customizer.php' );

	// Drop core panels (menus, widgets) from the AMP customizer
	add_filter( 'customize_loaded_components', array( 'AMP_Template_Customizer', '_unregister_core_panels' ) );

	// Fire up the AMP Customizer
	add_action( 'customize_register', array( 'AMP_Template_Customizer', 'init' ), 500 );

	// Add some basic design settings + controls to the Customizer
	add_action( 'amp_init', array( 'AMP_Customizer_Design_Settings', 'init' ) );

	// Add a link to the Customizer
	add_action( 'admin_menu', 'amp_add_customizer_link' );
}

function amp_admin_get_preview_permalink() {
	/**
	 * Filter the post type to retrieve the latest for use in the AMP template customizer.
	 *
	 * @param string $post_type Post type slug. Default 'post'.
	 */
	$post_type = (string) apply_filters( 'amp_customizer_post_type', 'post' );

	if ( ! post_type_supports( $post_type, 'amp' ) ) {
		return;
	}

	$post_ids = get_posts( array(
		'post_status'      => 'publish',
		'post_type'        => $post_type,
		'posts_per_page'   => 1,
		'fields'           => 'ids',
	) );

	if ( empty( $post_ids ) ) {
		return false;
	}

	$post_id = $post_ids[0];

	return amp_get_permalink( $post_id );
}

/**
 * Registers a submenu page to access the AMP template editor panel in the Customizer.
 */
function amp_add_customizer_link() {
	// Teensy little hack on menu_slug, but it works. No redirect!
	$menu_slug = add_query_arg( array(
		'autofocus[panel]'         => AMP_Template_Customizer::PANEL_ID,
		'return'                   => rawurlencode( admin_url() ),
		AMP_CUSTOMIZER_QUERY_VAR   => true,
	), 'customize.php' );

	// Add the theme page.
	add_theme_page(
		__( 'AMP', 'amp' ),
		__( 'AMP', 'amp' ),
		'edit_theme_options',
		$menu_slug
	);
}

/**
 * Registers a top-level menu for AMP configuration options
 */
function amp_add_options_menu() {
	if ( ! is_admin() ) {
		return;
	}

	$show_options_menu = apply_filters( 'amp_options_menu_is_enabled', true );
	if ( true !== $show_options_menu ) {
		return;
	}

	$amp_options = new AMP_Options_Menu();
	$amp_options->init();
}
add_action( 'wp_loaded', 'amp_add_options_menu' );

function amp_add_custom_analytics( $analytics ) {
	$analytics_entries = AMP_Options_Manager::get_option( 'analytics', array() );

	if ( ! $analytics_entries ) {
		return $analytics;
	}

	foreach ( $analytics_entries as $entry_id => $entry ) {
		$analytics[ $entry_id ] = array(
			'type' => $entry['type'],
			'attributes' => array(),
			'config_data' => json_decode( $entry['config'] ),
		);
	}

	return $analytics;
}
add_filter( 'amp_post_template_analytics', 'amp_add_custom_analytics' );
