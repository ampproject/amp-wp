<?php
/**
 * Tests for the MonitorCssTransientCaching class.
 */

namespace AmpProject\AmpWP\Tests\BackgroundTask;

use AMP_Options_Manager;
use AMP_Style_Sanitizer;
use AmpProject\AmpWP\BackgroundTask\MonitorCssTransientCaching;
use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\Tests\DependencyInjectedTestCase;
use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;
use AmpProject\Dom\Document;
use DateTime;

/** @coversDefaultClass \AmpProject\AmpWP\BackgroundTask\MonitorCssTransientCaching */
class MonitorCssTransientCachingTest extends DependencyInjectedTestCase {

	use PrivateAccess;

	/**
	 * Whether external object cache is being used.
	 *
	 * @var bool
	 */
	private $was_wp_using_ext_object_cache;

	/** @var string */
	private $original_wp_version;

	/**
	 * Set up the tests by clearing the list of scheduled events.
	 */
	public function setUp() {
		parent::setUp();
		_set_cron_array( [] );
		$this->was_wp_using_ext_object_cache = wp_using_ext_object_cache();
		wp_using_ext_object_cache( false );
		$this->original_wp_version = $GLOBALS['wp_version'];
	}

	/**
	 * Tear down the tests by clearing the list of scheduled events.
	 */
	public function tearDown() {
		parent::tearDown();
		_set_cron_array( [] );
		wp_using_ext_object_cache( $this->was_wp_using_ext_object_cache );
		$GLOBALS['wp_version'] = $this->original_wp_version;
	}

	/**
	 * @covers ::register()
	 */
	public function test_register() {
		$monitor = $this->injector->make( MonitorCssTransientCaching::class );
		$monitor->register();
		$this->assertEquals( 10, has_action( 'amp_plugin_update', [ $monitor, 'handle_plugin_update' ] ) );
	}

	/**
	 * @covers ::get_interval()
	 */
	public function test_get_interval() {
		$monitor = $this->injector->make( MonitorCssTransientCaching::class );
		$this->assertIsString( $this->call_private_method( $monitor, 'get_interval' ) );
	}

