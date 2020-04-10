<?php
/**
 * Class CachedRemoteGetRequest.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\RemoteRequest;

use AmpProject\Exception\FailedToGetCachedResponse;
use AmpProject\Exception\FailedToGetFromRemoteUrl;
use AmpProject\RemoteGetRequest;
use AmpProject\RemoteRequest\RemoteGetRequestResponse;
use AmpProject\Response;
use DateTimeImmutable;
use DateTimeInterface;

/**
 * Caching decorator for RemoteGetRequest implementations.
 *
 * Caching uses WordPress transients.
 *
 * @package AmpProject\AmpWP
 */
final class CachedRemoteGetRequest implements RemoteGetRequest {

	/**
	 * Prefix to use to identify transients.
	 *
	 * @var string
	 */
	const TRANSIENT_PREFIX = 'amp_remote_request_';

	/**
	 * Cache control header directive name.
	 *
	 * @var string
	 */
	const CACHE_CONTROL = 'Cache-Control';

	/**
	 * Remote request object to decorate with caching.
	 *
	 * @var RemoteGetRequest
	 */
	private $remote_request;

	/**
	 * Cache expiration time in seconds.
	 *
	 * This will be used by default for successful requests when the 'cache-control: max-age' was not provided.
	 *
	 * @var int
	 */
	private $expiry;

	/**
	 * Minimum cache expiration time in seconds.
	 *
	 * This will be used for failed requests, or for successful requests when the 'cache-control: max-age' is inferior.
	 * Caching will never expire quicker than this minimum.
	 *
	 * @var int
	 */
	private $min_expiry;

	/**
	 * Whether to use Cache-Control headers to decide on expiry times if available.
	 *
	 * @var bool
	 */
	private $use_cache_control;

	/**
	 * Instantiate a CachedRemoteGetRequest object.
	 *
	 * This is a decorator that can wrap around an existing remote request object to add a caching layer.
	 *
	 * @param RemoteGetRequest $remote_request    Remote request object to decorate with caching.
	 * @param int|float        $expiry            Optional. Default cache expiry in seconds. Defaults to 30 days.
	 * @param int|float        $min_expiry        Optional. Default enforced minimum cache expiry in seconds. Defaults
	 *                                            to 24 hours.
	 * @param bool             $use_cache_control Optional. Use Cache-Control headers for expiry if available. Defaults
	 *                                            to true.
	 */
	public function __construct(
		RemoteGetRequest $remote_request,
		$expiry = MONTH_IN_SECONDS,
		$min_expiry = DAY_IN_SECONDS,
		$use_cache_control = true
	) {
		$this->remote_request    = $remote_request;
		$this->expiry            = (int) $expiry;
		$this->min_expiry        = (int) $min_expiry;
		$this->use_cache_control = (bool) $use_cache_control;
	}

	/**
	 * Do a GET request to retrieve the contents of a remote URL.
	 *
	 * @todo Should this also respect additional Cache-Control directives like 'no-cache'?
	 *
	 * @param string $url URL to get.
	 * @return Response Response for the executed request.
	 * @throws FailedToGetCachedResponse If retrieving the contents from the URL failed.
	 */
	public function get( $url ) {
		$cache_key       = self::TRANSIENT_PREFIX . md5( __CLASS__ . $url );
		$cached_response = get_transient( $cache_key );
		$headers         = [];

		if ( false !== $cached_response ) {
			if ( PHP_MAJOR_VERSION >= 7 ) {
				$cached_response = unserialize( $cached_response, [ CachedResponse::class, DateTimeImmutable::class ] ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize,PHPCompatibility.FunctionUse.NewFunctionParameters.unserialize_optionsFound
			} else {
				// PHP 5.6 does not provide the second $options argument yet.
				$cached_response = unserialize( $cached_response ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
			}
		}

		if ( ! $cached_response instanceof CachedResponse || $cached_response->is_expired() ) {
			try {
				$response = $this->remote_request->get( $url );
				$status   = $response->getStatusCode();
				$expiry   = $this->get_expiry_time( $response );
				$headers  = $response->getHeaders();
				$body     = $response->getBody();
			} catch ( FailedToGetFromRemoteUrl $exception ) {
				$status = $exception->getStatusCode();
				$expiry = new DateTimeImmutable( "+ {$this->min_expiry} seconds" );
				$body   = $exception->getMessage();
			}

			$cached_response = new CachedResponse( $body, $headers, $status, $expiry );

			set_transient( $cache_key, serialize( $cached_response ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
		}

		if ( ! $cached_response->is_valid() ) {
			throw new FailedToGetCachedResponse( $url );
		}

		return new RemoteGetRequestResponse( $cached_response->get_body(), $cached_response->get_headers(), $cached_response->get_status_code() );
	}

	/**
	 * Get the expiry time of the data to cache.
	 *
	 * This will use the cache-control header information in the provided response or fall back to the provided default
	 * expiry.
	 *
	 * @param Response $response Response object to get the expiry from.
	 * @return DateTimeInterface Expiry of the data.
	 */
	private function get_expiry_time( Response $response ) {
		if ( $this->use_cache_control && $response->hasHeader( self::CACHE_CONTROL ) ) {
			$expiry = max( $this->min_expiry, $this->get_max_age( $response->getHeader( self::CACHE_CONTROL ) ) );
			return new DateTimeImmutable( "+ {$expiry} seconds" );
		}

		return new DateTimeImmutable( "+ {$this->expiry} seconds" );
	}

	/**
	 * Get the max age setting from one or more cache-control header strings.
	 *
	 * @param array|string $cache_control_strings One or more cache control header strings.
	 * @return int Value of the max-age cache directive. 0 if not found.
	 */
	private function get_max_age( $cache_control_strings ) {
		$max_age = 0;

		foreach ( (array) $cache_control_strings as $cache_control_string ) {
			$cache_control_parts = array_map( 'trim', explode( ',', $cache_control_string ) );

			foreach ( $cache_control_parts as $cache_control_part ) {
				$cache_control_setting_parts = array_map( 'trim', explode( '=', $cache_control_part ) );

				if ( count( $cache_control_setting_parts ) !== 2 ) {
					continue;
				}

				if ( 'max-age' === $cache_control_setting_parts[0] ) {
					$max_age = absint( $cache_control_setting_parts[1] );
				}
			}
		}

		return $max_age;
	}
}
