<?php

namespace AmpProject\AmpWP\Tests\RemoteRequest;

use AmpProject\AmpWP\RemoteRequest\CachedRemoteGetRequest;
use AmpProject\AmpWP\RemoteRequest\CachedResponse;
use AmpProject\AmpWP\RemoteRequest\WpHttpRemoteGetRequest;
use AmpProject\RemoteRequest\RemoteGetRequestResponse;
use AmpProject\RemoteRequest\StubbedRemoteGetRequest;
use AmpProject\AmpWP\Tests\TestCase;
use DateTimeImmutable;

/** @coversDefaultClass \AmpProject\AmpWP\RemoteRequest\CachedRemoteGetRequest */
class CachedRemoteGetRequestTest extends TestCase {

	/** @var CachedRemoteGetRequest */
	private $instance;

	public function set_up() {
		parent::set_up();

		$this->instance = new CachedRemoteGetRequest( new StubbedRemoteGetRequest( [] ) );
	}

	/** @covers ::get() */
	public function test_get_with_serialized_cached_response() {
		$url         = 'https://example.com/foo.css';
		$body        = 'body {color:red}';
		$headers     = [ 'content-type' => 'text/css' ];
		$status_code = 418;
		$expiry      = new DateTimeImmutable( '+ ' . DAY_IN_SECONDS . ' seconds' );

		$cache_key = CachedRemoteGetRequest::TRANSIENT_PREFIX . md5( CachedRemoteGetRequest::class . $url );

		$cached_response = new CachedResponse( $body, $headers, $status_code, $expiry );
		add_filter(
			"pre_transient_${cache_key}",
			static function () use ( $cached_response ) {
				return serialize( $cached_response ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
			}
		);

		$cached_remote_request  = new CachedRemoteGetRequest( new StubbedRemoteGetRequest( [] ) );
		$cached_remote_response = $cached_remote_request->get( $url );

		$this->assertInstanceOf( RemoteGetRequestResponse::class, $cached_remote_response );
		$this->assertSame( $body, $cached_remote_response->getBody() );
		$this->assertSame( $headers, $cached_remote_response->getHeaders() );
		$this->assertSame( $status_code, $cached_remote_response->getStatusCode() );
	}

	/** @covers ::get() */
	public function test_get_with_unserialized_cached_response() {
		$url         = 'https://example.com/foo.css';
		$body        = 'body {color:red}';
		$headers     = [ 'content-type' => 'text/css' ];
		$status_code = 418;
		$expiry      = new DateTimeImmutable( '+ ' . DAY_IN_SECONDS . ' seconds' );

		$cache_key = CachedRemoteGetRequest::TRANSIENT_PREFIX . md5( CachedRemoteGetRequest::class . $url );

		$cached_response = new CachedResponse( $body, $headers, $status_code, $expiry );
		add_filter(
			"pre_transient_${cache_key}",
			static function () use ( $cached_response ) {
				return $cached_response;
			}
		);

		$cached_remote_request  = new CachedRemoteGetRequest( new StubbedRemoteGetRequest( [] ) );
		$cached_remote_response = $cached_remote_request->get( $url );

		$this->assertInstanceOf( RemoteGetRequestResponse::class, $cached_remote_response );
		$this->assertSame( $body, $cached_remote_response->getBody() );
		$this->assertSame( $headers, $cached_remote_response->getHeaders() );
		$this->assertSame( $status_code, $cached_remote_response->getStatusCode() );
	}

	/** @covers ::get() */
	public function test_get_without_cached_response() {
		$url         = 'https://example.com/foo.css';
		$body        = 'body {color:red}';
		$headers     = [ 'content-type' => 'text/css' ];
		$status_code = 200;

		add_filter(
			'pre_http_request',
			static function ( $pre, $r, $_url ) use ( $url, $body, $headers, $status_code ) {
				if ( $_url !== $url ) {
					return $pre;
				}

				return [
					'body'     => $body,
					'headers'  => $headers,
					'response' => [
						'code'    => $status_code,
						'message' => 'OK',
					],
				];
			},
			10,
			3
		);


		$cached_remote_request  = new CachedRemoteGetRequest( new WpHttpRemoteGetRequest() );
		$cached_remote_response = $cached_remote_request->get( $url );

		$this->assertInstanceOf( RemoteGetRequestResponse::class, $cached_remote_response );
		$this->assertSame( $body, $cached_remote_response->getBody() );
		$this->assertSame( $headers, $cached_remote_response->getHeaders() );
		$this->assertSame( $status_code, $cached_remote_response->getStatusCode() );
	}
}
