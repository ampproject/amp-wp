<?php
/**
 * Deprecated functions.
 *
 * @package AMP
 */

/**
 * Load classes for FasterImage.
 *
 * @deprecated This is obsolete now that there is an autoloader.
 */
function amp_load_fasterimage_classes() {
	_deprecated_function( __FUNCTION__, '0.6' );
}

/**
 * Get FasterImage client for user agent.
 *
 * @deprecated This function is no longer used in favor of just instantiating the class.
 *
 * @param string $user_agent User Agent.
 * @return \FasterImage\FasterImage Instance.
 */
function amp_get_fasterimage_client( $user_agent ) {
	_deprecated_function( __FUNCTION__, '1.0' );
	return new FasterImage\FasterImage( $user_agent );
}
