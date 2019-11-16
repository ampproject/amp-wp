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

amp_add_post_template_actions();

$amp_post_template = new AMP_Post_Template( get_queried_object_id() );
$amp_post_template->load();
