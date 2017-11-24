<?php
/**
 * AMP Post types support.
 *
 * @package AMP
 * @since 0.6
 */

/**
 * Declare core post types support.
 *
 * @since 0.6
 */
function amp_core_post_types_support() {
	add_post_type_support( 'post', AMP_QUERY_VAR );
}
add_action( 'init', 'amp_core_post_types_support' );

/**
 * Declare custom post types support.
 *
 * This function should only be invoked through the 'after_setup_theme' action to
 * allow plugins/theme to overwrite the post types support.
 *
 * @since 0.6
 */
function amp_custom_post_types_support() {
	// Listen to post types settings.
	foreach ( AMP_Settings_Post_Types::get_instance()->get_settings() as $post_type => $enabled ) {
		if ( true === $enabled ) {
			add_post_type_support( $post_type, AMP_QUERY_VAR );
		}
	}
}
add_action( 'after_setup_theme', 'amp_custom_post_types_support' );
