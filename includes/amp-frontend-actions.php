<?php
/**
 * Callbacks for adding AMP-related things to the main theme.
 *
 * @package AMP
 */

add_action( 'wp_head', 'amp_frontend_add_canonical' );

/**
 * Add amphtml link to frontend.
 *
 * @since 0.2
 */
function amp_frontend_add_canonical() {

	/**
	 * Filters whether to show the amphtml link on the frontend.
	 *
	 * @since 0.2
	 */
	if ( false === apply_filters( 'amp_frontend_show_canonical', true ) ) {
		return;
	}

	$amp_url = amp_get_permalink( get_queried_object_id() );
	printf( '<link rel="amphtml" href="%s">', esc_url( $amp_url ) );
}
