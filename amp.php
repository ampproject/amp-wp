<?php
/**
 * Plugin Name: AMP
 * Description: Add AMP support to your WordPress site.
 * Author: Automattic
 * Version: 0.1
 * License: GPLv2
 */

define( 'AMP_QUERY_VAR', 'amp' );
if ( ! defined( 'AMP_DEV_MODE' ) ) {
	define( 'AMP_DEV_MODE', defined( 'WP_DEBUG' ) && WP_DEBUG );
}

require_once( dirname( __FILE__ ) . '/class-amp-post.php' );

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
	add_rewrite_endpoint( AMP_QUERY_VAR, EP_PERMALINK | EP_PAGES );

	if ( defined( 'JETPACK__VERSION') && JETPACK__VERSION != 'wpcom' ) {
		require_once( dirname( __FILE__ ) . '/wpcom-helper.php' );
	}
}

add_action( 'wp', 'amp_add_actions' );
function amp_add_actions() {
	if ( ! is_singular() ) {
		return;
	}

	if ( false !== get_query_var( AMP_QUERY_VAR, false ) ) {
		// TODO: check if post_type supports amp
		add_action( 'template_redirect', 'amp_template_redirect' );
	} else {
		add_action( 'wp_head', 'amp_canonical' );
	}
}

function amp_template_redirect() {
	amp_render( get_queried_object_id() );
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
	if ( false === apply_filters( 'show_amp_canonical', true ) ) {
		return;
	}

	$amp_url = amp_get_url( get_queried_object_id() );
	printf( '<link rel="amphtml" href="%s" />', esc_url( $amp_url ) );
}

function amp_render( $post_id ) {
	do_action( 'pre_amp_render', $post_id );
	$amp_post = new AMP_Post( $post_id );
	include( dirname( __FILE__ ) . '/template.php' );
}

