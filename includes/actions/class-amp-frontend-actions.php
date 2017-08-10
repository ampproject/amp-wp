<?php
// Callbacks for adding AMP-related things to the main theme

require_once ( AMP__DIR__ . '/includes/actions/class-amp-actions.php' );

class AMP_Frontend_Actions {
	
	public static function register_hooks() {
		add_action( 'wp_head', 'AMP_Frontend_Actions::add_canonical' );
	}
	
	public static function add_canonical() {
		if ( false === apply_filters( 'add_canonical_link', true ) ) {
			return;
		}
		$amp_url = amp_get_permalink( get_queried_object_id() );
		printf( '<link rel="amphtml" href="%s" />', esc_url( $amp_url ) );
	}
}
