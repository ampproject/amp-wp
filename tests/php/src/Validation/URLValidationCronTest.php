<?php

namespace AmpProject\AmpWP\Tests\Validation;

use AmpProject\AmpWP\BackgroundTask\BackgroundTaskDeactivator;
use AmpProject\AmpWP\Tests\Helpers\ValidationRequestMocking;
use AmpProject\AmpWP\Validation\URLValidationCron;
use WP_UnitTestCase;

/** @coversDefaultClass AmpProject\AmpWP\Validation\URLValidationCron */
final class URLValidationCronTest extends WP_UnitTestCase {
	/**
	 * Test instance
	 *
	 * @var URLValidationCron
	 */
	private $validation_cron;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();
		$this->validation_cron = new URLValidationCron( new BackgroundTaskDeactivator() );
		add_filter( 'pre_http_request', [ ValidationRequestMocking::class, 'get_validate_response' ] );
	}

	/**
	 * Test validate_urls.
	 *
	 * @covers ::validate_urls()
	 */
	public function test_validate_urls() {
		$this->factory()->post->create_many( 5 );

		$this->validation_cron->validate_urls( false );
		$this->assertCount( 6, ValidationRequestMocking::get_validated_urls() );
	}
}
