<?php

// before Jetpack ever gets loaded, we need to remove a link rel prefetch for canonical AMP support
// Remove the videopress shortcode as it adds a link rel prefetch that doesn't validate in AMP
function amp_remove_shortcode( $shortcodes ) {

	$jetpack_shortcodes_dir = WP_CONTENT_DIR . '/plugins/jetpack/modules/shortcodes/';

	$shortcodes_to_unload = array( 'videopress.php' );

	foreach ( $shortcodes_to_unload as $shortcode ) {
			if ( $key = array_search( $jetpack_shortcodes_dir . $shortcode, $shortcodes ) ) {
					unset( $shortcodes[$key] );
			}
	}

	return $shortcodes;
}
add_filter( 'jetpack_shortcodes_to_include', 'amp_remove_shortcode' );