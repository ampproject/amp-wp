<?php
/**
 * Class CachedRemoteGetRequest.
 *
 * @package Amp\AmpWP
 */

namespace Amp\AmpWP;

use Amp\Exception\FailedToGetFromRemoteUrl;
use Amp\RemoteGetRequest;
use Amp\RemoteRequest\RemoteGetRequestResponse;
use Amp\Response;

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
     * Whether to use Cache-Control headers to decide on expiry times if available.
     *
     * @var bool
     */
	private $useCacheControl;

	/**
	 * Instantiate a CachedRemoteGetRequest object.
	 *
	 * This is a decorator that can wrap around an existing remote request object to add a caching layer.
	 *
	 * @param RemoteGetRequest $remote_request  Remote request object to decorate with caching.
	 * @param int              $expiry          Optional. Default cache expiry in seconds. Defaults to 24 hours.
     * @param bool             $useCacheControl Optional. Use Cache-Control headers for expiry if available. Defaults to
     *                                          true.
	 */
	public function __construct( RemoteGetRequest $remote_request, $expiry = 24 * HOUR_IN_SECONDS, $useCacheControl = true ) {
		$this->remote_request  = $remote_request;
		$this->expiry          = $expiry;
		$this->useCacheControl = $useCacheControl;
	}

	/**
	 * Do a GET request to retrieve the contents of a remote URL.
	 *
	 * @param string $url URL to get.
     * @return Response Response for the executed request.
	 * @throws FailedToGetFromRemoteUrl If retrieving the contents from the URL failed.
	 */
	public function get( $url ) {
		$cache_key = 'amp_remote_request_' . md5( __CLASS__ . $url );
		$result    = get_transient( $cache_key );

		if ( false === $result ) {
			$result = $this->remote_request->get( $url );
			set_transient( $cache_key, $result, $this->expiry );
		}

		return new RemoteGetRequestResponse( $result );
	}
}
