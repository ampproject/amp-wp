<?php
/**
 * Tests for the MonitorCssTransientCaching class.
 */

namespace AmpProject\AmpWP\Tests\BackgroundTask;

use AMP_Options_Manager;
use AmpProject\AmpWP\BackgroundTask\BackgroundTaskDeactivator;
use AmpProject\AmpWP\BackgroundTask\MonitorCssTransientCaching;
use AmpProject\AmpWP\Option;
use DateTime;
use WP_UnitTestCase;

/** @coversDefaultClass \AmpProject\AmpWP\BackgroundTask\MonitorCssTransientCaching */
class MonitorCssTransientCachingTest extends WP_UnitTestCase {

	/**
	 * Whether external object cache is being used.
	 *
	 * @var bool
	 */
	private $was_wp_using_ext_object_cache;

	/**
	 * Set up the tests by clearing the list of scheduled events.
	 */
	public function setUp() {
		parent::setUp();
		_set_cron_array( [] );
		$this->was_wp_using_ext_object_cache = wp_using_ext_object_cache();
		wp_using_ext_object_cache( false );
	}

	/**
	 * Tear down the tests by clearing the list of scheduled events.
	 */
	public function tearDown() {
		parent::tearDown();
		_set_cron_array( [] );
		wp_using_ext_object_cache( $this->was_wp_using_ext_object_cache );
	}

	/**
	 * Test whether an event is actually scheduled when the monitor is registered.
	 *
	 * @uses \AmpProject\AmpWP\BackgroundTask\CronBasedBackgroundTask::schedule_event
	 * @uses \AmpProject\AmpWP\BackgroundTask\BackgroundTaskDeactivator::deactivate
	 */
	public function test_event_gets_scheduled_and_unscheduled() {
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$this->assertFalse( wp_next_scheduled( MonitorCssTransientCaching::EVENT_NAME ) );

		$monitor = new MonitorCssTransientCaching( new BackgroundTaskDeactivator() );
		$monitor->schedule_event();

		$timestamp = wp_next_scheduled( MonitorCssTransientCaching::EVENT_NAME );

		$this->assertNotFalse( $timestamp );
		$this->assertIsInt( $timestamp );
		$this->assertGreaterThan( 0, $timestamp );
	}

	/**
	 * Test whether time series are calculated and stored when the monitor is processing.
	 *
	 * @covers ::process()
	 */
	public function test_event_can_be_processed() {
		delete_option( MonitorCssTransientCaching::TIME_SERIES_OPTION_KEY );

		$monitor = new MonitorCssTransientCaching( new BackgroundTaskDeactivator() );
		$monitor->process();

		$this->assertNotFalse( get_option( MonitorCssTransientCaching::TIME_SERIES_OPTION_KEY ) );
	}

	/**
	 * Test whether transient caching is disabled once it hits the threshold.
	 *
	 * @covers ::process()
	 */
	public function test_transient_caching_is_disabled() {
		delete_option( MonitorCssTransientCaching::TIME_SERIES_OPTION_KEY );
		AMP_Options_Manager::update_option( Option::DISABLE_CSS_TRANSIENT_CACHING, false );

		add_filter(
			'amp_css_transient_monitoring_threshold',
			static function () {
				return 10;
			}
		);
		add_filter(
			'amp_css_transient_monitoring_sampling_range',
			static function () {
				return 3;
			}
		);

		$monitor = new MonitorCssTransientCaching( new BackgroundTaskDeactivator() );

		// Moving average should be 0.
		$monitor->process( new DateTime( '2000-01-01' ), 5 );
		$this->assertEquals( [ '20000101' => 5 ], get_option( MonitorCssTransientCaching::TIME_SERIES_OPTION_KEY ) );
		$this->assertFalse( AMP_Options_Manager::get_option( Option::DISABLE_CSS_TRANSIENT_CACHING ) );

		// Moving average should be 5.
		$monitor->process( new DateTime( '2000-01-02' ), 10 );
		$this->assertEquals(
			[
				'20000101' => 5,
				'20000102' => 10,
			],
			get_option( MonitorCssTransientCaching::TIME_SERIES_OPTION_KEY )
		);
		$this->assertFalse( AMP_Options_Manager::get_option( Option::DISABLE_CSS_TRANSIENT_CACHING ) );

		// Moving average should be 7.5.
		$monitor->process( new DateTime( '2000-01-03' ), 12 );
		$this->assertEquals(
			[
				'20000101' => 5,
				'20000102' => 10,
				'20000103' => 12,
			],
			get_option( MonitorCssTransientCaching::TIME_SERIES_OPTION_KEY )
		);
		$this->assertFalse( AMP_Options_Manager::get_option( Option::DISABLE_CSS_TRANSIENT_CACHING ) );

		// Moving average should be 11.
		$monitor->process( new DateTime( '2000-01-04' ), 12 );
		$this->assertEquals(
			[
				'20000102' => 10,
				'20000103' => 12,
				'20000104' => 12,
			],
			get_option( MonitorCssTransientCaching::TIME_SERIES_OPTION_KEY )
		);
		$this->assertTrue( AMP_Options_Manager::get_option( Option::DISABLE_CSS_TRANSIENT_CACHING ) );
	}
}
