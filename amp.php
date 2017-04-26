<?php
/**
 * Plugin Name: AMP
 * Description: Add AMP support to your WordPress site.
 * Plugin URI: https://github.com/automattic/amp-wp
 * Author: Automattic
 * Author URI: https://automattic.com
 * Version: 0.4.2
 * Text Domain: amp
 * Domain Path: /languages/
 * License: GPLv2 or later
 */

define( 'AMP__FILE__', __FILE__ );
define( 'AMP__DIR__', dirname( __FILE__ ) );
define( 'AMP__VERSION', '0.4.2' );

require_once( AMP__DIR__ . '/back-compat/back-compat.php' );
require_once( AMP__DIR__ . '/includes/amp-helper-functions.php' );
require_once( AMP__DIR__ . '/includes/admin/functions.php' );
require_once( AMP__DIR__ . '/includes/settings/class-amp-customizer-settings.php' );
require_once( AMP__DIR__ . '/includes/settings/class-amp-customizer-design-settings.php' );
require_once( AMP__DIR__ . '/includes/utils/class-amp-dom-utils.php');
require_once( AMP__DIR__ . '/option.php' );
require_once(AMP__DIR__ . '/post-processing/class-amp-sanitize-tweentyseventeen-theme-plain.php');

register_activation_hook( __FILE__, 'amp_activate' );
function amp_activate() {
	if ( ! did_action( 'amp_init' ) ) {
		amp_init();
	}
	flush_rewrite_rules();
}

register_deactivation_hook( __FILE__, 'amp_deactivate' );
function amp_deactivate() {
	// We need to manually remove the amp endpoint
	global $wp_rewrite;
	foreach ( $wp_rewrite->endpoints as $index => $endpoint ) {
		if ( AMP_QUERY_VAR === $endpoint[1] ) {
			unset( $wp_rewrite->endpoints[ $index ] );
			break;
		}
	}

	flush_rewrite_rules();
}

add_action( 'init', 'amp_init' );
function amp_init() {
	if ( false === apply_filters( 'amp_is_enabled', true ) ) {
		return;
	}
	error_log("AMP INIT");

	define( 'AMP_QUERY_VAR', apply_filters( 'amp_query_var', 'amp' ) );

	do_action( 'amp_init' );

	load_plugin_textdomain( 'amp', false, plugin_basename( AMP__DIR__ ) . '/languages' );

	// If the amp_canonical option has not been setup, or the current
	// theme does not provide AMP support, then follow the "paired" approach
	if ( ! get_option('amp_canonical') || ! get_theme_support('amp')) {
		add_rewrite_endpoint( AMP_QUERY_VAR, EP_PERMALINK );
		add_post_type_support( 'post', AMP_QUERY_VAR );
	}

	add_filter( 'request', 'amp_force_query_var_value' );
	add_action( 'wp', 'amp_maybe_add_actions' );

	// Redirect the old url of amp page to the updated url.
	add_filter( 'old_slug_redirect_url', 'amp_redirect_old_slug_to_new_url' );

	if ( class_exists( 'Jetpack' ) && ! ( defined( 'IS_WPCOM' ) && IS_WPCOM ) ) {
		require_once( AMP__DIR__ . '/jetpack-helper.php' );
	}

}

// Make sure the `amp` query var has an explicit value.
// Avoids issues when filtering the deprecated `query_string` hook.
function amp_force_query_var_value( $query_vars ) {
	if ( isset( $query_vars[ AMP_QUERY_VAR ] ) && '' === $query_vars[ AMP_QUERY_VAR ] ) {
		$query_vars[ AMP_QUERY_VAR ] = 1;
	}
	return $query_vars;
}

function amp_maybe_add_actions() {
	if ( is_feed() || (!get_option('amp_canonical') && !is_singular())) {
		return;
	}

	$is_amp_endpoint = is_amp_endpoint();
	$supports = true;
	if (is_singular()) {
		// Cannot use `get_queried_object` before canonical redirect; see https://core.trac.wordpress.org/ticket/35344
		global $wp_query;
		$post = $wp_query->post;
		$supports = post_supports_amp($post);
		if ( ! $supports ) {
			if ( $is_amp_endpoint ) {
				wp_safe_redirect( get_permalink( $post->ID ) );
				exit;
			}
			return;
		}
	}

	if ( $is_amp_endpoint ) {
		amp_prepare_render();
	} else if( get_option('amp_canonical') && $supports && get_theme_support('amp') && is_singular() ) {
		amp_add_canonical_actions();
	} else {
		amp_add_frontend_actions();
	}
}

function amp_load_classes() {
	require_once( AMP__DIR__ . '/includes/class-amp-post-template.php' ); // this loads everything else
}

function amp_add_frontend_actions() {
	require_once( AMP__DIR__ . '/includes/amp-frontend-actions.php' );
}

