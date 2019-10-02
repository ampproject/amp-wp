<?php
/**
 * Jetpack bits.
 *
 * @todo Move this into Jetpack. See https://github.com/ampproject/amp-wp/issues/1021
 * @package AMP
 */

add_action( 'template_redirect', 'amp_jetpack_mods', 9 );

/**
 * Disable Jetpack features that are not compatible with AMP.
 *
 * @since 0.2
 */
function amp_jetpack_mods() {
	if ( ! is_amp_endpoint() ) {
		return;
	}
	if ( Jetpack::is_module_active( 'stats' ) ) {
		add_action( 'amp_post_template_footer', 'jetpack_amp_add_stats_pixel' );
	}
	amp_jetpack_disable_sharing();
	amp_jetpack_disable_related_posts();
	add_filter( 'videopress_shortcode_options', 'amp_videopress_enable_freedom_mode' );
}

/**
 * Disable Jetpack sharing.
 *
 * @since 0.3
 */
function amp_jetpack_disable_sharing() {
	add_filter( 'sharing_show', '__return_false', 100 );
}

/**
 * Remove the Related Posts placeholder and headline that gets hooked into the_content
 *
 * That placeholder is useless since we can't ouput, and don't want to output Related Posts in AMP.
 *
 * @since 0.2
 */
function amp_jetpack_disable_related_posts() {
	if ( class_exists( 'Jetpack_RelatedPosts' ) ) {
		$jprp = Jetpack_RelatedPosts::init();
		remove_filter( 'the_content', array( $jprp, 'filter_add_target_to_dom' ), 40 );
	}
}

/**
 * Add Jetpack stats pixel.
 *
 * @since 0.3.2
 */
function jetpack_amp_add_stats_pixel() {
	if ( ! has_action( 'wp_footer', 'stats_footer' ) ) {
		return;
	}
	?>
	<amp-pixel src="<?php echo esc_url( jetpack_amp_build_stats_pixel_url() ); ?>"></amp-pixel>
	<?php
}

/**
 * Generate the stats pixel.
 *
 * Looks something like:
 *     https://pixel.wp.com/g.gif?v=ext&j=1%3A3.9.1&blog=1234&post=5678&tz=-4&srv=example.com&host=example.com&ref=&rand=0.4107963021218808
 *
 * @since 0.3.2
 */
function jetpack_amp_build_stats_pixel_url() {
	global $wp_the_query;
	if ( function_exists( 'stats_build_view_data' ) ) { // Added in <https://github.com/Automattic/jetpack/pull/3445>.
		$data = stats_build_view_data();
	} else {
		$blog     = Jetpack_Options::get_option( 'id' );
		$tz       = get_option( 'gmt_offset' );
		$v        = 'ext';
		$blog_url = wp_parse_url( site_url() );
		$srv      = $blog_url['host'];
		$j        = sprintf( '%s:%s', JETPACK__API_VERSION, JETPACK__VERSION );
		$post     = $wp_the_query->get_queried_object_id();
		$data     = compact( 'v', 'j', 'blog', 'post', 'tz', 'srv' );
	}

	$data['host'] = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : ''; // input var ok.
	$data['rand'] = 'RANDOM'; // AMP placeholder.
	$data['ref']  = 'DOCUMENT_REFERRER'; // AMP placeholder.
	$data         = array_map( 'rawurlencode', $data );
	return add_query_arg( $data, 'https://pixel.wp.com/g.gif' );
}

/**
 * Force videopress to use html5 player that would generate <video /> tag
 * that will be later converted to <amp-video />
 *
 * @since 0.7.1
 *
 * @param array $options videopress shortcode options.
 * @return array videopress shortcode options with `freedom` set to true
 */
function amp_videopress_enable_freedom_mode( $options ) {
	$options['freedom'] = true;
	return $options;
}
