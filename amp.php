<?php
/**
 * Plugin Name: AMP
 * Description: Add AMP support to your WordPress site.
 * Plugin URI: https://github.com/gabrielperez/amp-wp
 * Author: gabrielperezs
 * Author URI: https://www.guero.net
 * Version: 0.3.1
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

	add_filter( 'rewrite_rules_array','amp_rewrite_rules', 1, 1);

	do_action( 'amp_init' );

	load_plugin_textdomain( 'amp', false, plugin_basename( AMP__DIR__ ) . '/languages' );

	add_rewrite_endpoint( AMP_QUERY_VAR, EP_PERMALINK | EP_PAGES | EP_ROOT );
	add_post_type_support( 'post', AMP_QUERY_VAR );
	add_post_type_support( 'page', AMP_QUERY_VAR );

	add_action( 'wp', 'amp_maybe_add_actions' );

	if ( class_exists( 'Jetpack' ) && ! ( defined( 'IS_WPCOM' ) && IS_WPCOM ) ) {
		require_once( AMP__DIR__ . '/jetpack-helper.php' );
	}
}

function amp_get_category_base() {
	if ( '' == ($category_base = get_option('category_base',true) ) )
		return 'category';

	return $category_base;
}

function amp_get_tag_base() {
	if ( '' == ($tag_base = get_option('tag_base',true) ) )
		return 'tag';

	return $tag_base;
}

function amp_post_link($permalink, $post, $leavename) {
	return $permalink.'amp/';
}
function amp__get_page_link($permalink, $post, $leavename) {
	return $permalink.'amp/';
}
function amp_term_link($termlink, $term, $taxonomy) {
	if ($taxonomy == 'category') {
		return str_replace('/'.amp_get_category_base().'/','/amp/'.amp_get_category_base().'/', $termlink);
	}
	elseif ($taxonomy == 'post_tag') {
		return str_replace('/'.amp_get_tag_base().'/','/amp/'.amp_get_tag_base().'/', $termlink);
	}
	return $termlink;
}

function amp_author_link($link, $author_id, $author_nicename) {
	return str_replace('/author/','/amp/author/',$link);
}

function amp_maybe_add_actions() {
	if ( ( !is_home() && !is_singular() && !is_category() && !is_author()) && !is_tag() || is_feed() ) {
		return;
	}

	$is_amp_endpoint = is_amp_endpoint();

	if ( !$is_amp_endpoint )  {
		amp_add_frontend_actions();
		return;
	}

	set_query_var('amp-object', get_queried_object());

	if( get_queried_object() === NULL) {
		set_query_var('amp-type', 'archive');
	}
	elseif (get_queried_object() instanceof WP_Term) {
		set_query_var('amp-type', 'archive');
	}
	elseif (get_queried_object() instanceof WP_User) {
		set_query_var('amp-type', 'archive');
	}
	else {
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

		set_query_var('amp-type', 'post');

	}

	amp_prepare_render();
}

function amp_load_classes() {
	require_once( AMP__DIR__ . '/includes/class-amp-common-template.php' ); // this loads everything else
	require_once( AMP__DIR__ . '/includes/class-amp-post-template.php' );
	require_once( AMP__DIR__ . '/includes/class-amp-archive-template.php' );
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

	if (get_query_var('amp-type') == 'archive') {
		$post_id = get_queried_object_id();
		do_action( 'pre_amp_render_post', $post_id );

		amp_add_post_template_actions();
		$template = new AMP_Archive_Template( $post_id );
	}
	else {
		$post_id = get_queried_object_id();
		do_action( 'pre_amp_render_post', $post_id );

		amp_add_post_template_actions();
		$template = new AMP_Post_Template( $post_id );
	}

	add_filter('term_link', 'amp_term_link', 1, 3);
	add_filter('author_link', 'amp_author_link', 1, 3);
	add_filter('_get_page_link', 'amp__get_page_link', 1, 3);
	add_filter('post_link', 'amp_post_link', 1, 3);

	$template->load();
	exit;
}

function amp_rewrite_rules( $rules ) {

    $newrules = array();

    $newrules["^amp/?$"] = 'index.php?amp=1';

    foreach($rules as $key => $value) {
        if (preg_match('/^('.amp_get_category_base().'|'.amp_get_tag_base().'|author)\//',$key)) $newrules["amp/".$key] = $value.'&amp=1';
    }

    return $newrules + $rules;
}
