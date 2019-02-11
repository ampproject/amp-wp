<?php
/**
 * Class AMP_WP_Utils
 *
 * @package AMP
 */

/**
 * Class with static WordPress utility methods.
 *
 * @since 0.5
 *
 * @deprecated 0.7 As WordPress 4.7 is our minimum supported version.
 */
class AMP_WP_Utils {
	/**
	 * The core function wp_parse_url in < WordPress 4.7 does not respect the component arg. This helper lets us use it.
	 *
	 * Don't use.
	 *
	 * @deprecated 0.7 wp_parse_url() is now used instead.
	 *
	 * @param string $url       The raw URL. Can be false if the URL failed to parse.
	 * @param int    $component The specific component to retrieve. Use one of the PHP
	 *                          predefined constants to specify which one.
	 *                          Defaults to -1 (= return all parts as an array).
	 * @return mixed False on parse failure; Array of URL components on success;
	 *               When a specific component has been requested: null if the component
	 *               doesn't exist in the given URL; a string or - in the case of
	 *               PHP_URL_PORT - integer when it does. See parse_url()'s return values.
	 */
	public static function parse_url( $url, $component = -1 ) {
		_deprecated_function( __METHOD__, '0.7', 'wp_parse_url' );
		$parsed = wp_parse_url( $url, $component );

		// Because < 4.7 always returned a full array regardless of component.
		if ( -1 !== $component && is_array( $parsed ) ) {
			return self::_get_component_from_parsed_url_array( $parsed, $component );
		}

		return $parsed;
	}

	/**
	 * Included for 4.6 back-compat
	 *
	 * Copied from https://developer.wordpress.org/reference/functions/_get_component_from_parsed_url_array/
	 *
	 * @deprecated 0.7
	 *
	 * @param array|false $url_parts The parsed URL. Can be false if the URL failed to parse.
	 * @param int         $component The specific component to retrieve. Use one of the PHP
	 *                               predefined constants to specify which one.
	 *                               Defaults to -1 (= return all parts as an array).
	 * @return mixed False on parse failure; Array of URL components on success;
	 *               When a specific component has been requested: null if the component
	 *               doesn't exist in the given URL; a string or - in the case of
	 *               PHP_URL_PORT - integer when it does. See parse_url()'s return values.
	 */
	protected static function _get_component_from_parsed_url_array( $url_parts, $component = -1 ) { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
		if ( -1 === $component ) {
			return $url_parts;
		}

		$key = self::_wp_translate_php_url_constant_to_key( $component );
		if ( false !== $key && is_array( $url_parts ) && isset( $url_parts[ $key ] ) ) {
			return $url_parts[ $key ];
		}

		return null;
	}

	/**
	 * Included for 4.6 back-compat
	 *
	 * Copied from https://developer.wordpress.org/reference/functions/_wp_translate_php_url_constant_to_key/
	 *
	 * @param int $constant The specific component to retrieve. Use one of the PHP
	 *                      predefined constants to specify which one.
	 * @return mixed False if component not found. string or integer if found.
	 *
	 * @deprecated 0.7
	 */
	protected static function _wp_translate_php_url_constant_to_key( $constant ) { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
		$translation = array(
			PHP_URL_SCHEME   => 'scheme',
			PHP_URL_HOST     => 'host',
			PHP_URL_PORT     => 'port',
			PHP_URL_USER     => 'user',
			PHP_URL_PASS     => 'pass',
			PHP_URL_PATH     => 'path',
			PHP_URL_QUERY    => 'query',
			PHP_URL_FRAGMENT => 'fragment',
		);

		if ( isset( $translation[ $constant ] ) ) {
			return $translation[ $constant ];
		}

		return false;
	}
}
