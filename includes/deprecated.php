<?php
/**
 * Deprecated functions.
 *
 * @package AMP
 */

use AmpProject\AmpWP\Services;

/**
 * Load classes.
 *
 * @since 0.2
 * @codeCoverageIgnore
 * @deprecated As of 0.6 since autoloading is now employed.
 * @internal
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

	$is_amp_request = amp_is_request();

	/**
	 * Queried post object.
	 *
	 * @var WP_Post $post
	 */
	$post = get_queried_object();
	if ( ! amp_is_post_supported( $post ) ) {
		if ( $is_amp_request ) {
			/*
			 * Temporary redirect is used for admin users because reader mode and AMP support can be enabled by user at any time,
			 * so they will be able to make AMP available for this URL and see the change without wrestling with the redirect cache.
			 */
			wp_safe_redirect( get_permalink( $post->ID ), current_user_can( 'manage_options' ) ? 302 : 301 );
			exit;
		}
		return;
	}

	if ( $is_amp_request ) {

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
	 * If amp_render_post is called directly outside of the standard endpoint, amp_is_request() will return false,
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
	 * This action is not triggered when 'amp' theme support is present. Instead, you should use 'template_redirect' action and check if `amp_is_request()`.
	 *
	 * @since 0.2
	 * @deprecated Check amp_is_request() on the template_redirect action instead.
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
 * @internal
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
 * @deprecated Boilerplate is now automatically added via the ampproject/amp-toolbox library.
 * @internal
 * @since 0.3
 * @see amp_get_boilerplate_code()
 */
function amp_post_template_add_boilerplate_css() {
	_deprecated_function( __FUNCTION__, '1.5' );
	echo amp_get_boilerplate_code(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Print Schema.org metadata.
 *
 * @codeCoverageIgnore
 * @deprecated Since 0.7
 * @internal
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
 * @internal
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
 * @internal
 */
function amp_admin_pointer() {
	_deprecated_function( __FUNCTION__, '1.5.0' );
}

/**
 * Print admin notice if the Xdebug extension is loaded.
 *
 * @since 1.3
 * @deprecated 1.5.0 Warning moved to Site Health.
 * @codeCoverageIgnore
 * @internal
 * @see AmpProject\AmpWP\Admin\SiteHealth::xdebug_extension()
 */
function _amp_xdebug_admin_notice() {
	_deprecated_function( __FUNCTION__, '1.5.0' );

	?>
	<div class="notice notice-warning">
		<p>
			<?php
			esc_html_e(
				'Your server currently has the Xdebug PHP extension loaded. This can cause some of the AMP plugin\'s processes to timeout depending on your system resources and configuration. Please deactivate Xdebug for the best experience.',
				'amp'
			);
			?>
		</p>
	</div>
	<?php
}

/**
 * Redirects the old AMP URL to the new AMP URL.
 *
 * If post slug is updated the amp page with old post slug will be redirected to the updated url.
 *
 * @since 0.5
 * @internal
 * @deprecated
 *
 * @param string $link New URL of the post.
 * @return string URL to be redirected.
 */
function amp_redirect_old_slug_to_new_url( $link ) {
	_deprecated_function( __FUNCTION__, '2.1' );
	return Services::get( 'paired_routing' )->maybe_add_paired_endpoint( $link );
}

/**
 * Fix up WP_Query for front page when amp query var is present.
 *
 * Normally the front page would not get served if a query var is present other than preview, page, paged, and cpage.
 *
 * @since 0.6
 * @internal
 * @see WP_Query::parse_query()
 * @link https://github.com/WordPress/wordpress-develop/blob/0baa8ae85c670d338e78e408f8d6e301c6410c86/src/wp-includes/class-wp-query.php#L951-L971
 * @deprecated
 *
 * @param WP_Query $query Query.
 */
function amp_correct_query_when_is_front_page( WP_Query $query ) {
	_deprecated_function( __FUNCTION__, '2.1' );
	Services::get( 'paired_routing' )->correct_query_when_is_front_page( $query );
}

/**
 * Add frontend actions.
 *
 * @since 0.2
 * @deprecated Since 2.1, moved to PairedRouting.
 * @internal
 */
function amp_add_frontend_actions() {
	_deprecated_function( __FUNCTION__, '2.1' );
	add_action( 'wp_head', 'amp_add_amphtml_link' );
}

/**
 * Add analytics scripts.
 *
 * @internal
 * @deprecated 2.1 This is no longer necessary since amp-analytics is added automatically to the page during post-processing.
 * @codeCoverageIgnore
 *
 * @param array $data Data.
 * @return array Data.
 */
function amp_post_template_add_analytics_script( $data ) {
	_deprecated_function( __FUNCTION__, '2.1' );

	if ( ! empty( $data['amp_analytics'] ) ) {
		$data['amp_component_scripts']['amp-analytics'] = 'https://cdn.ampproject.org/v0/amp-analytics-0.1.js';
	}
	return $data;
}
