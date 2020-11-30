<?php
/**
 * Class SuffixUrlStructure.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\PairedUrlStructure;

use AmpProject\AmpWP\PairedUrlStructure;

/**
 * Descriptor for paired URL structures that end in /amp/ endpoint suffix.
 *
 * @package AmpProject\AmpWP
 * @since 2.1
 * @internal
 */
final class SuffixUrlStructure extends PairedUrlStructure {

	/**
	 * Turn a given URL into a paired AMP URL.
	 *
	 * @param string $url URL (or REQUEST_URI).
	 * @return string AMP URL.
	 */
	public function add_endpoint( $url ) {
		return static::add_path_suffix( $url );
	}

	/**
	 * Determine a given URL is for a paired AMP request.
	 *
	 * @param string $url URL (or REQUEST_URI).
	 * @return bool True if the AMP query parameter is set with the required value, false if not.
	 */
	public function has_endpoint( $url ) {
		return self::has_path_suffix( $url ) || self::has_query_var( $url );
	}

	/**
	 * Remove the paired AMP endpoint from a given URL.
	 *
	 * @param string $url URL (or REQUEST_URI).
	 * @return string URL with AMP stripped.
	 */
	public function remove_endpoint( $url ) {
		$url = self::remove_path_suffix( $url );
		$url = self::remove_query_var( $url );
		return $url;
	}
}
