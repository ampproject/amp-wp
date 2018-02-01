<?php
/**
 * AMP Helper Functions
 *
 * @package AMP
 */

/**
 * Retrieves the full AMP-specific permalink for the given post ID.
 *
 * @since 0.1
 *
 * @param int $post_id Post ID.
 *
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
	$structure  = get_option( 'permalink_structure' );
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
 * @since 0.6 Returns false when post has meta to disable AMP.
 * @see   AMP_Post_Type_Support::get_support_errors()
 *
 * @param WP_Post $post Post.
 *
 * @return bool Whether the post supports AMP.
 */
function post_supports_amp( $post ) {
	if ( amp_is_canonical() ) {
		return true;
	}

	$errors = AMP_Post_Type_Support::get_support_errors( $post );

	// Return false if an error is found.
	if ( ! empty( $errors ) ) {
		return false;
	}

	switch ( get_post_meta( $post->ID, AMP_Post_Meta_Box::STATUS_POST_META_KEY, true ) ) {
		case AMP_Post_Meta_Box::ENABLED_STATUS:
			return true;

		case AMP_Post_Meta_Box::DISABLED_STATUS:
			return false;

		// Disabled by default for custom page templates, page on front and page for posts.
		default:
			$enabled = (
				! (bool) get_page_template_slug( $post )
				&&
				! (
					'page' === $post->post_type
					&&
					'page' === get_option( 'show_on_front' )
					&&
					in_array( (int) $post->ID, array(
						(int) get_option( 'page_on_front' ),
						(int) get_option( 'page_for_posts' ),
					), true )
				)
			);

			/**
			 * Filters whether default AMP status should be enabled or not.
			 *
			 * @since 0.6
			 *
			 * @param string  $status Status.
			 * @param WP_Post $post   Post.
			 */
			return apply_filters( 'amp_post_status_default_enabled', $enabled, $post );
	}
}

/**
 * Are we currently on an AMP URL?
 *
 * Note: will always return `false` if called before the `parse_query` hook.
 *
 * @return bool Whether it is the AMP endpoint.
 */
function is_amp_endpoint() {
	if ( amp_is_canonical() ) {
		return true;
	}

	if ( 0 === did_action( 'parse_query' ) ) {
		_doing_it_wrong( __FUNCTION__, sprintf( esc_html__( "is_amp_endpoint() was called before the 'parse_query' hook was called. This function will always return 'false' before the 'parse_query' hook is called.", 'amp' ) ), '0.4.2' );
	}

	return false !== get_query_var( AMP_QUERY_VAR, false );
}

/**
 * Get AMP asset URL.
 *
 * @param string $file Relative path to file in assets directory.
 * @return string URL.
 */
function amp_get_asset_url( $file ) {
	return plugins_url( sprintf( 'assets/%s', $file ), AMP__FILE__ );
}

/**
 * Print AMP boilerplate code.
 *
 * @link https://www.ampproject.org/docs/reference/spec#boilerplate
 */
function amp_print_boilerplate_code() {
	echo '<style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style>';
	echo '<noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>';
}

/**
 * Get content embed handlers.
 *
 * @since 0.7
 *
 * @param WP_Post $post Post that the content belongs to. Deprecated when theme supports AMP, as embeds may apply
 *                      to non-post data (e.g. Text widget).
 * @return array Embed handlers.
 */
function amp_get_content_embed_handlers( $post = null ) {
	if ( current_theme_supports( 'amp' ) && $post ) {
		_deprecated_argument( __FUNCTION__, '0.7', esc_html__( 'The $post argument is deprecated when theme supports AMP.', 'amp' ) );
		$post = null;
	}

	/**
	 * Filters the content embed handlers.
	 *
	 * @since 0.2
	 * @since 0.7 Deprecated $post parameter.
	 *
	 * @param array   $handlers Handlers.
	 * @param WP_Post $post     Post. Deprecated. It will be null when `amp_is_canonical()`.
	 */
	return apply_filters( 'amp_content_embed_handlers',
		array(
			'AMP_Twitter_Embed_Handler'     => array(),
			'AMP_YouTube_Embed_Handler'     => array(),
			'AMP_DailyMotion_Embed_Handler' => array(),
			'AMP_Vimeo_Embed_Handler'       => array(),
			'AMP_SoundCloud_Embed_Handler'  => array(),
			'AMP_Instagram_Embed_Handler'   => array(),
			'AMP_Issuu_Embed_Handler'       => array(),
			'AMP_Meetup_Embed_Handler'      => array(),
			'AMP_Vine_Embed_Handler'        => array(),
			'AMP_Facebook_Embed_Handler'    => array(),
			'AMP_Pinterest_Embed_Handler'   => array(),
			'AMP_Reddit_Embed_Handler'      => array(),
			'AMP_Tumblr_Embed_Handler'      => array(),
			'AMP_Gallery_Embed_Handler'     => array(),
			'WPCOM_AMP_Polldaddy_Embed'     => array(),
		),
		$post
	);
}

