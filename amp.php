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
require_once(AMP__DIR__ . '/includes/utils/amp-helper-functions.php');
require_once( AMP__DIR__ . '/includes/admin/functions.php' );
require_once( AMP__DIR__ . '/includes/settings/class-amp-customizer-settings.php' );
require_once( AMP__DIR__ . '/includes/settings/class-amp-customizer-design-settings.php' );
require_once( AMP__DIR__ . '/includes/utils/class-amp-dom-utils.php');
require_once(AMP__DIR__ . '/includes/utils/class-amp-utils.php');
require_once(AMP__DIR__ . '/includes/utils/class-amp-render.php');
require_once(AMP__DIR__ . '/includes/actions/class-paired-mode-actions.php');
require_once(AMP__DIR__ . '/includes/actions/class-canonical-mode-actions.php');
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

	add_filter( 'request', 'AMPUtils::amp_force_query_var_value' );
	add_action( 'wp', 'amp_add_actions');

	// Redirect the old url of amp page to the updated url.
	add_filter( 'old_slug_redirect_url', 'AMPUtils::amp_redirect_old_slug_to_new_url' );

	if ( class_exists( 'Jetpack' ) && ! ( defined( 'IS_WPCOM' ) && IS_WPCOM ) ) {
		require_once( AMP__DIR__ . '/jetpack-helper.php' );
	}
}

function amp_add_actions() {

	if ( is_feed()) {
		return;
	}

	if (get_option('amp_canonical') ) {
		CanonicalModeActions::add_actions();
	} else {
		PairedModeActions::add_actions();
	}
}

function amp_add_frontend_actions() {
	require_once(AMP__DIR__ . '/includes/actions/amp-frontend-actions.php');
}


add_action( 'plugins_loaded', 'AMPUtils::_amp_bootstrap_customizer', 9 );

