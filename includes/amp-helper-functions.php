<?php
/**
 * AMP Helper Functions
 *
 * @package AMP
 */

/**
 * Get the slug used in AMP for the query var, endpoint, and post type support.
 *
 * This function always returns 'amp' when 'amp' theme support is present. Otherwise,
 * the return value can be overridden by previously defining a AMP_QUERY_VAR
 * constant or by adding a 'amp_query_var' filter, but *warning* this ability
 * may be deprecated in the future. Normally the slug should be just 'amp'.
 *
 * @since 0.7
 * @since 1.0 The return value is always 'amp' when 'amp' theme support is present, and the 'amp_query_var' filter no longer applies.
 *
 * @return string Slug used for query var, endpoint, and post type support.
 */
function amp_get_slug() {
	if ( current_theme_supports( 'amp' ) ) {
		if ( ! defined( 'AMP_QUERY_VAR' ) ) {
			define( 'AMP_QUERY_VAR', 'amp' );
		} elseif ( 'amp' !== AMP_QUERY_VAR ) {
			_doing_it_wrong( __FUNCTION__, esc_html__( 'The AMP_QUERY_VAR constant should only be defined as "amp" when "amp" theme support is present.', 'amp' ), '1.0' );
		}
		return 'amp';
	}

	if ( defined( 'AMP_QUERY_VAR' ) ) {
		return AMP_QUERY_VAR;
	}

	/**
	 * Filter the AMP query variable.
	 *
	 * Warning: This filter may become deprecated.
	 *
	 * @since 0.3.2
	 * @since 1.0 This filter does not apply when 'amp' theme support is present.
	 *
	 * @param string $query_var The AMP query variable.
	 */
	$query_var = apply_filters( 'amp_query_var', 'amp' );

	define( 'AMP_QUERY_VAR', $query_var );

	return $query_var;
}

/**
 * Get the URL for the current request.
 *
 * This is essentially the REQUEST_URI prefixed by the scheme and host for the home URL.
 * This is needed in particular due to subdirectory installs.
 *
 * @since 1.0
 *
 * @return string Current URL.
 */
function amp_get_current_url() {
	$url = preg_replace( '#(^https?://[^/]+)/.*#', '$1', home_url( '/' ) );
	if ( isset( $_SERVER['REQUEST_URI'] ) ) {
		$url = esc_url_raw( $url . wp_unslash( $_SERVER['REQUEST_URI'] ) );
	} else {
		$url .= '/';
	}
	return $url;
}

/**
 * Retrieves the full AMP-specific permalink for the given post ID.
 *
 * @since 0.1
 * @since 1.0 The query var 'amp' is always used exclusively when 'amp' theme support is present; the 'amp_pre_get_permalink' and 'amp_get_permalink' filters do not apply.
 *
 * @param int $post_id Post ID.
 *
 * @return string AMP permalink.
 */