/**
 * Get content sanitizers.
 *
 * @since 0.7
 *
 * @param WP_Post $post Post that the content belongs to. Deprecated when theme supports AMP, as sanitizers apply
 *                      to non-post data (e.g. Text widget).
 * @return array Embed handlers.
 */
function amp_get_content_sanitizers( $post = null ) {
	if ( current_theme_supports( 'amp' ) && $post ) {
		_deprecated_argument( __FUNCTION__, '0.7', esc_html__( 'The $post argument is deprecated when theme supports AMP.', 'amp' ) );
		$post = null;
	}

	/**
	 * Filters the content sanitizers.
	 *
	 * @since 0.2
	 * @since 0.7 Deprecated $post parameter. It will be null when `amp_is_canonical()`.
	 *
	 * @param array   $handlers Handlers.
	 * @param WP_Post $post     Post. Deprecated.
	 */
	return apply_filters( 'amp_content_sanitizers',
		array(
			'AMP_Style_Sanitizer'             => array(),
			'AMP_Img_Sanitizer'               => array(),
			'AMP_Form_Sanitizer'              => array(),
			'AMP_Comments_Sanitizer'          => array(),
			'AMP_Video_Sanitizer'             => array(),
			'AMP_Audio_Sanitizer'             => array(),
			'AMP_Playbuzz_Sanitizer'          => array(),
			'AMP_Iframe_Sanitizer'            => array(
				'add_placeholder' => true,
			),
			'AMP_Tag_And_Attribute_Sanitizer' => array(), // Note: This whitelist sanitizer must come at the end to clean up any remaining issues the other sanitizers didn't catch.
		),
		$post
	);
}

/**
 * Grabs featured image or the first attached image for the post.
 *
 * @since 0.7 This originally was located in the private method AMP_Post_Template::get_post_image_metadata().
 *
 * @param WP_Post|int $post Post or post ID.
 * @return array|false $post_image_meta Post image metadata, or false if not found.
 */
function amp_get_post_image_metadata( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return false;
	}

	$post_image_meta = null;
	$post_image_id   = false;

	if ( has_post_thumbnail( $post->ID ) ) {
		$post_image_id = get_post_thumbnail_id( $post->ID );
	} else {
		$attached_image_ids = get_posts(
			array(
				'post_parent'      => $post->ID,
				'post_type'        => 'attachment',
				'post_mime_type'   => 'image',
				'posts_per_page'   => 1,
				'orderby'          => 'menu_order',
				'order'            => 'ASC',
				'fields'           => 'ids',
				'suppress_filters' => false,
			)
		);

		if ( ! empty( $attached_image_ids ) ) {
			$post_image_id = array_shift( $attached_image_ids );
		}
	}

	if ( ! $post_image_id ) {
		return false;
	}

	$post_image_src = wp_get_attachment_image_src( $post_image_id, 'full' );

	if ( is_array( $post_image_src ) ) {
		$post_image_meta = array(
			'@type'  => 'ImageObject',
			'url'    => $post_image_src[0],
			'width'  => $post_image_src[1],
			'height' => $post_image_src[2],
		);
	}

	return $post_image_meta;
}

/**
 * Get schema.org metadata for the current query.
 *
 * @since 0.7
 * @see AMP_Post_Template::build_post_data() Where the logic in this function originally existed.
 *
 * @return array $metadata All schema.org metadata for the post.
 */
