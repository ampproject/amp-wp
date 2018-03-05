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

	if ( amp_is_canonical() ) {
		$amp_url = get_permalink( $post_id );
	} else {
		$parsed_url = wp_parse_url( get_permalink( $post_id ) );
		$structure  = get_option( 'permalink_structure' );
		if ( empty( $structure ) || ! empty( $parsed_url['query'] ) || is_post_type_hierarchical( get_post_type( $post_id ) ) ) {
			$amp_url = add_query_arg( AMP_QUERY_VAR, '', get_permalink( $post_id ) );
		} else {
			$amp_url = trailingslashit( get_permalink( $post_id ) ) . user_trailingslashit( AMP_QUERY_VAR, 'single_amp' );
		}
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
 * Remove the AMP endpoint (and query var) from a given URL.
 *
 * @since 0.7
 *
 * @param string $url URL.
 * @return string URL with AMP stripped.
 */
function amp_remove_endpoint( $url ) {

	// Strip endpoint.
	$url = preg_replace( ':/' . preg_quote( AMP_QUERY_VAR, ':' ) . '(?=/?(\?|#|$)):', '', $url );

	// Strip query var.
	$url = remove_query_arg( AMP_QUERY_VAR, $url );

	return $url;
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
 * Get AMP boilerplate code.
 *
 * @since 0.7
 * @link https://www.ampproject.org/docs/reference/spec#boilerplate
 * @return string Boilerplate code.
 */
function amp_get_boilerplate_code() {
	return '<style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style>'
		. '<noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>';
}

/**
 * Retrieve analytics data added in backend.
 *
 * @since 0.7
 *
 * @param array $analytics Analytics entries.
 * @return array Analytics.
 */
function amp_get_analytics( $analytics = array() ) {
	$analytics_entries = AMP_Options_Manager::get_option( 'analytics', array() );

	/**
	 * Add amp-analytics tags.
	 *
	 * This filter allows you to easily insert any amp-analytics tags without needing much heavy lifting.
	 * This filter should be used to alter entries for paired mode.
	 *
	 * @since 0.7
	 *
	 * @param array $analytics_entries An associative array of the analytics entries we want to output. Each array entry must have a unique key, and the value should be an array with the following keys: `type`, `attributes`, `script_data`. See readme for more details.
	 */
	$analytics_entries = apply_filters( 'amp_analytics_entries', $analytics_entries );

	if ( ! $analytics_entries ) {
		return $analytics;
	}

	foreach ( $analytics_entries as $entry_id => $entry ) {
		$analytics[ $entry_id ] = array(
			'type'        => $entry['type'],
			'attributes'  => array(),
			'config_data' => json_decode( $entry['config'] ),
		);
	}

	return $analytics;
}

/**
 * Print analytics data.
 *
 * @since 0.7
 *
 * @param array $analytics Analytics entries.
 */
function amp_print_analytics( $analytics ) {
	$analytics_entries = amp_get_analytics( $analytics );

	if ( empty( $analytics_entries ) ) {
		return;
	}

	// Can enter multiple configs within backend.
	foreach ( $analytics_entries as $id => $analytics_entry ) {
		if ( ! isset( $analytics_entry['type'], $analytics_entry['attributes'], $analytics_entry['config_data'] ) ) {
			/* translators: %1$s is analytics entry ID, %2$s is actual entry keys. */
			_doing_it_wrong( __FUNCTION__, sprintf( esc_html__( 'Analytics entry for %1$s is missing one of the following keys: `type`, `attributes`, or `config_data` (array keys: %2$s)', 'amp' ), esc_html( $id ), esc_html( implode( ', ', array_keys( $analytics_entry ) ) ) ), '0.3.2' );
			continue;
		}
		$script_element = AMP_HTML_Utils::build_tag( 'script', array(
			'type' => 'application/json',
		), wp_json_encode( $analytics_entry['config_data'] ) );

		$amp_analytics_attr = array_merge( array(
			'id'   => $id,
			'type' => $analytics_entry['type'],
		), $analytics_entry['attributes'] );

		echo AMP_HTML_Utils::build_tag( 'amp-analytics', $amp_analytics_attr, $script_element ); // WPCS: XSS OK.
	}
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
			'AMP_Playlist_Embed_Handler'    => array(),
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
	$sanitizers = apply_filters( 'amp_content_sanitizers',
		array(
			'AMP_Img_Sanitizer'               => array(),
			'AMP_Form_Sanitizer'              => array(),
			'AMP_Comments_Sanitizer'          => array(),
			'AMP_Video_Sanitizer'             => array(),
			'AMP_Audio_Sanitizer'             => array(),
			'AMP_Playbuzz_Sanitizer'          => array(),
			'AMP_Iframe_Sanitizer'            => array(
				'add_placeholder' => true,
			),
			'AMP_Style_Sanitizer'             => array(),
			'AMP_Tag_And_Attribute_Sanitizer' => array(), // Note: This whitelist sanitizer must come at the end to clean up any remaining issues the other sanitizers didn't catch.
		),
		$post
	);

	// Force style sanitizer and whitelist sanitizer to be at end.
	foreach ( array( 'AMP_Style_Sanitizer', 'AMP_Tag_And_Attribute_Sanitizer' ) as $class_name ) {
		if ( isset( $sanitizers[ $class_name ] ) ) {
			$sanitizer = $sanitizers[ $class_name ];
			unset( $sanitizers[ $class_name ] );
			$sanitizers[ $class_name ] = $sanitizer;
		}
	}

	return $sanitizers;
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
