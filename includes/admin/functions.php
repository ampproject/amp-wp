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
	 * Filter the post type to retrieve the latest of for use in the AMP template customizer.
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

	$amp_options = new AMP_Options_Menu();
	$amp_options->init();
}
add_action( 'wp_loaded', 'amp_add_options_menu' );

/**
 * Grab the analytics options from the DB and return $analytics option
 * @return array
 */
function amp_get_analytics_component_fields( $option ) {

	list( $id, $type, $config ) = $option;

	$fields = array();
	$component_index = $type . '-' . $id;
	$fields['id'] = $component_index;
	$fields['type'] = $type;
	$fields['attributes'] = array();

	$analytics_json = json_decode( $config , true );
	$fields['config_data'] = $analytics_json;

	return $fields;
}

function amp_add_custom_analytics( $analytics ) {
	$amp_options = get_option( 'amp-options' );
	$analytics_options = null;
	if ( $amp_options ) {
		$analytics_options = $amp_options['amp-analytics'];
	}

	if ( ! $analytics_options ) {
		return $analytics;
	}

	foreach ( $analytics_options as $option ) {
		$fields = amp_get_analytics_component_fields( $option );
		$analytics[ $fields['id'] ] = $fields;
	}

	return $analytics;
}
add_filter( 'amp_post_template_analytics', 'amp_add_custom_analytics' );
