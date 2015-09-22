<?php

// WPCOM-specific things

add_action( 'pre_amp_render', function() {
	add_filter( 'post_flair_disable', '__return_true', 99 );
} );

add_action( 'amp_head', function( $amp_post ) {
	if ( function_exists( 'jetpack_og_tags' ) ) {
		jetpack_og_tags();
	}
} );

add_filter( 'amp_post_metadata', function( $metadata, $post ) {
	$metadata = wpcom_amp_add_blavatar( $metadata, $post );
	return $metadata;
}, 10, 2 );

function wpcom_amp_add_blavatar( $metadata, $post ) {
	if ( ! function_exists( 'blavatar_domain' ) ) {
		return $metadata;
	}

	if ( ! isset( $metadata['publisher'] ) ) {
		return $metadata;
	}

	if ( isset( $metadata['publisher']['logo'] ) ) {
		return $metadata;
	}

	$size = 200;
	$blavatar_domain = blavatar_domain( site_url() );
	if ( blavatar_exists( $blavatar_domain ) ) {
		$metadata['logo'] = array(
			'@type' => 'ImageObject',
			'url' => blavatar_url( $blavatar_domain, 'img', $size, false, true ),
			'width' => $size,
			'height' => $size,
		);
	}

	return $metadata;
}
