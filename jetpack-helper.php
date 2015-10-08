<?php

// Jetpack bits.

add_action( 'pre_amp_render', 'amp_jetpack_disable_photon' );

/**
 * Disables Photon for all images.
 *
 * Photon currently strips the height/width attr from the img tag, which nojoys AMP.
 * For now, let's just disable Photon pending longterm fix.
 *
 **/
function amp_jetpack_disable_photon() {
	add_filter( 'jetpack_photon_skip_image', '__return_true' );
}