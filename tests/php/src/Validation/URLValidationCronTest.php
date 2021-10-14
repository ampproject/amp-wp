<?php

namespace AmpProject\AmpWP\Tests\Validation;

use AmpProject\AmpWP\BackgroundTask\CronBasedBackgroundTask;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Tests\DependencyInjectedTestCase;
use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;
use AmpProject\AmpWP\Tests\Helpers\ValidationRequestMocking;
use AmpProject\AmpWP\Validation\ScannableURLProvider;
use AmpProject\AmpWP\Validation\URLValidationCron;

/** @coversDefaultClass \AmpProject\AmpWP\Validation\URLValidationCron */
final class URLValidationCronTest extends DependencyInjectedTestCase {
	use ValidationRequestMocking, PrivateAccess;

	/**
	 * Test instance
	 *
	 * @var URLValidationCron
	 */
	private $test_instance;

	/** @var int */
	private $request_count = 0;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();
		$this->test_instance = $this->injector->make( URLValidationCron::class );
		add_filter(
			'pre_http_request',
			function () {
				$this->request_count++;
				return $this->get_validate_response();
			}
		);
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

		$this->test_instance->register();

		$this->assertEquals( 10, has_action( 'admin_init', [ $this->test_instance, 'schedule_event' ] ) );
		$this->assertEquals( 10, has_action( URLValidationCron::BACKGROUND_TASK_NAME, [ $this->test_instance, 'process' ] ) );
	}

	/** @covers RecurringBackgroundTask::schedule_event() */
	public function test_schedule_event_with_no_user() {
		$event_name = $this->call_private_method( $this->test_instance, 'get_event_name' );

		// No logged-in user.
		$this->test_instance->schedule_event();

		$this->assertFalse( wp_next_scheduled( $event_name ) );
	}

	/** @covers RecurringBackgroundTask::schedule_event() */
	public function test_schedule_event_with_user_without_permission() {
		$event_name = $this->call_private_method( $this->test_instance, 'get_event_name' );

		$this->assertFalse( wp_next_scheduled( $event_name ) );

		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'subscriber' ] ) );

		$this->test_instance->schedule_event();

		$this->assertTrue( is_numeric( wp_next_scheduled( $event_name ) ) );
	}

	/** @covers RecurringBackgroundTask::schedule_event() */
	public function test_schedule_event_with_user_with_permission() {
		$event_name = $this->call_private_method( $this->test_instance, 'get_event_name' );

		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'administrator' ] ) );

		$this->test_instance->schedule_event();

		$this->assertNotFalse( wp_next_scheduled( $event_name ) );
	}

	/** @covers RecurringBackgroundTask::schedule_event() */
	public function test_schedule_event_with_different_recurrence() {
		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'administrator' ] ) );
		$event_name   = $this->call_private_method( $this->test_instance, 'get_event_name' );
		$args         = [];
		$old_interval = 'daily';
		$interval     = $this->call_private_method( $this->test_instance, 'get_interval' );

		$this->assertFalse( wp_next_scheduled( $event_name, $args ) );

		$count_events = static function ( $hook ) {
			$count = 0;
			foreach ( _get_cron_array() as $cron ) {
				if ( isset( $cron[ $hook ] ) ) {
					$count += count( $cron[ $hook ] );
				}
			}
			return $count;
		};
		$this->assertEquals( 0, $count_events( $event_name ) );

		// First schedule with an old interval.
		$this->assertNotEquals( $old_interval, $interval );
		$old_time = time() + HOUR_IN_SECONDS;
		$this->assertTrue( wp_schedule_event( $old_time, $old_interval, $event_name, $args ) );
		$this->assertEquals( $old_time, wp_next_scheduled( $event_name, $args ) );
		$this->assertEquals( 1, $count_events( $event_name ) );

		// Now try scheduling with the new interval.
		$this->test_instance->schedule_event();
		$event = $this->test_instance->get_scheduled_event( $event_name, $args );
		$this->assertIsObject( $event );

		$this->assertEquals( 1, $count_events( $event_name ) );
		$this->assertNotEquals( $old_time, $event->timestamp, 'Expected old event to no longer be scheduled at the old time.' );
		$this->assertGreaterThanOrEqual( time(), $event->timestamp );
		$this->assertEquals( $interval, $event->schedule, 'Expected event to have the new interval.' );
	}

	/**
	 * Test validate_urls.
	 *
	 * @covers ::process()
	 * @covers ::dequeue()
	 */
	public function test_validate_urls() {
		/** @var ScannableURLProvider $scannable_url_provider */
		$scannable_url_provider = $this->get_private_property( $this->test_instance, 'scannable_url_provider' );

		$initial_urls      = wp_list_pluck( $scannable_url_provider->get_urls(), 'url' );
		$initial_url_count = count( $initial_urls );
		$this->assertGreaterThan( 0, $initial_url_count );

		delete_option( URLValidationCron::OPTION_KEY );

		// Verify that processing will enqueue URLs (if none are queued) and process one.
		for ( $i = 1; $i <= $initial_url_count; $i++ ) {
			$this->test_instance->process();
			$this->assertEquals( $i, $this->request_count );
			$data = get_option( URLValidationCron::OPTION_KEY );
			$this->assertArrayHasKey( 'urls', $data );
			$this->assertArrayHasKey( 'timestamp', $data );
			$this->assertLessThanOrEqual( time(), $data['timestamp'] );
			$this->assertEquals(
				array_slice( $initial_urls, $i ),
				$data['urls']
			);
		}

		// Ensure no URLs are queued or processed if timestamp is less than a week.
		$data = get_option( URLValidationCron::OPTION_KEY );
		$this->assertCount( 0, $data['urls'] );
		$before_request_count = $this->request_count;
		$this->test_instance->process();
		$this->assertEquals( $before_request_count, $this->request_count );
		$data = get_option( URLValidationCron::OPTION_KEY );
		$this->assertCount( 0, $data['urls'] );

		// Now test that after a week has transpired, the next set of URLs are re-queued and one is processed.
		$data['timestamp'] = time() - WEEK_IN_SECONDS - MINUTE_IN_SECONDS;
		update_option( URLValidationCron::OPTION_KEY, $data );
		$this->test_instance->process();
		$this->assertEquals( $before_request_count + 1, $this->request_count );
		$data = get_option( URLValidationCron::OPTION_KEY );
		$this->assertCount( $initial_url_count - 1, $data['urls'] );
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
