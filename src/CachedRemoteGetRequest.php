<?php
/**
 * Class CachedRemoteGetRequest.
 *
 * @package Amp\AmpWP
 */

namespace Amp\AmpWP;

use Amp\Exception\FailedToGetFromRemoteUrl;
use Amp\RemoteGetRequest;

/**
 * Caching decorator for RemoteGetRequest implementations.
 *
 * Caching uses WordPress transients.
 *
 * @package Amp\AmpWP
 */
final class CachedRemoteGetRequest implements RemoteGetRequest {

	/**
	 * Remote request object to decorate with caching.
	 *
	 * @var RemoteGetRequest
	 */
	private $remote_request;

	/**
	 * Cache expiration time in seconds.
	 *
	 * @var int
	 */
	private $expiry;

	/**
	 * Instantiate a CachedRemoteGetRequest object.
	 *
	 * This is a decorator that can wrap around an existing remote request object to add a caching layer.
	 *
	 * @param RemoteGetRequest $remote_request Remote request object to decorate with caching.
	 * @param int              $expiry         Optional. Cache expiry in seconds. Defaults to 24 hours.
	 */
	public function __construct( RemoteGetRequest $remote_request, $expiry = 24 * HOUR_IN_SECONDS ) {
		$this->remote_request = $remote_request;
		$this->expiry         = $expiry;
	}

	/**
	 * Do a GET request to retrieve the contents of a remote URL.
	 *
	 * @param string $url URL to get.
	 * @return string Contents retrieved from the remote URL.
	 * @throws FailedToGetFromRemoteUrl If retrieving the contents from the URL failed.
	 */
	public function get( $url ) {
		$cache_key = 'amp_remote_request_' . md5( __CLASS__ . $url );
		$result    = get_transient( $cache_key );

		if ( false === $result ) {
			$result = $this->remote_request->get( $url );
			set_transient( $cache_key, $result, $this->expiry );
		}

		return $result;
	}
}
