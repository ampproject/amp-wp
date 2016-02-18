<?php

// Jetpack bits.

add_action( 'pre_amp_render_post', 'amp_jetpack_mods' );

/**
 * Disable Jetpack features that are not compatible with AMP.
 *
 **/
function amp_jetpack_mods() {
	amp_jetpack_disable_sharing();
	amp_jetpack_disable_related_posts();
}

function amp_jetpack_disable_sharing() {
	add_filter( 'sharing_show', '__return_false', 100 );
}

/**
 * Remove the Related Posts placeholder and headline that gets hooked into the_content
 *
 * That placeholder is useless since we can't ouput, and don't want to output Related Posts in AMP.
 *
 **/
function amp_jetpack_disable_related_posts() {
	if ( class_exists( 'Jetpack_RelatedPosts' ) ) {
		$jprp = Jetpack_RelatedPosts::init();
		remove_filter( 'the_content', array( $jprp, 'filter_add_target_to_dom' ), 40 );
	}
}
