<?php
/**
 * Load the reader mode template.
 *
 * @package AMP
 */

the_post();

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
