<?php
/**
 * Class CachedRemoteRequest.
 *
 * @package Amp\AmpWP
 */

namespace Amp\AmpWP;

use Amp\Exception\FailedToFetchFromRemoteUrl;
use Amp\RemoteRequest;

/**
 * Caching decorator for RemoteRequest implementations.
 *
 * Caching uses WordPress transients.
 *
 * @package Amp\AmpWP
 */
final class CachedRemoteRequest implements RemoteRequest {

	/**
	 * Remote request object to decorate with caching.
	 *
	 * @var RemoteRequest
	 */
	private $remote_request;

	/**
	 * Cache expiration time in seconds.
	 *
	 * @var int
	 */
	private $expiry;

	/**
	 * Instantiate a CachedRemoteRequest object.
	 *
	 * This is a decorator that can wrap around an existing remote request object to add a caching layer.
	 *
	 * @param RemoteRequest $remote_request Remote request object to decorate with caching.
	 * @param int           $expiry         Optional. Cache expiry in seconds. Defaults to 24 hours.
	 */
	public function __construct( RemoteRequest $remote_request, $expiry = 24 * HOUR_IN_SECONDS ) {
		$this->remote_request = $remote_request;
		$this->expiry         = $expiry;
	}

	/**
	 * Fetch the contents of a remote request.
	 *
	 * @param string $url URL to fetch.
	 * @return string Contents retrieved from the remote URL.
	 * @throws FailedToFetchFromRemoteUrl If fetching the contents from the URL failed.
	 */
	public function fetch( $url ) {
		$cache_key = 'amp_remote_request_' . md5( __CLASS__ . $url );
		$result    = get_transient( $cache_key );

		if ( false === $result ) {
			$result = $this->remote_request->fetch( $url );
			set_transient( $cache_key, $result, $this->expiry );
		}

		return $result;
	}
}
