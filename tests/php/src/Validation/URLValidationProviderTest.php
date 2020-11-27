<?php

namespace AmpProject\AmpWP\Tests\Validation;

use AmpProject\AmpWP\Tests\Helpers\AssertContainsCompatibility;
use AmpProject\AmpWP\Tests\Helpers\ValidationRequestMocking;
use AmpProject\AmpWP\Validation\URLValidationProvider;
use WP_UnitTestCase;

/** @coversDefaultClass \AmpProject\AmpWP\Validation\URLValidationProvider */
final class URLValidationProviderTest extends WP_UnitTestCase {
	use AssertContainsCompatibility, ValidationRequestMocking;

	/**
	 * Validation provider instance to use.
	 *
	 * @var URLValidationProvider
	 */
	private $url_validation_provider;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();
		$this->url_validation_provider = new URLValidationProvider();
		add_filter( 'pre_http_request', [ $this, 'get_validate_response' ] );
	}

	/**
	 * Test get_url_validation.
	 *
	 * @covers ::get_url_validation()
	 * @covers ::get_total_errors()
	 * @covers ::get_unaccepted_errors()
	 * @covers ::update_state_from_validity()
	 */
	public function test_get_url_validation() {
		$single_post_permalink = get_permalink( self::factory()->post->create() );
		$this->url_validation_provider->get_url_validation( $single_post_permalink, 'post' );
		$this->assertContains( $single_post_permalink, $this->get_validated_urls() );

		$number_of_posts = 30;
		$post_permalinks = [];

		for ( $i = 0; $i < $number_of_posts; $i++ ) {
			$permalink         = get_permalink( self::factory()->post->create() );
			$post_permalinks[] = $permalink;
			$this->url_validation_provider->get_url_validation( $permalink, 'post' );
		}

		// All of the posts created should be present in the validated URLs.
		$this->assertEmpty( array_diff( $post_permalinks, $this->get_validated_urls() ) );

		$this->assertEquals( 31, $this->url_validation_provider->get_total_errors() );
		$this->assertEmpty( $this->url_validation_provider->get_unaccepted_errors() );
		$this->assertEquals( 31, $this->url_validation_provider->get_number_validated() );

		$this->assertEquals(
			[ 'post' ],
			array_keys( $this->url_validation_provider->get_validity_by_type() )
		);
	}

	/**
	 * Tests locking and unlocking.
	 *
	 * @covers ::lock()
	 * @covers ::unlock()
	 * @covers ::is_locked()
	 * @covers ::with_lock()
	 */
	public function test_locking() {
		$this->assertFalse( get_option( URLValidationProvider::LOCK_KEY ) );

		$expected_result = 'EXPECTED RESULT';
		$result          = $this->url_validation_provider->with_lock(
			function () use ( $expected_result ) {
				$this->assertTrue( (bool) get_option( URLValidationProvider::LOCK_KEY ) );

				// Expect an error when lock is already in place.
				$this->assertWPError( $this->url_validation_provider->with_lock( '__return_empty_string' ) );

				return $expected_result;
			}
		);

		$this->assertEquals( $expected_result, $result );

		$this->assertFalse( get_option( URLValidationProvider::LOCK_KEY ) );
	}
}
