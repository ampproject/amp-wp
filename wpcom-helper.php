<?php
/**
 * WPCOM-specific things.
 *
 * @todo Move this into Jetpack. See https://github.com/ampproject/amp-wp/issues/1021
 * @package AMP
 */

// Disable admin menu.
add_filter( 'amp_options_menu_is_enabled', '__return_false', 9999 );

add_action( 'amp_extract_image_dimensions_batch_callbacks_registered', 'wpcom_amp_extract_image_dimensions_batch_add_custom_callbacks' );

/**
 * Add hooks to extract image dimensions.
 *
 * @since 0.5
 */
function wpcom_amp_extract_image_dimensions_batch_add_custom_callbacks() {
	// If images are being served from Photon or WP.com files, try extracting the size using querystring.
	add_action( 'amp_extract_image_dimensions_batch', 'wpcom_amp_extract_image_dimensions_from_querystring', 9, 1 ); // Hook in before the default extractors.

	// Uses a special wpcom lib (wpcom_getimagesize) to extract dimensions as a last resort if we weren't able to figure them out.
	add_action( 'amp_extract_image_dimensions_batch', 'wpcom_amp_extract_image_dimensions_from_getimagesize', 99, 1 ); // Our last resort, so run late.

	// The wpcom override obviates this one, so take it out.
	remove_filter( 'amp_extract_image_dimensions_batch', array( 'AMP_Image_Dimension_Extractor', 'extract_by_downloading_images' ), 999 );
}

/**
 * Extract image dimensions from query string.
 *
 * @since 0.5
 *
 * @param array $dimensions Dimensions.
 * @return array Dimensions.
 */
function wpcom_amp_extract_image_dimensions_from_querystring( $dimensions ) {
	foreach ( $dimensions as $url => $value ) {

		if ( is_array( $value ) ) {
			continue;
		}

		$host = wp_parse_url( $url, PHP_URL_HOST );
		if ( ! wp_endswith( $host, '.wp.com' ) && ! wp_endswith( $host, '.files.wordpress.com' ) ) {
			continue;
		}

		parse_str( wp_parse_url( $url, PHP_URL_QUERY ), $query );
		$w = isset( $query['w'] ) ? absint( $query['w'] ) : false;
		$h = isset( $query['h'] ) ? absint( $query['h'] ) : false;

		if ( false !== $w && false !== $h ) {
			$dimensions[ $url ] = array(
				'width'  => $w,
				'height' => $h,
			);
		}
	}
	return $dimensions;
}

/**
 * Extract image dimensions via wpcom/imagesize.
 *
 * @since 0.5
 *
 * @param array $dimensions Dimensions.
 * @return array Dimensions.
 */
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
				'width'  => $result[0],
				'height' => $result[1],
			);
		}
	}

	return $dimensions;
}
