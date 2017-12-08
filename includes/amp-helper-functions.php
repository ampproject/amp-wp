<?php

/**
 * Get AMP permalink.
 *
 * @since 0.1
 *
 * @param int $post_id Post ID.
 * @return string AMP permalink.
 */
function amp_get_permalink( $post_id ) {

	/**
	 * Filters the AMP permalink to short-circuit normal generation.
	 *
	 * Returning a non-false value in this filter will cause the `get_permalink()` to get called and the `amp_get_permalink` filter to not apply.
	 *
	 * @since 0.4
	 *
	 * @param false $url     Short-circuited URL.
	 * @param int   $post_id Post ID.
	 */
	$pre_url = apply_filters( 'amp_pre_get_permalink', false, $post_id );

	if ( false !== $pre_url ) {
		return $pre_url;
	}

	$parsed_url = wp_parse_url( get_permalink( $post_id ) );
	$structure = get_option( 'permalink_structure' );
	if ( empty( $structure ) || ! empty( $parsed_url['query'] ) || is_post_type_hierarchical( get_post_type( $post_id ) ) ) {
		$amp_url = add_query_arg( AMP_QUERY_VAR, '', get_permalink( $post_id ) );
	} else {
		$amp_url = trailingslashit( get_permalink( $post_id ) ) . user_trailingslashit( AMP_QUERY_VAR, 'single_amp' );
	}

	/**
	 * Filters AMP permalink.
	 *
	 * @since 0.2
	 *
	 * @param false $amp_url AMP URL.
	 * @param int   $post_id Post ID.
	 */
	return apply_filters( 'amp_get_permalink', $amp_url, $post_id );
}

/**
 * Determine whether a given post supports AMP.
 *
 * @since 0.1
 *
 * @param WP_Post $post Post.
 * @return bool Whether the post supports AMP.
 */
function post_supports_amp( $post ) {

	// Because `add_rewrite_endpoint` doesn't let us target specific post_types.
	if ( ! post_type_supports( $post->post_type, AMP_QUERY_VAR ) ) {
		return false;
	}

	// Skip based on postmeta.
	if ( ! isset( $post->ID ) || (bool) get_post_meta( $post->ID, AMP_Post_Meta_Box::DISABLED_POST_META_KEY, true ) ) {
		return false;
	}

	if ( post_password_required( $post ) ) {
		return false;
	}

	/**
	 * Filters whether to skip the post from AMP.
	 *
	 * @since 0.3
	 *
	 * @param bool    $skipped Skipped.
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post.
	 */
	if ( true === apply_filters( 'amp_skip_post', false, $post->ID, $post ) ) {
		return false;
	}

	return true;
}

/**
 * Are we currently on an AMP URL?
 *
 * Note: will always return `false` if called before the `parse_query` hook.
 */
function is_amp_endpoint() {
	if ( 0 === did_action( 'parse_query' ) ) {
		_doing_it_wrong( __FUNCTION__, sprintf( esc_html__( "is_amp_endpoint() was called before the 'parse_query' hook was called. This function will always return 'false' before the 'parse_query' hook is called.", 'amp' ) ), '0.4.2' );
	}

	return false !== get_query_var( AMP_QUERY_VAR, false );
}

function amp_get_asset_url( $file ) {
	return plugins_url( sprintf( 'assets/%s', $file ), AMP__FILE__ );
}
