<?php
/**
 * Plugin Name: AMP
 * Description: Add AMP support to your WordPress site.
 * Plugin URI: https://github.com/automattic/amp-wp
 * Author: Automattic
 * Author URI: https://automattic.com
 * Version: 0.6-beta
 * Text Domain: amp
 * Domain Path: /languages/
 * License: GPLv2 or later
 *
 * @package AMP
 */

define( 'AMP__FILE__', __FILE__ );
define( 'AMP__DIR__', dirname( __FILE__ ) );
define( 'AMP__VERSION', '0.6-beta' );

require_once AMP__DIR__ . '/includes/class-amp-autoloader.php';
AMP_Autoloader::register();

require_once AMP__DIR__ . '/back-compat/back-compat.php';
require_once AMP__DIR__ . '/includes/amp-helper-functions.php';
require_once AMP__DIR__ . '/includes/admin/functions.php';

register_activation_hook( __FILE__, 'amp_activate' );
function amp_activate() {
	amp_after_setup_theme();
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

/**
 * Set up AMP.
 *
 * This function must be invoked through the 'after_setup_theme' action to allow
 * the AMP setting to declare the post types support earlier than plugins/theme.
 *
 * @since 0.6
 */
function amp_after_setup_theme() {
	if ( false === apply_filters( 'amp_is_enabled', true ) ) {
		return;
	}

	if ( ! defined( 'AMP_QUERY_VAR' ) ) {
		/**
		 * Filter the AMP query variable.
		 *
		 * @since 0.3.2
		 * @param string $query_var The AMP query variable.
		 */
		define( 'AMP_QUERY_VAR', apply_filters( 'amp_query_var', 'amp' ) );
	}

	add_action( 'init', 'amp_init' );
	add_action( 'admin_init', 'AMP_Options_Manager::register_settings' );
	add_filter( 'amp_post_template_analytics', 'amp_add_custom_analytics' );
	add_action( 'wp_loaded', 'amp_post_meta_box' );
	add_action( 'wp_loaded', 'amp_add_options_menu' );
	AMP_Post_Type_Support::add_post_type_support();
}
add_action( 'after_setup_theme', 'amp_after_setup_theme', 5 );

/**
 * Init AMP.
 *
 * @since 0.1
 */
function amp_init() {

	/**
	 * Triggers on init when AMP plugin is active.
	 *
	 * @since 0.3
	 */
	do_action( 'amp_init' );

	load_plugin_textdomain( 'amp', false, plugin_basename( AMP__DIR__ ) . '/languages' );

	add_rewrite_endpoint( AMP_QUERY_VAR, EP_PERMALINK );

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
			wp_safe_redirect( get_permalink( $post->ID ), 301 );
			exit;
		}
		return;
	}

	if ( $is_amp_endpoint ) {
		amp_prepare_render();
	} else {
		amp_add_frontend_actions();
	}
}

function amp_load_classes() {
	_deprecated_function( __FUNCTION__, '0.6' );
}

function amp_add_frontend_actions() {
	AMP_Frontend_Actions::register_hooks();
}

function amp_add_post_template_actions() {
	AMP_Paired_Post_Actions::register_hooks();
	require_once( AMP__DIR__ . '/includes/amp-post-template-functions.php' );
}

function amp_prepare_render() {
	add_action( 'template_redirect', 'amp_render' );
}

/**
 * Render AMP for queried post.
 *
 * @since 0.1
 */
function amp_render() {
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
 * @param WP_Post|int $post Post.
 * @global WP_Query $wp_query
 */
function amp_render_post( $post ) {
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
	$was_set = isset( $wp_query->query_vars[ AMP_QUERY_VAR ] );
	if ( ! $was_set ) {
		$wp_query->query_vars[ AMP_QUERY_VAR ] = true;
	}

	/**
	 * Fires before rendering a post in AMP.
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
		unset( $wp_query->query_vars[ AMP_QUERY_VAR ] );
	}
}

/**
 * Bootstraps the AMP customizer.
 *
 * @since 0.4
 */
function _amp_bootstrap_customizer() {
	add_action( 'after_setup_theme', 'amp_init_customizer' );
}
add_action( 'plugins_loaded', '_amp_bootstrap_customizer', 9 ); // Should be hooked before priority 10 on 'plugins_loaded' to properly unhook core panels.

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
