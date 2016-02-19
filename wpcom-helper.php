<?php

// WPCOM-specific things

// Add stats pixel
add_filter( 'amp_post_template_footer', 'jetpack_amp_add_stats_pixel' );

function jetpack_amp_add_stats_pixel( $amp_template ) {
	?>
	<amp-pixel src="<?php echo esc_url( wpcom_amp_get_pageview_url() ); ?>"></amp-pixel>
	<amp-pixel src="<?php echo esc_url( wpcom_amp_get_mc_url() ); ?>"></amp-pixel>
	<amp-pixel src="<?php echo esc_url( wpcom_amp_get_stats_extras_url() ); ?>"></amp-pixel>
	<?php
}

function wpcom_amp_get_pageview_url() {
	$stats_info = stats_collect_info();
	$a = $stats_info['st_go_args'];

	$url = add_query_arg( array(
		'rand' => 'RANDOM', // AMP placeholder
		'host' => rawurlencode( $_SERVER['HTTP_HOST'] ),
		'ref' => 'DOCUMENT_REFERRER', // AMP placeholder
	), 'https://pixel.wp.com/b.gif'  );
	$url .= '&' . stats_array_string( $a );
	return $url;
}

function wpcom_amp_get_mc_url() {
	return add_query_arg( array(
		'rand' => 'RANDOM', // special amp placeholder
		'v' => 'wpcom-no-pv',
		'x_amp-views' => 'view',
	), 'https://pixel.wp.com/b.gif' );
}

function wpcom_amp_get_stats_extras_url() {
	$stats_extras = stats_extras();
	if ( ! $stats_extras ) {
		return false;
	}

	$url = add_query_arg( array(
		'rand' => 'RANDOM', // special amp placeholder
		'v' => 'wpcom-no-pv',
	), 'https://pixel.wp.com/b.gif' );

	$url .= '&' . stats_array_string( array(
		'crypt' => base64_encode(
			wp_encrypt_plus(
				ltrim(
					add_query_arg( $stats_extras, ''),
				'?'),
			8, 'url')
		)
	) );

	return $url;
}

add_action( 'pre_amp_render_post', 'jetpack_amp_disable_the_content_filters' );

function jetpack_amp_disable_the_content_filters( $post_id ) {
	add_filter( 'post_flair_disable', '__return_true', 99 );
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
	$metadata = wpcom_amp_add_blavatar( $metadata, $post );
	return $metadata;
}

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

	$size = 60;
	$blavatar_domain = blavatar_domain( site_url() );
	if ( blavatar_exists( $blavatar_domain ) ) {
		$metadata['publisher']['logo'] = array(
			'@type' => 'ImageObject',
			'url' => blavatar_url( $blavatar_domain, 'img', $size, false, true ),
			'width' => $size,
			'height' => $size,
		);
	}

	return $metadata;
}

add_action( 'amp_extract_image_dimensions_callbacks_registered', 'wpcom_amp_extract_image_dimensions_add_custom_callbacks' );
function wpcom_amp_extract_image_dimensions_add_custom_callbacks() {
	// If images are being served from Photon or WP.com files, try extracting the size using querystring.
	add_action( 'amp_extract_image_dimensions', 'wpcom_amp_extract_image_dimensions_from_querystring', 9, 2 ); // Hook in before the default extractors

	// Uses a special wpcom lib (wpcom_getimagesize) to extract dimensions as a last resort if we weren't able to figure them out.
	add_action( 'amp_extract_image_dimensions', 'wpcom_amp_extract_image_dimensions_from_getimagesize', 99, 2 ); // Our last resort, so run late

	// This doesn't work well on WP.com and doesn't scale well for VIP sites (see https://github.com/Automattic/amp-wp/issues/207)
	remove_filter( 'amp_extract_image_dimensions', array( 'AMP_Image_Dimension_Extractor', 'extract_from_attachment_metadata' ) );
	// The wpcom override obviates this one, so take it out.
	remove_filter( 'amp_extract_image_dimensions', array( 'AMP_Image_Dimension_Extractor', 'extract_by_downloading_image' ), 100 );
}

function wpcom_amp_extract_image_dimensions_from_querystring( $dimensions, $url ) {
	if ( is_array( $dimensions ) ) {
		return $dimensions;
	}

	$host = parse_url( $url, PHP_URL_HOST );
	if ( ! wp_endswith( $host, '.wp.com' ) || ! wp_endswith( $host, '.files.wordpress.com' ) ) {
		return false;
	}

	$query = parse_url( $url, PHP_URL_QUERY );
	$w = isset( $query['w'] ) ? absint( $query['w'] ) : false;
	$h = isset( $query['h'] ) ? absint( $query['h'] ) : false;

	if ( false !== $w && false !== $h ) {
		return array( $w, $h );
	}

	return false;
}

function wpcom_amp_extract_image_dimensions_from_getimagesize( $dimensions, $url ) {
	if ( is_array( $dimensions ) ) {
		return $dimensions;
	}

	if ( ! function_exists( 'require_lib' ) ) {
		return false;
	}

	require_lib( 'wpcom/imagesize' );
	$size = wpcom_getimagesize( $url );
	if ( ! is_array( $size ) ) {
		return false;
	}

	return array( $size[0], $size[1] );
}
