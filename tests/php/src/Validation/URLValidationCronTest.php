<?php

namespace AmpProject\AmpWP\Tests\Validation;

use AmpProject\AmpWP\BackgroundTask\BackgroundTaskDeactivator;
use AmpProject\AmpWP\BackgroundTask\CronBasedBackgroundTask;
use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;
use AmpProject\AmpWP\Tests\Helpers\ValidationRequestMocking;
use AmpProject\AmpWP\Validation\ScannableURLProvider;
use AmpProject\AmpWP\Validation\URLScanningContext;
use AmpProject\AmpWP\Validation\URLValidationCron;
use AmpProject\AmpWP\Validation\URLValidationProvider;
use WP_UnitTestCase;

/** @coversDefaultClass \AmpProject\AmpWP\Validation\URLValidationCron */
final class URLValidationCronTest extends WP_UnitTestCase {
	use ValidationRequestMocking, PrivateAccess;

	/**
	 * Test instance
	 *
	 * @var URLValidationCron
	 */
	private $test_instance;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();
		$this->test_instance = new URLValidationCron( new BackgroundTaskDeactivator(), new ScannableURLProvider( new URLScanningContext( 20 ) ), new URLValidationProvider() );
		add_filter( 'pre_http_request', [ $this, 'get_validate_response' ] );
	}

	/**
	 * @covers ::register()
	 * @covers ::get_event_name()
	 */
	public function test_register() {
		$this->assertInstanceof( CronBasedBackgroundTask::class, $this->test_instance );
		$this->assertInstanceof( URLValidationCron::class, $this->test_instance );
		$this->assertInstanceof( Service::class, $this->test_instance );
		$this->assertInstanceof( Registerable::class, $this->test_instance );
		$this->assertInstanceof( Conditional::class, $this->test_instance );

		$this->test_instance->register();

		$this->assertEquals( 10, has_action( 'admin_init', [ $this->test_instance, 'schedule_event' ] ) );
		$this->assertEquals( 10, has_action( URLValidationCron::BACKGROUND_TASK_NAME, [ $this->test_instance, 'process' ] ) );
	}

	/** @covers ::schedule_event() */
	public function test_schedule_event_with_no_user() {
		$event_name = $this->call_private_method( $this->test_instance, 'get_event_name' );

		// No logged-in user.
		$this->test_instance->schedule_event();

		$this->assertFalse( wp_next_scheduled( $event_name ) );
	}

	/** @covers ::schedule_event() */
	public function test_schedule_event_with_user_without_permission() {
		$event_name = $this->call_private_method( $this->test_instance, 'get_event_name' );

		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'subscriber' ] ) );

		$this->test_instance->schedule_event();

		$this->assertFalse( wp_next_scheduled( $event_name ) );
	}

	/** @covers ::schedule_event() */
	public function test_schedule_event_with_user_with_permission() {
		$event_name = $this->call_private_method( $this->test_instance, 'get_event_name' );

		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'administrator' ] ) );

		$this->test_instance->schedule_event();

		$this->assertNotFalse( wp_next_scheduled( $event_name ) );
	}

	/**
	 * Test validate_urls.
	 *
	 * @covers ::process()
	 * @covers ::get_sleep_time()
	 */
	public function test_validate_urls() {
		$this->factory()->post->create_many( 5 );

		add_filter( 'amp_url_validation_sleep_time', '__return_false' );

		$this->test_instance->process();
		$this->assertCount( 10, $this->get_validated_urls() );
	}

	/** @covers ::get_event_name() */
	public function test_get_event_name() {
		$this->assertEquals(
			URLValidationCron::BACKGROUND_TASK_NAME,
			$this->call_private_method( $this->test_instance, 'get_event_name' )
		);
	}

	/** @covers ::get_interval() */
	public function test_get_interval() {
		$this->assertEquals(
			URLValidationCron::DEFAULT_INTERVAL_HOURLY,
			$this->call_private_method( $this->test_instance, 'get_interval' )
		);
	}
}
