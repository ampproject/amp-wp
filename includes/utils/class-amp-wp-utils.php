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

	public static function amp_get_permalink( $post_id ) {
		$pre_url = apply_filters( 'amp_pre_get_permalink', false, $post_id );

		if ( false !== $pre_url ) {
			return $pre_url;
		}

		$structure = get_option( 'permalink_structure' );
		if ( empty( $structure ) ) {
			$amp_url = add_query_arg( AMP_QUERY_VAR, 1, get_permalink( $post_id ) );
		} else {
			$amp_url = trailingslashit( get_permalink( $post_id ) ) . user_trailingslashit( AMP_QUERY_VAR, 'single_amp' );
		}

		return apply_filters( 'amp_get_permalink', $amp_url, $post_id );
	}

	public static function post_supports_amp( $post ) {
		// Because `add_rewrite_endpoint` doesn't let us target specific post_types :(
		if ( ! post_type_supports( $post->post_type, AMP_QUERY_VAR ) ) {
			return false;
		}

		if ( post_password_required( $post ) ) {
			return false;
		}

		if ( true === apply_filters( 'amp_skip_post', false, $post->ID, $post ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Are we currently on an AMP URL?
	 *
	 * Note: will always return `false` if called before the `parse_query` hook.
	 */
	public static function is_amp_endpoint() {
		if ( 0 === did_action( 'parse_query' ) ) {
			_doing_it_wrong( __FUNCTION__, sprintf( esc_html__( "is_amp_endpoint() was called before the 'parse_query' hook was called. This function will always return 'false' before the 'parse_query' hook is called.", 'amp' ) ), '0.4.2' );
		}

		return false !== get_query_var( AMP_QUERY_VAR, false );
	}

	public static function amp_get_asset_url( $file ) {
		return plugins_url( sprintf( 'assets/%s', $file ), AMP__FILE__ );
	}
}
