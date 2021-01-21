<?php
/**
 * Class QueryVarUrlStructure.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\PairedUrlStructure;

use AmpProject\AmpWP\PairedUrlStructure;

/**
 * Descriptor for paired URL structures that include the `?amp=1` query parameter.
 *
 * A query parameter is also known as a query arg and a query var. Given prior usage of "query var" in the codebase,
 * the slug for the paired URL structure is `query_var` whereas in the UI it is presented as "query parameter".
 *
 * @package AmpProject\AmpWP
 * @since 2.1
 * @internal
 */
final class QueryVarUrlStructure extends PairedUrlStructure {

	/**
	 * Turn a given URL into a paired AMP URL.
	 *
	 * @param string $url URL (or REQUEST_URI).
	 * @return string AMP URL.
	 */
	public function add_endpoint( $url ) {
		return $this->paired_url->add_query_var( $url );
	}

	/**
	 * Determine a given URL is for a paired AMP request.
	 *
	 * @param string $url URL (or REQUEST_URI).
	 * @return bool True if the AMP query parameter is set with the required value, false if not.
	 */
	public function has_endpoint( $url ) {
		return $this->paired_url->has_query_var( $url );
	}

	/**
	 * Remove the paired AMP endpoint from a given URL.
	 *
	 * @param string $url URL (or REQUEST_URI).
	 * @return string URL with AMP stripped.
	 */
	public function remove_endpoint( $url ) {
		return $this->paired_url->remove_query_var( $url );
	}
}
