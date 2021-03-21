<?php
/**
 * Abstract class PairedUrlStructure.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP;

/**
 * Interface for classes that implement a PairedUrl.
 *
 * @package AmpProject\AmpWP
 * @since 2.1
 */
abstract class PairedUrlStructure {

	/**
	 * Paired URL service.
	 *
	 * @var PairedUrl
	 */
	protected $paired_url;

	/**
	 * PairedUrlStructure constructor.
	 *
	 * @param PairedUrl $paired_url Paired URL service.
	 */
	public function __construct( PairedUrl $paired_url ) {
		$this->paired_url = $paired_url;
	}

	/**
	 * Determine a given URL is for a paired AMP request.
	 *
	 * @param string $url URL (or REQUEST_URI).
	 * @return bool True if the URL has the paired endpoint.
	 */
	public function has_endpoint( $url ) {
		return $url !== $this->remove_endpoint( $url );
	}

	/**
	 * Turn a given URL into a paired AMP URL.
	 *
	 * @param string $url URL (or REQUEST_URI).
	 * @return string AMP URL.
	 */
	abstract public function add_endpoint( $url );

	/**
	 * Remove the paired AMP endpoint from a given URL.
	 *
	 * @param string $url URL (or REQUEST_URI).
	 * @return string URL with AMP stripped.
	 */
	abstract public function remove_endpoint( $url );
}
