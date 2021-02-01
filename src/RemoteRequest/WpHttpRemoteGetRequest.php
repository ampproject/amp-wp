<?php
/**
 * Class WpHttpRemoteGetRequest.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\RemoteRequest;

use AmpProject\Exception\FailedToGetFromRemoteUrl;
use AmpProject\RemoteGetRequest;
use AmpProject\RemoteRequest\RemoteGetRequestResponse;
use AmpProject\Response;
use Traversable;
use WP_Error;
use WP_Http;

/**
 * Remote request transport using the WordPress WP_Http abstraction layer.
 *
 * @package AmpProject\AmpWP
 * @since 2.0
 * @internal
 */
final class WpHttpRemoteGetRequest implements RemoteGetRequest {

	/**
	 * Default timeout value to use in seconds.
	 *
	 * @var int
	 */
	const DEFAULT_TIMEOUT = 5;

	/**
	 * Default number of retry attempts to do.
	 *
	 * @var int
	 */
	const DEFAULT_RETRIES = 2;

	/**
	 * List of HTTP status codes that are worth retrying for.
	 *
	 * @var int[]
	 */
	const RETRYABLE_STATUS_CODES = [
		WP_Http::REQUEST_TIMEOUT,
		WP_Http::LOCKED,
		WP_Http::TOO_MANY_REQUESTS,
		WP_Http::INTERNAL_SERVER_ERROR,
		WP_Http::SERVICE_UNAVAILABLE,
		WP_Http::GATEWAY_TIMEOUT,
	];

	/**
	 * Whether to verify SSL certificates or not.
	 *
	 * @var boolean
	 */
	private $ssl_verify;

	/**
	 * Timeout value to use in seconds.
	 *
	 * @var int
	 */
	private $timeout;

	/**
	 * Number of retry attempts to do for an error that is worth retrying.
	 *
	 * @var int
	 */
	private $retries;

	/**
	 * Instantiate a WpHttpRemoteGetRequest object.
	 *
	 * @param bool $ssl_verify Optional. Whether to verify SSL certificates. Defaults to true.
	 * @param int  $timeout    Optional. Timeout value to use in seconds. Defaults to 10.
	 * @param int  $retries    Optional. Number of retry attempts to do if a status code was thrown that is worth
	 *                         retrying. Defaults to 2.
	 */
	public function __construct( $ssl_verify = true, $timeout = self::DEFAULT_TIMEOUT, $retries = self::DEFAULT_RETRIES ) {
		if ( ! is_int( $timeout ) || $timeout < 0 ) {
			$timeout = self::DEFAULT_TIMEOUT;
		}

		if ( ! is_int( $retries ) || $retries < 0 ) {
			$retries = self::DEFAULT_RETRIES;
		}

		$this->ssl_verify = $ssl_verify;
		$this->timeout    = $timeout;
		$this->retries    = $retries;
	}

	/**
	 * Do a GET request to retrieve the contents of a remote URL.
	 *
	 * @param string $url URL to get.
	 * @return Response Response for the executed request.
	 * @throws FailedToGetFromRemoteUrl If retrieving the contents from the URL failed.
	 */
	public function get( $url ) {
		$retries_left = $this->retries;
		do {
			$args = [
				'method'    => 'GET',
				'timeout'   => $this->timeout,
				'sslverify' => $this->ssl_verify,
			];

			$response = wp_remote_get( $url, $args );

			if ( $response instanceof WP_Error ) {
				return new RemoteGetRequestResponse( $response->get_error_message(), [], 500 );
			}

			if ( ! isset( $response['response']['code'] ) ) {
				return new RemoteGetRequestResponse(
					isset( $response['response']['message'] )
						? $response['response']['message']
						: 'Unknown error',
					[],
					500
				);
			}

			$status = isset( $response['response']['code'] ) ? $response['response']['code'] : 500;

			if ( $status < 200 || $status >= 300 ) {
				if ( ! $retries_left || in_array( $status, self::RETRYABLE_STATUS_CODES, true ) === false ) {
					throw FailedToGetFromRemoteUrl::withHttpStatus( $url, $status );
				}

				continue;
			}

			$headers = $response['headers'];

			if ( $headers instanceof Traversable ) {
				$headers = iterator_to_array( $headers );
			}

			if ( ! is_array( $headers ) ) {
				$headers = [];
			}

			return new RemoteGetRequestResponse( $response['body'], $headers, (int) $status );
		} while ( $retries_left-- );

		// This should never be triggered, but we want to ensure we always have a typed return value,
		// to make PHPStan happy.
		return new RemoteGetRequestResponse( '', [], 500 );
	}
}
