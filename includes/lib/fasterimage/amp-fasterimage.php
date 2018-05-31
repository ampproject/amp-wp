<?php
/**
 * Functions for FasterImage.
 *
 * @package AMP
 */

/**
 * Load classes for FasterImage.
 *
 * This is obsolete now that there is an autoloader.
 *
 * @deprecated
 */
function amp_load_fasterimage_classes() {
	_deprecated_function( __FUNCTION__, '0.6' );
}

/**
 * Get FasterImage client for user agent.
 *
 * @param string $user_agent User Agent.
 * @return \FasterImage\FasterImage Instance.
 */
function amp_get_fasterimage_client( $user_agent ) {
	return new FasterImage\FasterImage( $user_agent );
}
