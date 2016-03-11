<?php
// Callbacks for adding AMP-related things to the main theme

add_action( 'wp_head', 'amp_frontend_add_canonical' );

function amp_frontend_add_canonical() {
	if ( false === apply_filters( 'amp_frontend_show_canonical', true ) ) {
		return;
	}

    if ( is_home() ) {
        printf( '<link rel="amphtml" href="%s" />', esc_url( home_url().'/amp/' ) );
    }
    elseif( is_category() || is_tag() || is_author() ) {
        $term = get_queried_object();
        if ($term instanceof WP_Term) {
            $amp_url = get_term_link( $term );
            printf( '<link rel="amphtml" href="%s" />', esc_url( amp_term_link($amp_url, $term, $term->taxonomy ) ) );
        }
        elseif($term instanceof WP_User) {
            $amp_url = get_author_posts_url( $term->ID );
            amp_author_link($amp_url, $term->ID, $term );
            printf( '<link rel="amphtml" href="%s" />', esc_url( amp_author_link($amp_url, $term->ID, $term ) ) );   
        }
    }
    else {
        $amp_url = amp_get_permalink( get_queried_object_id() );
        printf( '<link rel="amphtml" href="%s" />', esc_url( $amp_url ) );
    }

}
