<?php

// WPCOM-specific things
add_action( 'pre_amp_render_post', 'jetpack_amp_disable_the_content_filters' );

// Disable admin menu
add_filter( 'amp_options_menu_is_enabled', '__return_false', 9999 );

/**
 * Disable the_content filters for Jetpack.
 *
 * @param int $post_id Post ID.
 */
function jetpack_amp_disable_the_content_filters( $post_id ) {
	add_filter( 'post_flair_disable', '__return_true', 99 );
	add_filter( 'videopress_show_2015_player', '__return_true' );
	add_filter( 'protected_embeds_use_form_post', '__return_false' );

	remove_filter( 'the_title', 'widont' );

	remove_filter( 'pre_kses', array( 'Filter_Embedded_HTML_Objects', 'filter' ), 11 );
	remove_filter( 'pre_kses', array( 'Filter_Embedded_HTML_Objects', 'maybe_create_links' ), 100 );
}

add_action( 'amp_post_template_head', 'jetpack_amp_add_og_tags' );

function jetpack_amp_add_og_tags( $amp_template ) {
	if ( function_exists( 'jetpack_og_tags' ) ) {
		jetpack_og_tags();
	}
}

add_filter( 'amp_post_template_metadata', 'jetpack_amp_post_template_metadata', 10, 2 );

function jetpack_amp_post_template_metadata( $metadata, $post ) {
	if ( isset( $metadata['publisher'] ) && ! isset( $metadata['publisher']['logo'] ) ) {
		$metadata = wpcom_amp_add_blavatar_to_metadata( $metadata, $post );
	}

	if ( ! isset( $metadata['image'] ) ) {
		$metadata = wpcom_amp_add_image_to_metadata( $metadata, $post );
	}

	return $metadata;
}

function wpcom_amp_add_blavatar_to_metadata( $metadata, $post ) {
	if ( ! function_exists( 'blavatar_domain' ) ) {
		return $metadata;
	}

	$size = 60;
	$metadata['publisher']['logo'] = array(
		'@type' => 'ImageObject',
		'url' => blavatar_url( blavatar_domain( site_url() ), 'img', $size, staticize_subdomain( 'https://wordpress.com/i/favicons/apple-touch-icon-60x60.png' ) ),
		'width' => $size,
		'height' => $size,
	);

	return $metadata;
}

function wpcom_amp_add_image_to_metadata( $metadata, $post ) {
	if ( ! class_exists( 'Jetpack_PostImages' ) ) {
		return wpcom_amp_add_fallback_image_to_metadata( $metadata );
	}

	$image = Jetpack_PostImages::get_image( $post->ID, array(
		'fallback_to_avatars' => true,
		'avatar_size' => 200,
		// AMP already attempts these
		'from_thumbnail' => false,
		'from_attachment' => false,
	) );

	if ( empty( $image ) ) {
		return wpcom_amp_add_fallback_image_to_metadata( $metadata );
	}

	if ( ! isset( $image['src_width'] ) ) {
		$dimensions = wpcom_amp_extract_image_dimensions_from_getimagesize( array(
			$image['src'] => false,
			)
		);

		if ( false !== $dimensions[ $image['src'] ] ) {
			$image['src_width'] = $dimensions['width'];
			$image['src_height'] = $dimensions['height'];
		}
	}

	$metadata['image'] = array(
		'@type' => 'ImageObject',
		'url' => $image['src'],
		'width' => $image['src_width'],
		'height' => $image['src_height'],
	);

	return $metadata;
}

function wpcom_amp_add_fallback_image_to_metadata( $metadata ) {
	$metadata['image'] = array(
		'@type' => 'ImageObject',
		'url' => staticize_subdomain( 'https://wordpress.com/i/blank.jpg' ),
		'width' => 200,
		'height' => 200,
	);

	return $metadata;
}

add_action( 'amp_extract_image_dimensions_batch_callbacks_registered', 'wpcom_amp_extract_image_dimensions_batch_add_custom_callbacks' );
function wpcom_amp_extract_image_dimensions_batch_add_custom_callbacks() {
	// If images are being served from Photon or WP.com files, try extracting the size using querystring.
	add_action( 'amp_extract_image_dimensions_batch', 'wpcom_amp_extract_image_dimensions_from_querystring', 9, 1 ); // Hook in before the default extractors.

	// Uses a special wpcom lib (wpcom_getimagesize) to extract dimensions as a last resort if we weren't able to figure them out.
	add_action( 'amp_extract_image_dimensions_batch', 'wpcom_amp_extract_image_dimensions_from_getimagesize', 99, 1 ); // Our last resort, so run late

	// The wpcom override obviates this one, so take it out.
	remove_filter( 'amp_extract_image_dimensions_batch', array( 'AMP_Image_Dimension_Extractor', 'extract_by_downloading_images' ), 999, 1 );
}

function wpcom_amp_extract_image_dimensions_from_querystring( $dimensions ) {
	foreach ( $dimensions as $url => $value  ) {

		if ( is_array( $value ) ) {
			continue;
		}

		$host = parse_url( $url, PHP_URL_HOST );
		if ( ! wp_endswith( $host, '.wp.com' ) || ! wp_endswith( $host, '.files.wordpress.com' ) ) {
			continue;
		}

		parse_str( parse_url( $url, PHP_URL_QUERY ), $query );
		$w = isset( $query['w'] ) ? absint( $query['w'] ) : false;
		$h = isset( $query['h'] ) ? absint( $query['h'] ) : false;

		if ( false !== $w && false !== $h ) {
			$dimensions[ $url ] = array(
				'width' => $w,
				'height' => $h,
			);
		}
	}
	return $dimensions;
}

function wpcom_amp_extract_image_dimensions_from_getimagesize( $dimensions ) {
	if ( ! function_exists( 'require_lib' ) ) {
		return $dimensions;
	}
	require_lib( 'wpcom/imagesize' );

	foreach ( $dimensions as $url => $value ) {
		if ( is_array( $value ) ) {
			continue;
		}
		$result = wpcom_getimagesize( $url );
		if ( is_array( $result ) ) {
			$dimensions[ $url ] = array(
				'width' => $result[0],
				'height' => $result[1],
			);
		}
	}

	return $dimensions;
}
