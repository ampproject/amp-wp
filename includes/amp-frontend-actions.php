<?php
/**
 * Callbacks for adding AMP-related things to the main theme.
 *
 * @deprecated Function in this file has been moved to amp-helper-functions.php.
 * @package AMP
 */

_deprecated_file( __FILE__, '1.0', null, esc_html__( 'Use amp_add_amphtml_link() function which is already included from amp-helper-functions.php', 'amp' ) );

/**
 * Add amphtml link to frontend.
 *
 * @deprecated
 *
 * @since 0.2
 * @since 1.0 Deprecated
 * @see amp_add_amphtml_link()
 */
function amp_frontend_add_canonical() {
	_deprecated_function( __FUNCTION__, '1.0', 'amp_add_amphtml_link' );
	amp_add_amphtml_link();
}
