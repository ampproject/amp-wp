<?php
/**
 * Class LegacyReaderUrlStructure.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\PairedUrlStructure;

use AmpProject\AmpWP\PairedUrlStructure;

/**
 * Descriptor for paired URL structures that end in /amp/ path suffix for non-hierarchical posts and ?amp for others.
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
		$post_id = url_to_postid( $url );

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

		// Make sure any existing AMP endpoint is removed.
		$url = $this->paired_urls->remove_path_suffix( $url );
		$url = $this->paired_urls->remove_query_var( $url );

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
			$amp_url = $this->paired_urls->add_query_var( $url, '' );
		} else {
			$amp_url = $this->paired_urls->add_path_suffix( $url );
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
		return $this->paired_urls->has_query_var( $url ) || $this->paired_urls->has_path_suffix( $url );
	}

	/**
	 * Remove the paired AMP endpoint from a given URL.
	 *
	 * @param string $url URL (or REQUEST_URI).
	 * @return string URL with AMP stripped.
	 */
	public function remove_endpoint( $url ) {
		$url = $this->paired_urls->remove_query_var( $url );
		$url = $this->paired_urls->remove_path_suffix( $url );
		return $url;
	}
}
