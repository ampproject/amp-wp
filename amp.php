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
	if ( ! is_singular() || is_feed() ) {
		return;
	}

	$is_amp_endpoint = is_amp_endpoint();

	// Cannot use `get_queried_object` before canonical redirect; see https://core.trac.wordpress.org/ticket/35344
	global $wp_query;
	$post = $wp_query->post;

	$supports = post_supports_amp( $post );

	if ( ! $supports ) {
		if ( $is_amp_endpoint ) {
			wp_safe_redirect( get_permalink( $post->ID ) );
			exit;
		}
		return;
	}

	if ( $is_amp_endpoint ) {
		amp_prepare_render();
	} else if( get_option('amp_canonical') && $supports && get_theme_support('amp')) {
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
	amp_load_classes();

	$post_id = get_queried_object_id();
	do_action( 'pre_amp_render_post', $post_id );

	amp_add_post_template_actions();
	$template = new AMP_Post_Template( $post_id );
	$template->load();
	exit;
}

function amp_add_canonical_actions() {
	// Load AMP canonical actions
	require_once( AMP__DIR__ . '/includes/amp-canonical-actions.php');
	// Load high-priority filters for canonical AMP
	require_once( AMP__DIR__ . '/includes/amp-canonical-filters.php');
	add_action( 'template_redirect', 'amp_maybe_init_postprocess_html' );
}

function amp_maybe_init_postprocess_html() {
	ob_start( 'amp_canonical_postprocess_html' );
}

/**
 * Remove nodes from DOM
 */
function remove_dom_nodes($nodes_to_remove) {
	foreach ($nodes_to_remove as $node) {
		$node->parentNode->removeChild($node);
	}
}

/**
 * Add amp attribute to html tag
 */
function add_amp_attr($dom) {
	$html_tag = $dom->getElementsByTagName('html')->item(0);
	$html_tag->setAttribute('amp', '');
}

/**
 * Eliminate 3rd-party JS
 */
function eliminate_3p_js($dom) {
	// Get rid of 3rd-party scripts
	$scripts = $dom->getElementsByTagName('script');
	$scripts_to_remove = [];
	foreach ($scripts as $script) {
		$type = $script->getAttribute('type');
		$custom_element = $script->getAttribute('custom-element');
		if ($type !== "application/ld+json" and !$custom_element) {
			$src = $script->getAttribute('src');
			if ($src !== "https://cdn.ampproject.org/v0.js") {
				array_push($scripts_to_remove, $script);
			}
		}
	}

	error_log( "Removing  scripts!" );
	remove_dom_nodes($scripts_to_remove);
}

/**
 * Eliminate external stylesheets
 */
function eliminate_ext_css($dom) {
	$links = $dom->getElementsByTagName('link');
	$links_to_remove = [];

	$ids = array("dashicons-css", "admin-bar-css");
	foreach ($links as $link) {
		$rel = $link->getAttribute('rel');
		$id = $link->getAttribute('id');
		if ($rel == "stylesheet" and in_array($id, $ids)) {
			array_push($links_to_remove, $link);
		}
		if ( $link->hasAttribute('crossorigin') ) {
			$link->removeAttribute('crossorigin');
		}
	}

	error_log( "Removing external stylesheet links!" );
	remove_dom_nodes($links_to_remove);

}

/**
 * Eliminate sidebars
 */
function eliminate_sidebars($dom) {
	$aside_secondary = $dom->getElementById('secondary');
	error_log( "Removing secondary aside!" );
	remove_dom_nodes(array($aside_secondary));
}

/**
 * Eliminate entry-footers
 */
function eliminate_entry_footers($dom) {
	$entry_footers = $dom->getElementsByTagName( 'footer' );
	$footers_to_remove = [];
	foreach ($entry_footers as $footer) {
		$class = $footer->getAttribute('class');
		if ($class == "entry-footer") {
			array_push($footers_to_remove, $footer);
		}
	}
	error_log( "Removing footers!" );
	remove_dom_nodes($footers_to_remove);
}

/**
 * Eliminate overall footer
 */
function eliminate_overall_footer($dom) {
	$overall_footer = $dom->getElementById('colophon');
	error_log( "Removing overall footer!" );
	remove_dom_nodes(array($overall_footer));
}

/**
 * Eliminate post navigation
 */
function eliminate_post_navigation($dom) {
	$navs = $dom->getElementsByTagName( 'nav' );
	$navs_to_remove = [];
	foreach ($navs as $nav) {
		$classes = $nav->getAttribute('class');
		error_log(print_r($classes));
		error_log(gettype($classes));
		if (strpos($classes, "post-navigation") !== false) {
			array_push($navs_to_remove, $nav);
		}
	}
	error_log( "Removing post navigation!" );
	remove_dom_nodes($navs_to_remove);
}

/**
 * Eliminate comments section
 */
function eliminate_comments_section($dom) {
	$comments = $dom->getElementById('comments');
	error_log( "Removing comments section!" );
	remove_dom_nodes(array($comments));
}

/**
 * Set meta viewport
 * <meta name="viewport" content="width=device-width,minimum-scale=1">
 */
function set_meta_viewport($dom) {
	$metatags = $dom->getElementsByTagName('meta');
	foreach ($metatags as $meta) {
		$meta_tag_name = $meta->getAttribute('name');
		if ($meta_tag_name == "viewport") {
			error_log( "Updating viewport met tag!" );
			$meta->setAttribute('content', 'width=device-width,minimum-scale=1');
		}
	}
}

/**
 * Eliminate non-amp-custom styles
 */
function eliminate_non_amp_custom_styles($dom) {
	$styles = $dom->getElementsByTagName('style');
	$styles_to_remove = [];
	foreach ($styles as $style) {
		$type = $style->getAttribute('type');
		if ($type == "text/css") {
			if (!$style->hasAttribute('amp-custom')) {
				array_push($styles_to_remove, $style);
			}
		}
	}

	error_log("Removing non amp-custom style tags!");
	remove_dom_nodes($styles_to_remove);
}

/**
 * Convert generated $html to AMP-compatible format
 */
function amp_canonical_postprocess_html( $html ) {

	$dom = new DOMDocument();
	libxml_use_internal_errors(true);
	$dom->loadHTML($html);
	libxml_use_internal_errors(false);

	// Add amp attribute to html tag
	add_amp_attr($dom);
	// Eliminate 3p JS
	eliminate_3p_js($dom);
	// Eliminate external stylesheets
	eliminate_ext_css($dom);
	// Eliminate sidebars
	eliminate_sidebars($dom);
	// Eliminate entry footers
	eliminate_entry_footers($dom);
	// Eliminate overall footer
	eliminate_overall_footer($dom);
	// Eliminate post navigation
	eliminate_post_navigation($dom);
	// Eliminate comments section
	eliminate_comments_section($dom);
	// Set meta viewport
	set_meta_viewport($dom);
	// Eliminate non-amp-custom Stylesheets
	eliminate_non_amp_custom_styles($dom);

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
