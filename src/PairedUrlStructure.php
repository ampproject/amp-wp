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
 */
abstract class PairedUrlStructure {

	/**
	 * Paired URLs service.
	 *
	 * @var PairedUrls
	 */
	protected $paired_urls;

	/**
	 * PairedUrlStructure constructor.
	 *
	 * @param PairedUrls $paired_urls Paired URLs service.
	 */
	public function __construct( PairedUrls $paired_urls ) {
		$this->paired_urls = $paired_urls;
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
