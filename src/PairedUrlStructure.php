<?php
/**
 * Abstract class PairedUrl.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP;

/**
 * Interface for classes that implement a PairedUrl.
 *
 * @package AmpProject\AmpWP
 * @since 2.1
 * @internal
 * @todo Make this not internal?
 */
abstract class PairedUrlStructure {

	/**
	 * Strip paired query var.
	 *
	 * @param string $url URL.
	 *
	 * @return string URL.
	 */
	public static function remove_query_var( $url ) {
		return remove_query_arg( amp_get_slug(), $url );
	}

	/**
	 * Determine whether the given URL has the endpoint suffix.
	 *
	 * @param string $url URL.
	 * @return bool Has endpoint suffix.
	 */
	public static function has_path_suffix( $url ) {
		$path    = wp_parse_url( $url, PHP_URL_PATH );
		$pattern = sprintf(
			':/%s/?$:',
			preg_quote( amp_get_slug(), ':' )
		);

		return (bool) preg_match( $pattern, $path );
	}

	/**
	 * Strip paired endpoint suffix.
	 *
	 * @param string $url URL.
	 * @return string URL.
	 */
	public static function remove_path_suffix( $url ) {
		return preg_replace(
			sprintf(
				':/%s(?=/?(\?|#|$)):',
				preg_quote( amp_get_slug(), ':' )
			),
			'',
			$url
		);
	}

	/**
	 * Determine whether the given URL has the query var.
	 *
	 * @param string $url URL.
	 * @return bool Has query var.
	 */
	public static function has_query_var( $url ) {
		$parsed_url = wp_parse_url( $url );
		if ( ! empty( $parsed_url['query'] ) ) {
			$query_vars = [];
			wp_parse_str( $parsed_url['query'], $query_vars );
			if ( isset( $query_vars[ amp_get_slug() ] ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Get paired AMP URL using query var (`?amp=1`).
	 *
	 * @param string $url   URL.
	 * @param string $value Value. Defaults to 1.
	 * @return string AMP URL.
	 */
	public static function add_query_var( $url, $value = '1' ) {
		return add_query_arg( amp_get_slug(), $value, $url );
	}

	/**
	 * Get paired AMP URL using a endpoint suffix.
	 *
	 * @param string $url URL.
	 *
	 * @return string AMP URL.
	 */
	public static function add_path_suffix( $url ) {
		$url = self::remove_path_suffix( $url );

		$parsed_url = array_merge(
			wp_parse_url( home_url( '/' ) ),
			wp_parse_url( $url )
		);

		if ( empty( $parsed_url['scheme'] ) ) {
			$parsed_url['scheme'] = is_ssl() ? 'https' : 'http';
		}
		if ( ! isset( $parsed_url['host'] ) ) {
			$parsed_url['host'] = isset( $_SERVER['HTTP_HOST'] ) ? wp_unslash( $_SERVER['HTTP_HOST'] ) : 'localhost';
		}

		$parsed_url['path']  = trailingslashit( $parsed_url['path'] );
		$parsed_url['path'] .= user_trailingslashit( amp_get_slug(), 'amp' );

		$amp_url = $parsed_url['scheme'] . '://';
		if ( isset( $parsed_url['user'] ) ) {
			$amp_url .= $parsed_url['user'];
			if ( isset( $parsed_url['pass'] ) ) {
				$amp_url .= ':' . $parsed_url['pass'];
			}
			$amp_url .= '@';
		}
		$amp_url .= $parsed_url['host'];
		if ( isset( $parsed_url['port'] ) ) {
			$amp_url .= ':' . $parsed_url['port'];
		}
		$amp_url .= $parsed_url['path'];
		if ( isset( $parsed_url['query'] ) ) {
			$amp_url .= '?' . $parsed_url['query'];
		}
		if ( isset( $parsed_url['fragment'] ) ) {
			$amp_url .= '#' . $parsed_url['fragment'];
		}

		return $amp_url;
	}

	/**
	 * Determines whether the structure needs to manipulate request parsing.
	 *
	 * @return bool
	 */
	public function needs_request_parsing() {
		return false;
	}

	/**
	 * Turn a given URL into a paired AMP URL.
	 *
	 * @param string $url URL.
	 * @return string AMP URL.
	 */
	abstract public function add_endpoint( $url );

	/**
	 * Determine a given URL is for a paired AMP request.
	 *
	 * @param string $url URL to examine. If empty, will use the current URL.
	 * @return bool True if the AMP query parameter is set with the required value, false if not.
	 */
	abstract public function has_endpoint( $url );

	/**
	 * Remove the paired AMP endpoint from a given URL.
	 *
	 * @param string $url URL.
	 * @return string URL with AMP stripped.
	 */
	abstract public function remove_endpoint( $url );
}