function amp_get_schemaorg_metadata() {
	$metadata = array(
		'@context'  => 'http://schema.org',
		'publisher' => array(
			'@type' => 'Organization',
			'name'  => get_bloginfo( 'name' ),
		),
	);

	/**
	 * Filters the site icon used in AMP responses.
	 *
	 * In general the `get_site_icon_url` filter should be used instead.
	 *
	 * @since 0.3
	 * @todo Why is the size set to 32px?
	 *
	 * @param string $site_icon_url
	 */
	$site_icon_url = apply_filters( 'amp_site_icon_url', get_site_icon_url( AMP_Post_Template::SITE_ICON_SIZE ) );
	if ( $site_icon_url ) {
		$metadata['publisher']['logo'] = array(
			'@type'  => 'ImageObject',
			'url'    => $site_icon_url,
			'height' => AMP_Post_Template::SITE_ICON_SIZE,
			'width'  => AMP_Post_Template::SITE_ICON_SIZE,
		);
	}

	$post = get_queried_object();
	if ( $post instanceof WP_Post ) {
		$metadata = array_merge(
			$metadata,
			array(
				'@type'            => is_page() ? 'WebPage' : 'BlogPosting',
				'mainEntityOfPage' => get_permalink(),
				'headline'         => get_the_title(),
				'datePublished'    => date( 'c', get_the_date( 'U', $post->ID ) ),
				'dateModified'     => date( 'c', get_the_date( 'U', $post->ID ) ),
			)
		);

		$post_author = get_userdata( $post->post_author );
		if ( $post_author ) {
			$metadata['author'] = array(
				'@type' => 'Person',
				'name'  => html_entity_decode( $post_author->display_name, ENT_QUOTES, get_bloginfo( 'charset' ) ),
			);
		}

		$image_metadata = amp_get_post_image_metadata( $post );
		if ( $image_metadata ) {
			$metadata['image'] = $image_metadata;
		}

		/**
		 * Filters Schema.org metadata for a post.
		 *
		 * The 'post_template' in the filter name here is due to this filter originally being introduced in `AMP_Post_Template`.
		 * In general the `amp_schemaorg_metadata` filter should be used instead.
		 *
		 * @since 0.3
		 *
		 * @param array   $metadata Metadata.
		 * @param WP_Post $post     Post.
		 */
		$metadata = apply_filters( 'amp_post_template_metadata', $metadata, $post );
	}

	/**
	 * Filters Schema.org metadata for a query.
	 *
	 * Check the the main query for the context for which metadata should be added.
	 *
	 * @since 0.7
	 *
	 * @param array   $metadata Metadata.
	 */
	$metadata = apply_filters( 'amp_schemaorg_metadata', $metadata );

	return $metadata;
}

/**
 * Output schema.org metadata.
 *
 * @since 0.7
 */
function amp_print_schemaorg_metadata() {
	$metadata = amp_get_schemaorg_metadata();
	if ( empty( $metadata ) ) {
		return;
	}
	?>
	<script type="application/ld+json"><?php echo wp_json_encode( $metadata ); ?></script>
	<?php
}

/**
 * Hook into a comment submission of an AMP XHR post request.
 *
 * This only runs on wp-comments-post.php.
 *
 * @since 0.7.0
 */
function amp_prepare_xhr_post() {
	global $pagenow;
	if ( ! isset( $_GET['__amp_source_origin'] ) || ! isset( $pagenow ) ) { // WPCS: CSRF ok. Beware of AMP_Theme_Support::purge_amp_query_vars().
		return;
	}

	if ( 'wp-comments-post.php' === $pagenow ) {
		// This only runs on wp-comments-post.php.
		add_filter( 'comment_post_redirect', function() {
			// We don't need any data, so just send a success.
			wp_send_json_success();
		}, PHP_INT_MAX, 2 );
	} elseif ( ! isset( $_GET['_wp_amp_action_xhr_converted'] ) ) { // WPCS: CSRF ok.
		// submission was from a set action-xhr, implying it's being handled already.
		return;
	} else {
		// Add amp redirect hooks.
		add_filter( 'wp_redirect', 'amp_handle_general_post', PHP_INT_MAX, 2 );
		add_action( 'template_redirect', function() {
			/*
			 * Buffering starts here, so unlikely the form has a redirect,
			 * so force a redirect to the same page.
			 */
			$location = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ); // WPCS: CSRF ok, input var ok.
			amp_handle_general_post( $location );
		}, 0 );
	}
	// Add die handler for AMP error display.
	add_filter( 'wp_die_handler', function() {
		/**
		 * New error handler for AMP form submission.
		 *
		 * @param WP_Error|string $error The error to handle.
		 */
		return function( $error ) {
			status_header( 400 );
			if ( is_wp_error( $error ) ) {
				$error = $error->get_error_message();
			}
			$error = strip_tags( $error, 'strong' );
			wp_send_json( compact( 'error' ) );
		};
	} );

	// Send AMP header.
	$origin = esc_url_raw( wp_unslash( $_GET['__amp_source_origin'] ) ); // WPCS: CSRF ok.
	header( 'AMP-Access-Control-Allow-Source-Origin: ' . $origin, true );
}

/**
 * Handle a general, non comment AMP XHR post.
 *
 * @since 0.7.0
 * @param string $location The location to redirect to.
 */
function amp_handle_general_post( $location ) {

	$url = site_url( $location );
	header( 'AMP-Redirect-To: ' . $url );
	header( 'Access-Control-Expose-Headers: AMP-Redirect-To;' );
	// Send json success as no data is required.
	wp_send_json_success();
}
