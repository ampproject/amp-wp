<?php
/**
 * Class CachedData.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\RemoteRequest;

use DateTimeImmutable;
use DateTimeInterface;

/**
 * Serializable object that represents cached data together with its expiry time.
 *
 * @package AmpProject\AmpWP
 */
final class CachedData {

	/**
	 * Cached value.
	 *
	 * @var mixed
	 */
	private $value;

	/**
	 * Expiry time of the cached value.
	 *
	 * @var DateTimeInterface
	 */
	private $expiry;

	/**
	 * Instantiate a CachedData object.
	 *
	 * @param mixed             $value Cached value.
	 * @param DateTimeInterface $expiry Expiry of the cached value.
	 */
	public function __construct( $value, DateTimeInterface $expiry ) {
		$this->value  = $value;
		$this->expiry = $expiry;
	}

	/**
	 * Get the cache value.
	 *
	 * @return mixed Cached value.
	 */
	public function get_value() {
		return $this->value;
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
