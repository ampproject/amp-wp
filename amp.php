<?php
/**
 * Plugin Name: AMP
 * Description: Add AMP support to your WordPress site.
 * Plugin URI: https://github.com/automattic/amp-wp
 * Author: Automattic
 * Author URI: https://automattic.com
 * Version: 0.1
 * Text Domain: amp
 * Domain Path: /languages/
 * License: GPLv2
 */

define( 'AMP_QUERY_VAR', 'amp' );
if ( ! defined( 'AMP_DEV_MODE' ) ) {
	define( 'AMP_DEV_MODE', defined( 'WP_DEBUG' ) && WP_DEBUG );
}

define( 'AMP__DIR__', dirname( __FILE__ ) );

require_once( AMP__DIR__ . '/includes/class-amp-post-template.php' );

register_activation_hook( __FILE__, 'amp_activate' );
function amp_activate(){
	amp_init();
	flush_rewrite_rules();
}

register_deactivation_hook( __FILE__, 'amp_deactivate' );
function amp_deactivate(){
	flush_rewrite_rules();
}

add_action( 'init', 'amp_init' );
function amp_init() {
	if ( false === apply_filters( 'amp_is_enabled', true ) ) {
		return;
	}

	load_plugin_textdomain( 'amp', false, plugin_basename( AMP__DIR__ ) . '/languages' );

	add_rewrite_endpoint( AMP_QUERY_VAR, EP_PERMALINK );
	add_post_type_support( 'post', AMP_QUERY_VAR );

	add_action( 'wp', 'amp_maybe_add_actions' );

	if ( class_exists( 'Jetpack' ) ) {
		require_once( AMP__DIR__ . '/jetpack-helper.php' );
	}
}

function amp_maybe_add_actions() {
	if ( ! is_singular() ) {
		return;
	}

	$is_amp_endpoint = is_amp_endpoint();

	$post = get_queried_object();
	$supports = does_this_post_support_amp( $post );

	if ( ! $supports ) {
		if ( $is_amp_endpoint ) {
			wp_safe_redirect( get_permalink( $post->ID ) );
			exit;
		}
		return;
	}

	if ( $is_amp_endpoint ) {
		amp_prepare_render();
	} else {
		amp_add_template_actions();
	}
}

function amp_add_template_actions() {
	add_action( 'wp_head', 'amp_canonical' );
}

function amp_prepare_render() {
	add_action( 'template_redirect', 'amp_render' );
}

function amp_render() {
	$post_id = get_queried_object_id();
	do_action( 'pre_amp_render_post', $post_id );

	require( AMP__DIR__ . '/includes/amp-template-actions.php' );

	$template = new AMP_Post_Template( $post_id );
	$template->load();
	exit;
}

function amp_get_url( $post_id ) {
	if ( '' != get_option( 'permalink_structure' ) ) {
		$amp_url = trailingslashit( get_permalink( $post_id ) ) . user_trailingslashit( AMP_QUERY_VAR, 'single_amp' );
	} else {
		$amp_url = add_query_arg( AMP_QUERY_VAR, absint( $post_id ), home_url() );
	}

	return apply_filters( 'amp_get_url', $amp_url, $post_id );
}

function amp_canonical() {
	if ( false === apply_filters( 'amp_show_canonical', true ) ) {
		return;
	}

	$amp_url = amp_get_url( get_queried_object_id() );
	printf( '<link rel="amphtml" href="%s" />', esc_url( $amp_url ) );
}

function does_this_post_support_amp( $post ) {
	// Because `add_rewrite_endpoint` doesn't let us target specific post_types :(
	if ( ! post_type_supports( $post->post_type, AMP_QUERY_VAR ) ) {
		return false;
	}

	if ( true === apply_filters( 'amp_skip_post', false, $post->ID ) ) {
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
	return false !== get_query_var( AMP_QUERY_VAR, false );
}

function amp_get_asset_url( $file ) {
	return plugins_url( sprintf( 'assets/%s', $file ), __FILE__ );
}
