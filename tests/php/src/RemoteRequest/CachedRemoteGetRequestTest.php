<?php

namespace AmpProject\AmpWP\Tests\RemoteRequest;

use AmpProject\AmpWP\RemoteRequest\CachedRemoteGetRequest;
use AmpProject\AmpWP\RemoteRequest\CachedResponse;
use AmpProject\RemoteRequest\RemoteGetRequestResponse;
use AmpProject\RemoteRequest\StubbedRemoteGetRequest;
use DateTimeImmutable;
use WP_UnitTestCase;

/** @coversDefaultClass \AmpProject\AmpWP\RemoteRequest\CachedRemoteGetRequest */
class CachedRemoteGetRequestTest extends WP_UnitTestCase {

	/** @var CachedRemoteGetRequest */
	private $instance;

	public function setUp() {
		parent::setUp();

		$this->instance = new CachedRemoteGetRequest( new StubbedRemoteGetRequest( [] ) );
	}

	/** @covers ::get() */
	public function test_get_with_serialized_cached_response() {
		$url         = 'https://example.com/foo.css';
		$body        = '';
		$headers     = [];
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
		$body        = '';
		$headers     = [];
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
		$body        = '';
		$headers     = [];
		$status_code = 200;

		$remote_request         = new StubbedRemoteGetRequest( [ $url => $body ] );
		$cached_remote_request  = new CachedRemoteGetRequest( $remote_request );
		$cached_remote_response = $cached_remote_request->get( $url );

		$this->assertInstanceOf( RemoteGetRequestResponse::class, $cached_remote_response );
		$this->assertSame( $body, $cached_remote_response->getBody() );
		$this->assertSame( $headers, $cached_remote_response->getHeaders() );
		$this->assertSame( $status_code, $cached_remote_response->getStatusCode() );
	}
}
