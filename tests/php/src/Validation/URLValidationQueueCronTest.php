<?php
/**
 * Test cases for URLValidationQueueCron
 *
 *
 */

namespace AmpProject\AmpWP\Tests\Validation;

use AmpProject\AmpWP\BackgroundTask\BackgroundTaskDeactivator;
use AmpProject\AmpWP\BackgroundTask\CronBasedBackgroundTask;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Validation\ScannableURLProvider;
use AmpProject\AmpWP\Validation\URLScanningContext;
use AmpProject\AmpWP\Validation\URLValidationQueueCron;
use WP_UnitTestCase;
use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;
use AmpProject\AmpWP\Tests\Helpers\ValidationRequestMocking;

/** @coversDefaultClass \AmpProject\AmpWP\Validation\URLValidationQueueCron */
final class URLValidationQueueCronTest extends WP_UnitTestCase {

	use ValidationRequestMocking, PrivateAccess;

	/**
	 * Test instance
	 *
	 * @var URLValidationQueueCron
	 */
	private $test_instance;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {

		parent::setUp();
		$this->test_instance = new URLValidationQueueCron( new BackgroundTaskDeactivator(), new ScannableURLProvider( new URLScanningContext( 20 ) ) );

		add_filter( 'pre_http_request', [ $this, 'get_validate_response' ] );
	}

	/**
	 * @covers ::register()
	 * @covers ::get_event_name()
	 */
	public function test_register() {

		$this->assertInstanceof( CronBasedBackgroundTask::class, $this->test_instance );
		$this->assertInstanceof( URLValidationQueueCron::class, $this->test_instance );
		$this->assertInstanceof( Service::class, $this->test_instance );
		$this->assertInstanceof( Registerable::class, $this->test_instance );

		$this->test_instance->register();

		$this->assertEquals( 10, has_action( 'admin_init', [ $this->test_instance, 'schedule_event' ] ) );
		$this->assertEquals(
			10,
			has_action( URLValidationQueueCron::BACKGROUND_TASK_NAME, [ $this->test_instance, 'process' ] )
		);
	}

	/**
	 * @covers ::process
	 */
	public function test_process() {

		$this->factory()->post->create_many( 5 );
		$this->test_instance->process();

		$validation_queue_key = 'amp_url_validation_queue';
		$validation_queue     = get_option( $validation_queue_key, [] );
		$this->assertCount( 10, $validation_queue );
	}

	/**
	 * @covers ::get_event_name
	 */
	public function test_get_event_name() {

		$this->assertEquals(
			URLValidationQueueCron::BACKGROUND_TASK_NAME,
			$this->call_private_method( $this->test_instance, 'get_event_name' )
		);
	}

	/**
	 * @covers ::get_interval
	 */
	public function test_get_interval() {

		$this->assertEquals(
			URLValidationQueueCron::DEFAULT_INTERVAL_WEEKLY,
			$this->call_private_method( $this->test_instance, 'get_interval' )
		);
	}
}
