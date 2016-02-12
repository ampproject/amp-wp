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
add_action( 'customize_register', 'init_amp_template_customizer', 50 );

/**
 * Outputs a temporary link for accessing the Customizer URL (for testing purposes).
 *
 * @return string HTML markup for a Customizer link.
 */
function amp_template_customizer_link() {
	$latest_post = get_posts( array(
		'post_status'     => 'publish',
		'post_type'       => 'post',
		'posts_per_page'  => 1,
		'fields'          => 'ids',
		'supress_filters' => false
	) );

	// Bail if there's nothing to link to.
	if ( ! $latest_post ) {
		return '';
	} else {
		$url = add_query_arg( array(
			'autofocus[panel]' => 'amp_template_editor',
			'url'              => rawurlencode( amp_get_permalink( $latest_post ) ),
			'return'           => rawurlencode( admin_url() )
		), wp_customize_url() );

		printf( '<a href="%1$s">%2$s</a>', esc_url( $url ), __( 'Edit AMP Templates', 'amp' ) );
	}
}
add_action( 'activity_box_end', 'amp_template_customizer_link' );
