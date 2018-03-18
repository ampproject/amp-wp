<?php

class AMP_WP_Utils {
	/**
	 * wp_parse_url in < WordPress 4.7 does not respect the component arg, so we're adding this helper so we can use it.
	 *
	 * This can be removed once 4.8 is out and we bump up our min supported WP version.
	 */
	public static function parse_url( $url, $component = -1 ) {
		$parsed = wp_parse_url( $url, $component );

		// Because < 4.7 always returned a full array regardless of component
		if ( -1 !== $component && is_array( $parsed ) ) {
			return self::_get_component_from_parsed_url_array( $parsed, $component );
		}

		return $parsed;
	}

	/**
	 * Included for 4.6 back-compat
	 *
	 * Copied from https://developer.wordpress.org/reference/functions/_get_component_from_parsed_url_array/
	 */
	protected static function _get_component_from_parsed_url_array( $url_parts, $component = -1 ) {
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
	 */
	protected static function _wp_translate_php_url_constant_to_key( $constant ) {
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
