<?php
/**
 * Class LegacyReaderUrlStructure.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\PairedUrlStructure;

use AmpProject\AmpWP\PairedUrlStructure;

/**
 * Descriptor for paired URL structures that end in `/amp/` path suffix for non-hierarchical posts and `?amp` for others.
 *
 * @package AmpProject\AmpWP
 * @since 2.1
 * @internal
 */
final class LegacyReaderUrlStructure extends PairedUrlStructure {

	/**
	 * Turn a given URL into a paired AMP URL.
	 *
	 * @param string $url URL (or REQUEST_URI).
	 * @return string AMP URL.
	 */
	public function add_endpoint( $url ) {
		// Make sure any existing AMP endpoint is removed.
		$url = $this->remove_endpoint( $url );

		$post_id = $this->url_to_postid( $url );
		if ( $post_id ) {
			/**
			 * Filters the AMP permalink to short-circuit normal generation.
			 *
			 * Returning a string value in this filter will bypass the `get_permalink()` from being called and the `amp_get_permalink` filter will not apply.
			 *
			 * @since 0.4
			 * @since 1.0 This filter only applies when using the legacy reader paired URL structure.
			 *
			 * @param false $url     Short-circuited URL.
			 * @param int   $post_id Post ID.
			 */
			$pre_url = apply_filters( 'amp_pre_get_permalink', false, $post_id );

			if ( is_string( $pre_url ) ) {
				return $pre_url;
			}
		}

		$parsed_url    = wp_parse_url( $url );
		$use_query_var = (
			// If there are existing query vars, then always use the amp query var as well.
			! empty( $parsed_url['query'] )
			||
			// If no post was found for the URL.
			! $post_id
			||
			// If the post type is hierarchical then the /amp/ endpoint isn't available.
			is_post_type_hierarchical( get_post_type( $post_id ) )
			||
			// Attachment pages don't accept the /amp/ endpoint.
			'attachment' === get_post_type( $post_id )
		);
		if ( $use_query_var ) {
			$amp_url = $this->paired_url->add_query_var( $url, '' );
		} else {
			$amp_url = $this->paired_url->add_path_suffix( $url );
		}

		if ( $post_id ) {
			/**
			 * Filters AMP permalink.
			 *
			 * @since 0.2
			 * @since 1.0 This filter only applies when using the legacy reader paired URL structure.
			 *
			 * @param string $amp_url AMP URL.
			 * @param int    $post_id Post ID.
			 */
			$amp_url = apply_filters( 'amp_get_permalink', $amp_url, $post_id );
		}

		return $amp_url;
	}

	/**
	 * Determine a given URL is for a paired AMP request.
	 *
	 * @param string $url URL (or REQUEST_URI).
	 * @return bool True if the AMP query parameter is set with the required value, false if not.
	 */
	public function has_endpoint( $url ) {
		return $this->paired_url->has_query_var( $url ) || $this->paired_url->has_path_suffix( $url );
	}

	/**
	 * Remove the paired AMP endpoint from a given URL.
	 *
	 * @param string $url URL (or REQUEST_URI).
	 * @return string URL with AMP stripped.
	 */
	public function remove_endpoint( $url ) {
		$url = $this->paired_url->remove_query_var( $url );
		$url = $this->paired_url->remove_path_suffix( $url );
		return $url;
	}

	/**
	 * Cached version of url_to_postid(), which can be expensive.
	 *
	 * Examine a url and try to determine the post ID it represents.
	 *
	 * This is copied from the WordPress.com VIP implementation.
	 *
	 * @link https://github.com/svn2github/wordpress-vip-plugins/blob/4d6f59f9839167d1c11f550610012493c7380dfe/vip-do-not-include-on-wpcom/wpcom-caching.php#L300-L331
	 * @see wpcom_vip_url_to_postid()
	 *
	 * @param string $url Permalink to check.
	 * @return int Post ID, or 0 on failure.
	 */
	private function url_to_postid( $url ) {
		// Can only run after init, since home_url() has not been filtered to the mapped domain prior to that,
		// which will cause url_to_postid() to fail.
		// See <https://vip.wordpress.com/documentation/vip-development-tips-tricks/home_url-vs-site_url/>.
		if ( ! did_action( 'init' ) ) {
			_doing_it_wrong( __METHOD__, 'must be called after the init action, as home_url() has not yet been filtered', '' );
			return 0;
		}

		// Sanity check; no URLs not from this site.
		$host = wp_parse_url( $url, PHP_URL_HOST );
		if ( $host && wp_parse_url( home_url(), PHP_URL_HOST ) !== $host ) {
			return 0;
		}

		$cache_key = md5( $url );
		$post_id   = wp_cache_get( $cache_key, 'url_to_postid' );

		if ( false === $post_id ) {
			$post_id = url_to_postid( $url ); // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.url_to_postid_url_to_postid -- This method implements the caching.
			wp_cache_set( $cache_key, $post_id, 'url_to_postid', 3 * HOUR_IN_SECONDS );
		}

		return $post_id;
	}
}
