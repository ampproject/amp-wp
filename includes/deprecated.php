<?php
/**
 * Deprecated functions.
 *
 * @package AMP
 */

/**
 * Load classes.
 *
 * @since 0.2
 * @codeCoverageIgnore
 * @deprecated As of 0.6 since autoloading is now employed.
 */
function amp_load_classes() {
	_deprecated_function( __FUNCTION__, '0.6' );
}

/**
 * Conditionally add AMP actions or render the transitional mode template(s).
 *
 * If the request is for an AMP page and this is in 'canonical mode,' redirect to the non-AMP page.
 * It won't need this plugin's template system, nor the frontend actions like the 'rel' link.
 *
 * @codeCoverageIgnore
 * @deprecated This function is not used when 'amp' theme support is added.
 * @global WP_Query $wp_query
 * @since 0.2
 * @return void
 */
function amp_maybe_add_actions() {
	_deprecated_function( __FUNCTION__, '1.5' );

	// Short-circuit when theme supports AMP, as everything is handled by AMP_Theme_Support.
	if ( current_theme_supports( AMP_Theme_Support::SLUG ) ) {
		return;
	}

	// The remaining logic here is for transitional mode running in themes that don't support AMP, the template system in AMP<=0.6.
	global $wp_query;
	if ( ! ( is_singular() || $wp_query->is_posts_page ) || is_feed() ) {
		return;
	}

	if ( is_singular( AMP_Story_Post_Type::POST_TYPE_SLUG ) ) {
		return;
	}

	$is_amp_endpoint = is_amp_endpoint();

	/**
	 * Queried post object.
	 *
	 * @var WP_Post $post
	 */
	$post = get_queried_object();
	if ( ! post_supports_amp( $post ) ) {
		if ( $is_amp_endpoint ) {
			/*
			 * Temporary redirect is used for admin users because reader mode and AMP support can be enabled by user at any time,
			 * so they will be able to make AMP available for this URL and see the change without wrestling with the redirect cache.
			 */
			wp_safe_redirect( get_permalink( $post->ID ), current_user_can( 'manage_options' ) ? 302 : 301 );
			exit;
		}
		return;
	}

	if ( $is_amp_endpoint ) {

		// Prevent infinite URL space under /amp/ endpoint.
		global $wp;
		$path_args = [];
		wp_parse_str( $wp->matched_query, $path_args );
		if ( isset( $path_args[ amp_get_slug() ] ) && '' !== $path_args[ amp_get_slug() ] ) {
			wp_safe_redirect( amp_get_permalink( $post->ID ), 301 );
			exit;
		}

		amp_prepare_render();
	} else {
		amp_add_frontend_actions();
	}
}

/**
 * Add post template actions.
 *
 * @since 0.2
 * @codeCoverageIgnore
 * @deprecated This function is not used when 'amp' theme support is added.
 */
function amp_add_post_template_actions() {
	_deprecated_function( __FUNCTION__, '1.5' );
	require_once AMP__DIR__ . '/includes/amp-post-template-functions.php';
	amp_post_template_init_hooks();
}

/**
 * Add action to do post template rendering at template_redirect action.
 *
 * @since 0.2
 * @since 1.0 The amp_render() function is called at template_redirect action priority 11 instead of priority 10.
 * @codeCoverageIgnore
 * @deprecated This function is not used when 'amp' theme support is added.
 */
function amp_prepare_render() {
	_deprecated_function( __FUNCTION__, '1.5' );
	add_action( 'template_redirect', 'amp_render', 11 );
}

/**
 * Render AMP for queried post.
 *
 * @since 0.1
 * @codeCoverageIgnore
 * @deprecated This function is not used when 'amp' theme support is added.
 */
function amp_render() {
	_deprecated_function( __FUNCTION__, '1.5' );

	// Note that queried object is used instead of the ID so that the_preview for the queried post can apply.
	$post = get_queried_object();
	if ( $post instanceof WP_Post ) {
		amp_render_post( $post );
		exit;
	}
}

/**
 * Render AMP post template.
 *
 * @since 0.5
 * @codeCoverageIgnore
 * @deprecated Rendering a post is now handled by AMP_Theme_Support.
 *
 * @param WP_Post|int $post Post.
 * @global WP_Query $wp_query
 */
