<?php
/**
 * Plugin Name: AMP
 * Description: Add AMP support to your WordPress site.
 * Plugin URI: https://github.com/automattic/amp-wp
 * Author: Automattic
 * Author URI: https://automattic.com
 * Version: 0.2
 * Text Domain: amp
 * Domain Path: /languages/
 * License: GPLv2 or later
 */

define( 'AMP_QUERY_VAR', 'amp' );
if ( ! defined( 'AMP_DEV_MODE' ) ) {
	define( 'AMP_DEV_MODE', defined( 'WP_DEBUG' ) && WP_DEBUG );
}

define( 'AMP__FILE__', __FILE__ );
define( 'AMP__DIR__', dirname( __FILE__ ) );

require_once( AMP__DIR__ . '/includes/amp-helper-functions.php' );

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

	do_action( 'amp_init' );

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
