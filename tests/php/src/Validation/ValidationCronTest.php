<?php

namespace AmpProject\AmpWP\Tests\Validation;

use AmpProject\AmpWP\Tests\Helpers\AssertContainsCompatibility;
use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;
use AmpProject\AmpWP\Tests\Helpers\ValidationRequestMocking;
use AmpProject\AmpWP\Validation\ValidationCron;
use AmpProject\AmpWP\Validation\ValidationProvider;
use WP_UnitTestCase;

/** @coversDefaultClass AmpProject\AmpWP\Validation\ValidationCron */
final class ValidationCronTest extends WP_UnitTestCase {
	use PrivateAccess, AssertContainsCompatibility;

	/**
	 * Test instance
	 *
	 * @var ValidationCron
	 */
	private $validation_cron;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();
		$this->validation_cron = new ValidationCron();
		add_filter( 'pre_http_request', [ ValidationRequestMocking::class, 'get_validate_response' ] );
	}

	/**
	 * Test validate_urls.
	 *
	 * @covers ::validate_urls()
	 */
	public function test_validate_urls() {
		$this->factory()->post->create_many( 5 );

		$this->validation_cron->validate_urls( true, false );
		$this->assertEquals( 7, count( ValidationRequestMocking::get_validated_urls() ) );
		$this->assertEquals( 2, get_transient( ValidationCron::OFFSET_KEY ) );

		$this->validation_cron->validate_urls( true, false );
		$this->assertEquals( 9, count( ValidationRequestMocking::get_validated_urls() ) );
		$this->assertEquals( 4, get_transient( ValidationCron::OFFSET_KEY ) );

		( new ValidationProvider() )->with_lock(
			function() {
				$this->validation_cron->validate_urls( true, false );
			}
		);

		$this->assertEquals( 4, get_transient( ValidationCron::OFFSET_KEY ) );

		$this->validation_cron->validate_urls( true, false );
		$this->assertEquals( 10, count( ValidationRequestMocking::get_validated_urls() ) );
		$this->assertEquals( 6, get_transient( ValidationCron::OFFSET_KEY ) );

		// Validation should now reset.
		$this->validation_cron->validate_urls( true, false );
		$this->assertEquals( 2, get_transient( ValidationCron::OFFSET_KEY ) );
	}
}
