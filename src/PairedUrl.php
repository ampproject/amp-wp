<?php
/**
 * Class PairedUrl.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP;

use AmpProject\AmpWP\Infrastructure\Service;

/**
 * Service for manipulating a paired URL.
 *
 * @package AmpProject\AmpWP
 * @since 2.1
 */
final class PairedUrl implements Service {

	/**
	 * Strip paired query var.
	 *
	 * @param string $url URL (or REQUEST_URI).
	 * @return string URL.
	 */
	public function remove_query_var( $url ) {
		return remove_query_arg( amp_get_slug(), $url );
	}

	/**
	 * Determine whether the given URL has the endpoint suffix.
	 *
	 * @param string $url URL (or REQUEST_URI).
	 * @return bool Has endpoint suffix.
	 */
	public function has_path_suffix( $url ) {
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
	 * @param string $url URL (or REQUEST_URI).
	 * @return string URL.
	 */
	public function remove_path_suffix( $url ) {
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
	 * @param string $url URL (or REQUEST_URI).
	 * @return bool Has query var.
	 */
	public function has_query_var( $url ) {
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
	 * @param string $url   URL (or REQUEST_URI).
	 * @param string $value Value. Defaults to 1.
	 * @return string AMP URL.
	 */
	public function add_query_var( $url, $value = '1' ) {
		return add_query_arg( amp_get_slug(), $value, $url );
	}

	/**
	 * Get paired AMP URL using a endpoint suffix.
	 *
	 * @todo The URL parsing and serialization logic here should ideally be put into a reusable class.
	 *
	 * @param string $url URL (or REQUEST_URI).
	 * @return string AMP URL.
	 */
	public function add_path_suffix( $url ) {
		$url = $this->remove_path_suffix( $url );

		$parsed_url = wp_parse_url( $url );
		if ( false === $parsed_url ) {
			$parsed_url = [];
		}

		$parsed_url = array_merge(
			wp_parse_url( home_url( '/' ) ),
			$parsed_url
		);

		if ( empty( $parsed_url['scheme'] ) ) {
			$parsed_url['scheme'] = is_ssl() ? 'https' : 'http';
		}
		if ( empty( $parsed_url['host'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$parsed_url['host'] = ! empty( $_SERVER['HTTP_HOST'] ) ? wp_unslash( $_SERVER['HTTP_HOST'] ) : 'localhost';
		}

		$parsed_url['path']  = trailingslashit( $parsed_url['path'] );
		$parsed_url['path'] .= user_trailingslashit( amp_get_slug(), 'amp' );

		$amp_url = $parsed_url['scheme'] . '://';
		if ( ! empty( $parsed_url['user'] ) ) {
			$amp_url .= $parsed_url['user'];
			if ( ! empty( $parsed_url['pass'] ) ) {
				$amp_url .= ':' . $parsed_url['pass'];
			}
			$amp_url .= '@';
		}
		$amp_url .= $parsed_url['host'];
		if ( ! empty( $parsed_url['port'] ) ) {
			$amp_url .= ':' . $parsed_url['port'];
		}
		$amp_url .= $parsed_url['path'];
		if ( ! empty( $parsed_url['query'] ) ) {
			$amp_url .= '?' . $parsed_url['query'];
		}
		if ( ! empty( $parsed_url['fragment'] ) ) {
			$amp_url .= '#' . $parsed_url['fragment'];
		}

		return $amp_url;
	}
}
