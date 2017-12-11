<?php
/**
 * Shortcode functions for WordPress.com.
 *
 * @package AMP
 */

add_filter( 'amp_content_embed_handlers', 'wpcom_amp_add_custom_embeds', 10, 2 );

/**
 * Add custom embeds for WordPress.com.
 *
 * @param array   $embed_handler_classes Embed handler classes.
 * @param WP_Post $post                  Post.
 * @return mixed
 */
function wpcom_amp_add_custom_embeds( $embed_handler_classes, $post ) {
	$embed_handler_classes['WPCOM_AMP_Polldaddy_Embed'] = array();
	return $embed_handler_classes;
}