	/**
	 * @covers ::get_event_name()
	 */
	public function test_get_event_name() {
		$monitor = $this->injector->make( MonitorCssTransientCaching::class );
		$this->assertIsString( $this->call_private_method( $monitor, 'get_event_name' ) );
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

		$monitor = $this->injector->make( MonitorCssTransientCaching::class );
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
	public function test_process_causes_time_series_to_be_stored() {
		delete_option( MonitorCssTransientCaching::TIME_SERIES_OPTION_KEY );

		$monitor = $this->injector->make( MonitorCssTransientCaching::class );
		$monitor->process();

		$this->assertNotFalse( get_option( MonitorCssTransientCaching::TIME_SERIES_OPTION_KEY ) );
	}

	/**
	 * Test whether transient caching is disabled once it hits the threshold.
	 *
	 * @covers ::process()
	 * @covers ::get_time_series()
	 * @covers ::get_sampling_range()
	 * @covers ::persist_time_series()
	 * @covers ::calculate_average()
	 * @covers ::get_threshold()
	 * @covers ::disable_css_transient_caching()
	 */
	public function test_process_disables_transient_caching_once_threshold_is_reached() {
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

		$monitor = $this->injector->make( MonitorCssTransientCaching::class );

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

		$expected = [
			MonitorCssTransientCaching::WP_VERSION        => get_bloginfo( 'version' ),
			MonitorCssTransientCaching::GUTENBERG_VERSION => defined( 'GUTENBERG_VERSION' ) ? GUTENBERG_VERSION : null,
		];

		$this->assertTrue( (bool) AMP_Options_Manager::get_option( Option::DISABLE_CSS_TRANSIENT_CACHING ) );
		$this->assertEquals( $expected, AMP_Options_Manager::get_option( Option::DISABLE_CSS_TRANSIENT_CACHING ) );
	}

	/**
	 * @covers ::enable_css_transient_caching()
	 * @covers ::disable_css_transient_caching()
	 * @covers ::is_css_transient_caching_disabled()
	 */
	public function test_enable_disable_is_css_transient_caching_disabled() {
		$monitor = $this->injector->make( MonitorCssTransientCaching::class );
		$this->assertFalse( $monitor->is_css_transient_caching_disabled() );
		$monitor->disable_css_transient_caching();
		$this->assertTrue( $monitor->is_css_transient_caching_disabled() );
		$monitor->enable_css_transient_caching();
		$this->assertFalse( $monitor->is_css_transient_caching_disabled() );
	}

	/**
	 * @covers ::query_css_transient_count()
	 */
	public function test_query_css_transient_count() {
		$monitor = $this->injector->make( MonitorCssTransientCaching::class );

		$this->assertEquals( 0, $monitor->query_css_transient_count() );

		$dom = new Document();
		$dom->loadHTML(
			'
				<html>
					<head><style>body { background:red }</style></head>
					<body style="color:blue"></body>
				</html>
			'
		);
		$style_sanitizer = new AMP_Style_Sanitizer(
			$dom,
			[ 'use_document_element' => true ]
		);
		$style_sanitizer->sanitize();

		$this->assertEquals( 2, $monitor->query_css_transient_count() );

		$dom = new Document();
		$dom->loadHTML(
			'
				<html>
					<head><style>body { background:red }</style></head>
					<body style="color:white"></body>
				</html>
			'
		);
		$style_sanitizer = new AMP_Style_Sanitizer(
			$dom,
			[ 'use_document_element' => true ]
		);
		$style_sanitizer->sanitize();

		$this->assertEquals( 3, $monitor->query_css_transient_count() );
	}

	/** @return array */
	public function get_data_to_test_handle_plugin_update() {
		return [
			'not_disabled'                      => [
				function ( MonitorCssTransientCaching $monitor ) {
					$monitor->enable_css_transient_caching();
					$this->assertFalse( $monitor->is_css_transient_caching_disabled() );
					$monitor->handle_plugin_update( '2.2.1' );
				},
				false,
			],
			'old_reset_condition_in_range'      => [
				function ( MonitorCssTransientCaching $monitor ) {
					AMP_Options_Manager::update_option( Option::DISABLE_CSS_TRANSIENT_CACHING, true );
					$this->assertTrue( $monitor->is_css_transient_caching_disabled() );
					$monitor->handle_plugin_update( '1.5.1' );
				},
				false,
			],
			'old_reset_condition_outside_range' => [
				function ( MonitorCssTransientCaching $monitor ) {
					AMP_Options_Manager::update_option(
						Option::DISABLE_CSS_TRANSIENT_CACHING,
						[
							MonitorCssTransientCaching::WP_VERSION => '999.9',
							MonitorCssTransientCaching::GUTENBERG_VERSION => '999.9',
						]
					);
					$this->assertTrue( $monitor->is_css_transient_caching_disabled() );
					$monitor->handle_plugin_update( '1.5.2' ); // Should no-op.
				},
				true,
			],
			'uniqid_before_storing_meta'        => [
				function ( MonitorCssTransientCaching $monitor ) {
					AMP_Options_Manager::update_option( Option::DISABLE_CSS_TRANSIENT_CACHING, true );
					$this->assertTrue( $monitor->is_css_transient_caching_disabled() );
					$monitor->handle_plugin_update( '2.2.1' );
				},
				false,
			],
			'uniqid_after_storing_meta'         => [
				function ( MonitorCssTransientCaching $monitor ) {
					AMP_Options_Manager::update_option(
						Option::DISABLE_CSS_TRANSIENT_CACHING,
						[
							MonitorCssTransientCaching::WP_VERSION => '999.0',
							MonitorCssTransientCaching::GUTENBERG_VERSION => '999.9',
						]
					);
					$this->assertTrue( $monitor->is_css_transient_caching_disabled() );
					$monitor->handle_plugin_update( '2.2.2' ); // Should no-op.
				},
				true,
			],
		];
	}

	/**
	 * @dataProvider get_data_to_test_handle_plugin_update
	 * @covers ::handle_plugin_update()
	 *
	 * @param callable $set_up
	 * @param bool     $expected_disabled
	 */
	public function test_handle_plugin_update( $set_up, $expected_disabled ) {
		$monitor = $this->injector->make( MonitorCssTransientCaching::class );
		$set_up( $monitor );
		$this->assertSame( $expected_disabled, $monitor->is_css_transient_caching_disabled() );
	}

	/**
	 * @covers ::get_default_threshold()
	 */
	public function test_get_default_threshold() {
		$monitor = $this->injector->make( MonitorCssTransientCaching::class );
		$this->assertIsFloat( $monitor->get_default_threshold() );
	}

	/**
	 * @covers ::get_default_sampling_range()
	 */
	public function test_get_default_sampling_range() {
		$monitor = $this->injector->make( MonitorCssTransientCaching::class );
		$this->assertIsInt( $monitor->get_default_sampling_range() );
	}

	/**
	 * @covers ::get_time_series()
	 * @covers ::persist_time_series()
	 */
	public function test_get_and_persist_time_series() {
		$monitor = $this->injector->make( MonitorCssTransientCaching::class );

		$this->assertEquals( [], $this->call_private_method( $monitor, 'get_time_series' ) );

		$time_series = [
			'20220101' => 10,
			'20220102' => 20,
			'20220103' => 30,
		];

		$this->call_private_method( $monitor, 'persist_time_series', [ $time_series ] );

		$this->assertEquals( $time_series, $this->call_private_method( $monitor, 'get_time_series' ) );
	}
}