function amp_get_permalink( $post_id ) {

	// When theme support is present, the plain query var should always be used.
	if ( current_theme_supports( 'amp' ) ) {
		$permalink = get_permalink( $post_id );
		if ( ! amp_is_canonical() ) {
			$permalink = add_query_arg( 'amp', '', $permalink );
		}
		return $permalink;
	}

	/**
	 * Filters the AMP permalink to short-circuit normal generation.
	 *
	 * Returning a non-false value in this filter will cause the `get_permalink()` to get called and the `amp_get_permalink` filter to not apply.
	 *
	 * @since 0.4
	 * @since 1.0 This filter does not apply when 'amp' theme support is present.
	 *
	 * @param false $url     Short-circuited URL.
	 * @param int   $post_id Post ID.
	 */
	$pre_url = apply_filters( 'amp_pre_get_permalink', false, $post_id );

	if ( false !== $pre_url ) {
		return $pre_url;
	}

	$permalink = get_permalink( $post_id );

	if ( amp_is_canonical() ) {
		$amp_url = $permalink;
	} else {
		$parsed_url    = wp_parse_url( get_permalink( $post_id ) );
		$structure     = get_option( 'permalink_structure' );
		$use_query_var = (
			// If pretty permalinks aren't available, then query var must be used.
			empty( $structure )
			||
			// If there are existing query vars, then always use the amp query var as well.
			! empty( $parsed_url['query'] )
			||
			// If the post type is hierarchical then the /amp/ endpoint isn't available.
			is_post_type_hierarchical( get_post_type( $post_id ) )
		);
		if ( $use_query_var ) {
			$amp_url = add_query_arg( amp_get_slug(), '', $permalink );
		} else {
			$amp_url = preg_replace( '/#.*/', '', $permalink );
			$amp_url = trailingslashit( $amp_url ) . user_trailingslashit( amp_get_slug(), 'single_amp' );
			if ( ! empty( $parsed_url['fragment'] ) ) {
				$amp_url .= '#' . $parsed_url['fragment'];
			}
		}
	}

	/**
	 * Filters AMP permalink.
	 *
	 * @since 0.2
	 * @since 1.0 This filter does not apply when 'amp' theme support is present.
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
	$url = preg_replace( ':/' . preg_quote( amp_get_slug(), ':' ) . '(?=/?(\?|#|$)):', '', $url );

	// Strip query var.
	$url = remove_query_arg( amp_get_slug(), $url );

	return $url;
}

/**
 * Add amphtml link.
 *
 * If there are known validation errors for the current URL then do not output anything.
 *
 * @since 1.0
 */
function amp_add_amphtml_link() {

	/**
	 * Filters whether to show the amphtml link on the frontend.
	 *
	 * @todo This filter's name is incorrect. It's not about adding a canonical link but adding the amphtml link.
	 * @since 0.2
	 */
	if ( false === apply_filters( 'amp_frontend_show_canonical', true ) ) {
		return;
	}

	$current_url = amp_get_current_url();

	$amp_url = null;
	if ( current_theme_supports( 'amp' ) ) {
		if ( AMP_Theme_Support::is_paired_available() ) {
			$amp_url = add_query_arg( amp_get_slug(), '', $current_url );
		}
	} else {
		if ( is_singular() ) {
			$amp_url = amp_get_permalink( get_queried_object_id() );
		} else {
			$amp_url = add_query_arg( amp_get_slug(), '', $current_url );
		}
	}

	if ( ! $amp_url ) {
		printf( '<!-- %s -->', esc_html__( 'There is no amphtml version available for this URL.', 'amp' ) );
		return;
	}

	// Check to see if there are known unaccepted validation errors for this URL.
	if ( current_theme_supports( 'amp' ) ) {
		$validation_errors = AMP_Invalid_URL_Post_Type::get_invalid_url_validation_errors( $current_url, array( 'ignore_accepted' => true ) );
		$error_count       = count( $validation_errors );
		if ( $error_count > 0 ) {
			echo "<!--\n";
			echo esc_html( sprintf(
				/* translators: %s is error count */
				_n(
					'There is %s validation error that is blocking the amphtml version from being available.',
					'There are %s validation errors that are blocking the amphtml version from being available.',
					$error_count,
					'amp'
				),
				number_format_i18n( $error_count )
			) );
			echo "\n-->";
			return;
		}
	}

	if ( $amp_url ) {
		printf( '<link rel="amphtml" href="%s">', esc_url( $amp_url ) );
	}
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

		// Disabled by default for custom page templates, page on front and page for posts, unless 'amp' theme support is present.
		default:
			$enabled = current_theme_supports( 'amp' ) || (
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
 * Determine whether the current response being served as AMP.
 *
 * This function cannot be called before the parse_query action because it needs to be able
 * to determine the queried object is able to be served as AMP.
 *
 * @see is_header_video_active()
 * @return bool Whether it is the AMP endpoint.
 */
function is_amp_endpoint() {
	if ( is_admin() || is_feed() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return false;
	}

	$did_parse_query = did_action( 'parse_query' );

	if ( ! $did_parse_query ) {
		_doing_it_wrong( __FUNCTION__, sprintf( esc_html__( "is_amp_endpoint() was called before the 'parse_query' hook was called. This function will always return 'false' before the 'parse_query' hook is called.", 'amp' ) ), '0.4.2' );
	}

	$has_amp_query_var = (
		isset( $_GET[ amp_get_slug() ] ) // WPCS: CSRF OK.
		||
		false !== get_query_var( amp_get_slug(), false )
	);

	// When there is no query var and AMP is not canonical/native, then this is definitely not an AMP endpoint.
	if ( ! $has_amp_query_var && ! amp_is_canonical() ) {
		return false;
	}

	/*
	 * If theme suport is present and there is available_callback, it is what determines whether this is
	 * an AMP endpoint. The available_callback is is presumed to check post_supports_amp().
	 * @todo Should available_callback take a WP_Query as its arg? We need to be able to determine whether AMP is supported for another URL.
	 * @todo Should this not be a filter?
	 */
	if ( current_theme_supports( 'amp' ) ) {
		$args = get_theme_support( 'amp' );
		if ( isset( $args[0]['available_callback'] ) && is_callable( $args[0]['available_callback'] ) ) {
			return call_user_func( $args[0]['available_callback'] );
		}
	}

	// When there is no theme support, then
	if ( $did_parse_query ) {
		$queried_object = get_queried_object();
		return (
			is_singular() && $queried_object instanceof WP_Post && post_supports_amp( $queried_object )
			||
			! is_singular() && AMP_Options_Manager::get_option( 'non_singular_supported' )
		);
	}

	return true;
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
 *
 * @return string Boilerplate code.
 */
function amp_get_boilerplate_code() {
	return '<style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style>'
		. '<noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>';
}

/**
 * Register default scripts for AMP components.
 *
 * @param WP_Scripts $wp_scripts Scripts.
 */
function amp_register_default_scripts( $wp_scripts ) {

	// AMP Runtime.
	$handle = 'amp-runtime';
	$wp_scripts->add(
		$handle,
		'https://cdn.ampproject.org/v0.js',
		array(),
		null
	);
	$wp_scripts->add_data( $handle, 'amp_script_attributes', array(
		'async' => true,
	) );

	// Shadow AMP API.
	$handle = 'amp-shadow';
	$wp_scripts->add(
		$handle,
		'https://cdn.ampproject.org/shadow-v0.js',
		array(),
		null
	);
	$wp_scripts->add_data( $handle, 'amp_script_attributes', array(
		'async' => true,
	) );

	// Get all AMP components as defined in the spec.
	$extensions = array();
	foreach ( AMP_Allowed_Tags_Generated::get_allowed_tags() as $allowed_tag ) {
		foreach ( $allowed_tag as $rule_spec ) {
			if ( ! empty( $rule_spec[ AMP_Rule_Spec::TAG_SPEC ]['requires_extension'] ) ) {
				$extensions = array_merge(
					$extensions,
					$rule_spec[ AMP_Rule_Spec::TAG_SPEC ]['requires_extension']
				);
			}
		}
	}
	$extensions = array_unique( $extensions );

	foreach ( $extensions as $extension ) {
		$src = sprintf(
			'https://cdn.ampproject.org/v0/%s-%s.js',
			$extension,
			'latest'
		);

		$wp_scripts->add(
			$extension,
			$src,
			array( 'amp-runtime' ),
			null
		);
	}
}

/**
 * Generate HTML for AMP scripts that have not yet been printed.
 *
 * This is adapted from `wp_scripts()->do_items()`, but it runs only the bare minimum required to output
 * the missing scripts, without allowing other filters to apply which may cause an invalid AMP response.
 * The HTML for the scripts is returned instead of being printed.
 *
 * @since 0.7.2
 * @see WP_Scripts::do_items()
 * @see AMP_Base_Embed_Handler::get_scripts()
 * @see AMP_Base_Sanitizer::get_scripts()
 *
 * @param array $scripts Script handles mapped to URLs or true.
 * @return string HTML for scripts tags that have not yet been done.
 */
function amp_render_scripts( $scripts ) {
	$script_tags = '';

	/*
	 * Make sure the src is up to date. This allows for embed handlers to override the
	 * default extension version by defining a different URL.
	 */
	foreach ( $scripts as $handle => $src ) {
		if ( is_string( $src ) && wp_script_is( $handle, 'registered' ) ) {
			wp_scripts()->registered[ $handle ]->src = $src;
		}
	}

	foreach ( array_diff( array_keys( $scripts ), wp_scripts()->done ) as $handle ) {
		if ( ! wp_script_is( $handle, 'registered' ) ) {
			continue;
		}

		$script_dep   = wp_scripts()->registered[ $handle ];
		$script_tags .= amp_filter_script_loader_tag(
			sprintf(
				"<script type='text/javascript' src='%s'></script>\n", // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
				esc_url( $script_dep->src )
			),
			$handle
		);

		wp_scripts()->done[] = $handle;
	}
	return $script_tags;
}

/**
 * Add AMP script attributes to enqueued scripts.
 *
 * @link https://core.trac.wordpress.org/ticket/12009
 * @since 0.7
 *
 * @param string $tag    The script tag.
 * @param string $handle The script handle.
 * @return string Script loader tag.
 */
function amp_filter_script_loader_tag( $tag, $handle ) {
	$prefix = 'https://cdn.ampproject.org/';
	$src    = wp_scripts()->registered[ $handle ]->src;
	if ( 0 !== strpos( $src, $prefix ) ) {
		return $tag;
	}

	/*
	 * All scripts from AMP CDN should be loaded async.
	 * See <https://www.ampproject.org/docs/integration/pwa-amp/amp-in-pwa#include-"shadow-amp"-in-your-progressive-web-app>.
	 */
	$attributes = array(
		'async' => true,
	);

	// Add custom-template and custom-element attributes. All component scripts look like https://cdn.ampproject.org/v0/:name-:version.js.
	if ( 'v0' === strtok( substr( $src, strlen( $prefix ) ), '/' ) ) {
		/*
		 * Per the spec, "Most extensions are custom-elements." In fact, there is only one custom template. So we hard-code it here.
		 *
		 * @link https://github.com/ampproject/amphtml/blob/cd685d4e62153557519553ffa2183aedf8c93d62/validator/validator.proto#L326-L328
		 * @link https://github.com/ampproject/amphtml/blob/cd685d4e62153557519553ffa2183aedf8c93d62/extensions/amp-mustache/validator-amp-mustache.protoascii#L27
		 */
		if ( 'amp-mustache' === $handle ) {
			$attributes['custom-template'] = $handle;
		} else {
			$attributes['custom-element'] = $handle;
		}
	}

	// Add each attribute (if it hasn't already been added).
	foreach ( $attributes as $key => $value ) {
		if ( ! preg_match( ":\s$key(=|>|\s):", $tag ) ) {
			if ( true === $value ) {
				$attribute_string = sprintf( ' %s', esc_attr( $key ) );
			} else {
				$attribute_string = sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( $value ) );
			}
			$tag = preg_replace(
				':(?=></script>):',
				$attribute_string,
				$tag,
				1
			);
		}
	}

	return $tag;
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
 * @param array|string $analytics Analytics entries, or empty string when called via wp_footer action.
 */
function amp_print_analytics( $analytics ) {
	if ( '' === $analytics ) {
		$analytics = array();
	}
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
			'AMP_Core_Block_Handler'        => array(),
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
			'AMP_Gfycat_Embed_Handler'      => array(),
			'AMP_Hulu_Embed_Handler'        => array(),
			'AMP_Imgur_Embed_Handler'       => array(),
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
			'AMP_Core_Theme_Sanitizer'        => array(
				'template'   => get_template(),
				'stylesheet' => get_stylesheet(),
			),
			'AMP_Img_Sanitizer'               => array(),
			'AMP_Form_Sanitizer'              => array(),
			'AMP_Comments_Sanitizer'          => array(),
			'AMP_Video_Sanitizer'             => array(),
			'AMP_O2_Player_Sanitizer'         => array(),
			'AMP_Audio_Sanitizer'             => array(),
			'AMP_Playbuzz_Sanitizer'          => array(),
			'AMP_Embed_Sanitizer'             => array(),
			'AMP_Iframe_Sanitizer'            => array(
				'add_placeholder' => true,
			),
			'AMP_Gallery_Block_Sanitizer'     => array( // Note: Gallery block sanitizer must come after image sanitizers since itś logic is using the already sanitized images.
				'carousel_required' => ! current_theme_supports( 'amp' ), // For back-compat.
			),
			'AMP_Block_Sanitizer'             => array(), // Note: Block sanitizer must come after embed / media sanitizers since it's logic is using the already sanitized content.
			'AMP_Script_Sanitizer'            => array(),
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
	} elseif ( ( 'attachment' === $post->post_type ) && wp_attachment_is( 'image', $post ) ) {
		$post_image_id = $post->ID;
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

	/*
	 * "The logo should be a rectangle, not a square. The logo should fit in a 60x600px rectangle.,
	 * and either be exactly 60px high (preferred), or exactly 600px wide. For example, 450x45px
	 * would not be acceptable, even though it fits in the 600x60px rectangle."
	 * See <https://developers.google.com/search/docs/data-types/article#logo-guidelines>.
	 */
	$max_logo_width  = 600;
	$max_logo_height = 60;
	$custom_logo_id  = get_theme_mod( 'custom_logo' );
	$schema_img      = array();

	if ( has_custom_logo() && $custom_logo_id ) {
		$custom_logo_img = wp_get_attachment_image_src( $custom_logo_id, array( $max_logo_width, $max_logo_height ), false );
		if ( $custom_logo_img ) {
			// @todo Warning: The width/height returned may not actually be physically the $max_logo_width and $max_logo_height for the image returned.
			$schema_img = array(
				'url'    => $custom_logo_img[0],
				'width'  => $custom_logo_img[1],
				'height' => $custom_logo_img[2],
			);
		}
	}

	// Try Site Icon, though it is not ideal because "The logo should be a rectangle, not a square." per <https://developers.google.com/search/docs/data-types/article#logo-guidelines>.
	if ( empty( $schema_img['url'] ) ) {
		/*
		 * Note that AMP_Post_Template::SITE_ICON_SIZE is used and not $max_logo_height because 32px is the largest
		 * size that is defined in \WP_Site_Icon::$site_icon_sizes which is less than 60px. It may be a good idea
		 * to add a site_icon_image_sizes filter which appends 60 to the list of sizes, but this will only help
		 * when adding a new site icon and it would be irrelevant when a custom logo is present, per above.
		 */
		$schema_img = array(
			'url'    => get_site_icon_url( AMP_Post_Template::SITE_ICON_SIZE ),
			'width'  => AMP_Post_Template::SITE_ICON_SIZE,
			'height' => AMP_Post_Template::SITE_ICON_SIZE,
		);
	}

	/**
	 * Filters the publisher logo URL in the schema.org data.
	 *
	 * Previously, this only filtered the Site Icon, as that was the only possible schema.org publisher logo.
	 * But the Custom Logo is now the preferred publisher logo, if it exists and its dimensions aren't too big.
	 *
	 * @since 0.3
	 *
	 * @param string $schema_img_url URL of the publisher logo, either the Custom Logo or the Site Icon.
	 */
	$filtered_schema_img_url = apply_filters( 'amp_site_icon_url', $schema_img['url'] );
	if ( $filtered_schema_img_url !== $schema_img['url'] ) {
		$schema_img['url'] = $filtered_schema_img_url;
		unset( $schema_img['width'], $schema_img['height'] ); // Clear width/height since now unknown, and not required.
	}

	if ( ! empty( $schema_img['url'] ) ) {
		$metadata['publisher']['logo'] = array_merge(
			array(
				'@type' => 'ImageObject',
			),
			$schema_img
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
