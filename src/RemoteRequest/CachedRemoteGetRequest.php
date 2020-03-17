<?php
/**
 * Class CachedRemoteGetRequest.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\RemoteRequest;

use AmpProject\AmpWP\RemoteRequest\CachedData;
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
	 * @var int
	 */
	private $expiry;

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
	 * @param int              $expiry            Optional. Default cache expiry in seconds. Defaults to 24 hours.
	 * @param bool             $use_cache_control Optional. Use Cache-Control headers for expiry if available. Defaults
	 *                                            to true.
	 */
	public function __construct( RemoteGetRequest $remote_request, $expiry = 86400, $use_cache_control = true ) {
		$this->remote_request    = $remote_request;
		$this->expiry            = $expiry;
		$this->use_cache_control = $use_cache_control;
	}

	/**
	 * Do a GET request to retrieve the contents of a remote URL.
	 *
	 * @todo Should this also respect additional Cache-Control directives like 'no-cache'?
	 *
	 * @param string $url URL to get.
	 * @return Response Response for the executed request.
	 * @throws FailedToGetFromRemoteUrl If retrieving the contents from the URL failed.
	 */
	public function get( $url ) {
		$cache_key   = self::TRANSIENT_PREFIX . md5( __CLASS__ . $url );
		$cached_data = get_transient( $cache_key );

		if ( false !== $cached_data ) {
			if ( PHP_MAJOR_VERSION >= 7 ) {
				$cached_data = unserialize( $cached_data, [ CachedData::class, DateTimeImmutable::class ] ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize,PHPCompatibility.FunctionUse.NewFunctionParameters.unserialize_optionsFound
			} else {
				// PHP 5.6 does not provide the second $options argument yet.
				$cached_data = unserialize( $cached_data ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
			}
		}

		if ( false === $cached_data || $cached_data->is_expired() ) {
			$response    = $this->remote_request->get( $url );
			$expiry      = $this->get_expiry_time( $response );
			$cached_data = new CachedData( $response->getBody(), $expiry );
			set_transient( $cache_key, serialize( $cached_data ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
		}

		return new RemoteGetRequestResponse( $cached_data->get_value() );
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
			$max_age              = 0;
			$cache_control_string = $response->getHeader( self::CACHE_CONTROL );
			$cache_control_parts  = array_map( 'trim', explode( ',', $cache_control_string ) );

			foreach ( $cache_control_parts as $cache_control_part ) {
				if ( 'max-age=' === substr( $cache_control_part, 0, 8 ) ) {
					$max_age = absint( substr( $cache_control_part, 8 ) );
				}
			}

			if ( $max_age > 0 ) {
				return new DateTimeImmutable( "+ {$max_age} seconds" );
			}
		}

		return new DateTimeImmutable( "+ {$this->expiry} seconds" );
	}
}