function amp_add_post_template_actions() {
	require_once( AMP__DIR__ . '/includes/amp-post-template-actions.php' );
	require_once( AMP__DIR__ . '/includes/amp-post-template-functions.php' );
}

function amp_prepare_render() {
	add_action( 'template_redirect', 'amp_render' );
}

function amp_render() {
	$post_id = get_queried_object_id();
	amp_render_post( $post_id );
	exit;
}

function amp_render_post( $post_id ) {
	$post = get_post( $post_id );
	if ( ! $post ) {
		return;
	}

	amp_load_classes();

	do_action( 'pre_amp_render_post', $post_id );

	amp_add_post_template_actions();
	$template = new AMP_Post_Template( $post_id );
	$template->load();
}

function amp_add_canonical_actions() {
	// Load AMP canonical actions
	require_once( AMP__DIR__ . '/includes/amp-canonical-actions.php');
	// Load high-priority filters for canonical AMP
	require_once( AMP__DIR__ . '/includes/amp-canonical-filters.php');
	// Template redirect to postprocessing actions
	add_action( 'template_redirect', 'amp_maybe_init_postprocess_html' );
}

function amp_maybe_init_postprocess_html() {
	ob_start( 'amp_canonical_postprocess_html' );
}

/**
 * Convert generated $html (plain 2017 Theme) to valid-AMP format
 */
function amp_canonical_postprocess_html( $html ) {

	$dom = new DOMDocument();
	libxml_use_internal_errors(true);
	$dom->loadHTML($html);
	libxml_use_internal_errors(false);

	// TODO (@amedina): Define how specific theme sanitations
	// will be executed; user can select a specific one or a
	// generic one could be executed

	// Add amp attribute to html tag
	AMP_Sanitize_TweentySeventeen_Theme::add_amp_attr($dom);
	// Eliminate 3p JS
	AMP_Sanitize_TweentySeventeen_Theme::eliminate_3p_js($dom);
	// Eliminate external stylesheets
	AMP_Sanitize_TweentySeventeen_Theme::eliminate_ext_css($dom);
	// Eliminate sidebars
	//AMP_Sanitize_TweentySeventeen_Theme::eliminate_sidebars($dom);
	// Eliminate entry footers
	AMP_Sanitize_TweentySeventeen_Theme::eliminate_entry_footers($dom);
	// Eliminate overall footer
	AMP_Sanitize_TweentySeventeen_Theme::eliminate_overall_footer($dom);
	// Eliminate post navigation
	//AMP_Sanitize_TweentySeventeen_Theme::eliminate_post_navigation($dom);
	// Eliminate comments section
	AMP_Sanitize_TweentySeventeen_Theme::eliminate_comments_section($dom);
	// Set meta viewport
	AMP_Sanitize_TweentySeventeen_Theme::set_meta_viewport($dom);
	// Eliminate non-amp-custom Stylesheets
	AMP_Sanitize_TweentySeventeen_Theme::eliminate_non_amp_custom_styles($dom);
	// Inline theme CSS
	AMP_Sanitize_TweentySeventeen_Theme::inline_theme_css($dom);
	// AMP Custom-header img
	AMP_Sanitize_TweentySeventeen_Theme::amp_custom_header_img($dom);
	// Remove styled SVGs
	AMP_Sanitize_TweentySeventeen_Theme::remove_styled_svgs($dom);
	// Save new HTML contents
	$amp_html = $dom->saveHTML();

	return $amp_html;
}

/**
 * Bootstraps the AMP customizer.
 *
 * If the AMP customizer is enabled, initially drop the core widgets and menus panels. If the current
 * preview page isn't flagged as an AMP template, the core panels will be re-added and the AMP panel
 * hidden.
 *
 * @internal This callback must be hooked before priority 10 on 'plugins_loaded' to properly unhook
 *           the core panels.
 *
 * @since 0.4
 */
function _amp_bootstrap_customizer() {
	/**
	 * Filter whether to enable the AMP template customizer functionality.
	 *
	 * @param bool $enable Whether to enable the AMP customizer. Default true.
	 */
	$amp_customizer_enabled = apply_filters( 'amp_customizer_is_enabled', true );

	if ( true === $amp_customizer_enabled ) {
		amp_init_customizer();
	}
}
add_action( 'plugins_loaded', '_amp_bootstrap_customizer', 9 );

/**
 * Redirects the old AMP URL to the new AMP URL.
 * If post slug is updated the amp page with old post slug will be redirected to the updated url.
 *
 * @param  string $link New URL of the post.
 *
 * @return string $link URL to be redirected.
 */
function amp_redirect_old_slug_to_new_url( $link ) {

	if ( is_amp_endpoint() ) {
		$link = trailingslashit( trailingslashit( $link ) . AMP_QUERY_VAR );
	}

	return $link;
}
