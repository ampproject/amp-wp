<?php
/**
 * Callbacks for adding AMP-related things to the main theme.
 *
 * @codeCoverageIgnore
 * @deprecated Function in this file has been moved to amp-helper-functions.php.
 * @package AMP
 */

_deprecated_file(
	__FILE__,
	'1.0',
	null,
	sprintf(
		/* translators: 1: amp_add_amphtml_link(). 2: amp-helper-functions.php */
		esc_html__( 'Use %1$s function which is already included from %2$s', 'amp' ),
		'amp_add_amphtml_link()',
		'amp-helper-functions.php'
	)
);

/**
 * Add amphtml link to frontend.
 *
 * @deprecated Use amp_add_amphtml_link() instead.
 *
 * @since 0.2
 * @since 1.0 Deprecated
 * @see amp_add_amphtml_link()
 */
function amp_frontend_add_canonical() {
	_deprecated_function( __FUNCTION__, '1.0', 'amp_add_amphtml_link' );
	amp_add_amphtml_link();
}