function amp_render_post( $post ) {
	_deprecated_function( __FUNCTION__, '1.5' );
	global $wp_query;

	if ( ! ( $post instanceof WP_Post ) ) {
		$post = get_post( $post );
		if ( ! $post ) {
			return;
		}
	}
	$post_id = $post->ID;

	/*
	 * If amp_render_post is called directly outside of the standard endpoint, is_amp_endpoint() will return false,
	 * which is not ideal for any code that expects to run in an AMP context.
	 * Let's force the value to be true while we render AMP.
	 */
	$was_set = isset( $wp_query->query_vars[ amp_get_slug() ] );
	if ( ! $was_set ) {
		$wp_query->query_vars[ amp_get_slug() ] = true;
	}

	// Prevent New Relic from causing invalid AMP responses due the NREUM script it injects after the meta charset.
	if ( extension_loaded( 'newrelic' ) ) {
		newrelic_disable_autorum();
	}

	/**
	 * Fires before rendering a post in AMP.
	 *
	 * This action is not triggered when 'amp' theme support is present. Instead, you should use 'template_redirect' action and check if `is_amp_endpoint()`.
	 *
	 * @since 0.2
	 *
	 * @param int $post_id Post ID.
	 */
	do_action( 'pre_amp_render_post', $post_id );

	amp_add_post_template_actions();
	$template = new AMP_Post_Template( $post );
	$template->load();

	if ( ! $was_set ) {
		unset( $wp_query->query_vars[ amp_get_slug() ] );
	}
}

/**
 * Print scripts.
 *
 * @codeCoverageIgnore
 * @deprecated Scripts are now automatically added.
 * @see amp_register_default_scripts()
 * @see amp_filter_script_loader_tag()
 * @param AMP_Post_Template $amp_template Template.
 */
function amp_post_template_add_scripts( $amp_template ) {
	_deprecated_function( __FUNCTION__, '1.5' );
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo amp_render_scripts(
		array_merge(
			[
				// Just in case the runtime has been overridden by amp_post_template_data filter.
				'amp-runtime' => $amp_template->get( 'amp_runtime_script' ),
			],
			$amp_template->get( 'amp_component_scripts', [] )
		)
	);
}

/**
 * Print boilerplate CSS.
 *
 * @codeCoverageIgnore
 * @deprecated Boilerplate is now automatically added via the amp/optimizer library.
 * @since 0.3
 * @see amp_get_boilerplate_code()
 */
function amp_post_template_add_boilerplate_css() {
	_deprecated_function( __FUNCTION__, '1.5' );
	echo amp_get_boilerplate_code(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}


/**
 * Get AMP boilerplate code.
 *
 * @deprecated Boilerplate is now added via the amp/optimizer library.
 * @since 0.7
 * @link https://www.ampproject.org/docs/reference/spec#boilerplate
 *
 * @return string Boilerplate code.
 */
function amp_get_boilerplate_code() {
	_deprecated_function( __FUNCTION__, '1.5' );
	$stylesheets = amp_get_boilerplate_stylesheets();
	return sprintf( '<style amp-boilerplate>%s</style><noscript><style amp-boilerplate>%s</style></noscript>', $stylesheets[0], $stylesheets[1] );
}

/**
 * Get AMP boilerplate stylesheets.
 *
 * @deprecated Boilerplate is now added via the amp/optimizer library.
 * @since 1.3
 * @link https://www.ampproject.org/docs/reference/spec#boilerplate
 *
 * @return string[] Stylesheets, where first is contained in style[amp-boilerplate] and the second in noscript>style[amp-boilerplate].
 */
function amp_get_boilerplate_stylesheets() {
	_deprecated_function( __FUNCTION__, '1.5' );
	return [
		'body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}',
		'body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}',
	];
}

/**
 * Print Schema.org metadata.
 *
 * @codeCoverageIgnore
 * @deprecated Since 0.7
 */
function amp_post_template_add_schemaorg_metadata() {
	_deprecated_function( __FUNCTION__, '0.7', 'amp_print_schemaorg_metadata' );
	amp_print_schemaorg_metadata();
}

/**
 * Bootstrap AMP post meta box.
 *
 * This function must be invoked only once through the 'wp_loaded' action.
 *
 * @since 0.6
 * @codeCoverageIgnore
 * @deprecated Since 1.5.0, as admin class bootstrapping is moved to amp_bootstrap_admin().
 */
function amp_post_meta_box() {
	_deprecated_function( __FUNCTION__, '1.5.0' );
}

/**
 * Bootstrap the AMP admin pointer class.
 *
 * @since 1.0
 * @codeCoverageIgnore
 * @deprecated Since 1.5.0, as admin class bootstrapping is moved to amp_bootstrap_admin().
 */
function amp_admin_pointer() {
	_deprecated_function( __FUNCTION__, '1.5.0' );
}
