<?php
/**
 * Class CachedResponse.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\RemoteRequest;

use DateTimeImmutable;
use DateTimeInterface;

/**
 * Serializable object that represents a cached response together with its expiry time.
 *
 * @package AmpProject\AmpWP
 */
final class CachedResponse {

	/**
	 * Cached body.
	 *
	 * @var string
	 */
	private $body;

	/**
	 * Cached headers.
	 *
	 * @var array
	 */
	private $headers;

	/**
	 * Cached status code.
	 *
	 * @var int
	 */
	private $status_code;

	/**
	 * Expiry time of the cached value.
	 *
	 * @var DateTimeInterface
	 */
	private $expiry;

	/**
	 * Instantiate a CachedResponse object.
	 *
	 * @param string            $body         Cached body.
	 * @param array             $headers      Cached headers.
	 * @param int               $status_code  Cached status code.
	 * @param DateTimeInterface $expiry       Expiry of the cached value.
	 */
	public function __construct( $body, array $headers, $status_code, DateTimeInterface $expiry ) {
		$this->body        = $body;
		$this->headers     = $headers;
		$this->status_code = $status_code;
		$this->expiry      = $expiry;
	}

	/**
	 * Get the cached body.
	 *
	 * @return string Cached body.
	 */
	public function get_body() {
		return $this->body;
	}

	/**
	 * Get the cached headers.
	 *
	 * @return array Cached headers.
	 */
	public function get_headers() {
		return $this->headers;
	}

	/**
	 * Get the cached status code.
	 *
	 * @return int Cached status code.
	 */
	public function get_status_code() {
		return $this->status_code;
	}

	/**
	 * Determine the validity of the cached response.
	 *
	 * @return bool Whether the cached response if valid.
	 */
	public function is_valid() {
		// $this->headers is typed so no need for sanity check.
		return null !== $this->body && is_int( $this->status_code );
	}

	/**
	 * Get the expiry of the cached value.
	 *
	 * @return DateTimeInterface Expiry of the cached value.
	 */
	public function get_expiry() {
		return $this->expiry;
	}

	/**
	 * Check whether the cached value is expired.
	 *
	 * @return bool Whether the cached value is expired.
	 */
	public function is_expired() {
		return new DateTimeImmutable( 'now' ) > $this->expiry;
	}
}
