<?php
/**
 * Post template functions and polyfills.
 *
 * @package AMP
 */

// Was only available in Customizer > 4.6.
if ( ! function_exists( 'sanitize_hex_color' ) ) {
	/**
	 * Sanitizes a hex color.
	 *
	 * Only used as polyfill for WordPress < 4.6.
	 *
	 * @param string $color Hex color.
	 * @return string Sanitized hex color.
	 */
	function sanitize_hex_color( $color ) {
		if ( '' === $color ) {
			return '';
		}

		// 3 or 6 hex digits, or the empty string.
		if ( preg_match( '|^#([A-Fa-f0-9]{3}){1,2}$|', $color ) ) {
			return $color;
		}
	}
}
