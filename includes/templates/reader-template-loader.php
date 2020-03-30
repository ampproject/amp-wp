<?php
/**
 * Load the reader mode template.
 *
 * @package AMP
 */

/**
 * Queried post.
 *
 * @global WP_Post $post
 */
global $post;

// Populate the $post without calling the_post() to prevent entering The Loop. This ensures that templates which
// contain The Loop will still loop over the posts. Otherwise, if a template contains The Loop then calling the_post()
// here will advance the WP_Query::$current_post to the next_post. See WP_Query::the_post().
$post = get_queried_object(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
setup_postdata( $post );

/**
 * Fires before rendering a post in AMP.
 *
 * This action is not triggered when 'amp' theme support is present. Instead, you should use 'template_redirect' action and check if `is_amp_endpoint()`.
 *
 * @since 0.2
 *
 * @param int $post_id Post ID.
 */
do_action( 'pre_amp_render_post', get_queried_object_id() );

require_once AMP__DIR__ . '/includes/amp-post-template-functions.php';
amp_post_template_init_hooks();

$amp_post_template = new AMP_Post_Template( get_queried_object_id() );
$amp_post_template->load();
