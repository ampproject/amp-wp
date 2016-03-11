<?php
/**
 * Plugin Name: AMP
 * Description: Add AMP support to your WordPress site.
 * Plugin URI: https://github.com/automattic/amp-wp
 * Author: Automattic
 * Author URI: https://automattic.com
 * Version: 0.3.2
 * Text Domain: amp
 * Domain Path: /languages/
 * License: GPLv2 or later
 */

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

	define( 'AMP_QUERY_VAR', apply_filters( 'amp_query_var', 'amp' ) );

	do_action( 'amp_init' );

	load_plugin_textdomain( 'amp', false, plugin_basename( AMP__DIR__ ) . '/languages' );

	add_rewrite_endpoint( AMP_QUERY_VAR, EP_PERMALINK );
	add_post_type_support( 'post', AMP_QUERY_VAR );

	if ( is_admin() || is_customize_preview() ) {
		amp_add_backend_actions();
	}

	add_action( 'wp', 'amp_maybe_add_actions' );

	if ( class_exists( 'Jetpack' ) && ! ( defined( 'IS_WPCOM' ) && IS_WPCOM ) ) {
		require_once( AMP__DIR__ . '/jetpack-helper.php' );
	}
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

/**
 * Loads the backend actions callbacks.
 *
 * @since 0.3
 */
function amp_add_backend_actions() {
	require_once( AMP__DIR__ . '/includes/admin/amp-backend-actions.php' );
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

/**
 * Bootstraps the AMP customizer.
 *
 * If the AMP customizer is enabled, initially drop the core widgets and menus panels. If the current
 * preview page isn't flagged as an AMP template, the core panels will be re-added and the AMP panel
 * hidden.
 *
 * @internal This callback must be hooked before priority 10 on 'plugins_loaded' to properly unhook
 *           the core panels.
 */
function _amp_bootstrap_customizer() {
	/**
	 * Filter whether to enable the AMP template customizer functionality.
	 *
	 * @param bool $enable Whether to enable the AMP customizer. Default true.
	 */
	$amp_customizer_enabled = apply_filters( 'amp_customizer_is_enabled', true );

	if ( true === $amp_customizer_enabled ) {
		// Drop core panels.
		add_filter( 'customize_loaded_components', '_amp_drop_core_panels'    );

		// Initialize AMP customizer
		add_action( 'customize_register',          'amp_init_customizer', 500 );

		// Add the Appearance > AMP link to the admin menu.
		add_action( 'admin_menu',                  'amp_add_customizer_link'  );
	}
}
add_action( 'plugins_loaded', '_amp_bootstrap_customizer', 9 );

/**
 * Filters the core components to unhook the menus and widgets panels and retain
 * 'selective_refresh', a component introduced in 4.5.
 *
 * @access private
 *
 * @return array Array of core Customizer components to keep active.
 */
function _amp_drop_core_panels() {
	return array( 'selective_refresh' );
}
