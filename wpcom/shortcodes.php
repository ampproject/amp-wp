<?php
/**
 * Shortcode functions for WordPress.com.
 *
 * @package AMP
 */

_deprecated_file( __FILE__, '0.6' );

/**
 * Add custom embeds for WordPress.com.
 *
 * @deprecated Now PollDaddy is supported in core AMP.
 * @param array $embed_handler_classes Embed handler classes.
 * @return mixed
 */
function wpcom_amp_add_custom_embeds( $embed_handler_classes ) {
	_deprecated_function( __FUNCTION__, '0.6' );
	return $embed_handler_classes;
}
