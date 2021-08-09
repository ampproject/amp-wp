<?php
/**
 * Tests for the BackgroundTaskDeactivator class.
 */

namespace AmpProject\AmpWP\Tests\BackgroundTask;

use AmpProject\AmpWP\BackgroundTask\BackgroundTaskDeactivator;
use AmpProject\AmpWP\Infrastructure\Deactivateable;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Tests\Helpers\AssertContainsCompatibility;
use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;
use WP_UnitTestCase;

/**
 * @coversDefaultClass \AmpProject\AmpWP\BackgroundTask\BackgroundTaskDeactivator
 */
final class BackgroundTaskDeactivatorTest extends WP_UnitTestCase {
	use AssertContainsCompatibility, PrivateAccess;

	/**
	 * BackgroundTaskDeactivator instance.
	 *
	 * @var BackgroundTaskDeactivator.
	 */
	private $test_instance;

	public function setUp() {
		$this->test_instance = new BackgroundTaskDeactivator();
	}

	/**
	 * @covers ::register()
	 */
	public function test_register() {
		$this->assertInstanceof( BackgroundTaskDeactivator::class, $this->test_instance );
		$this->assertInstanceof( Service::class, $this->test_instance );
		$this->assertInstanceof( Registerable::class, $this->test_instance );
		$this->assertInstanceof( Deactivateable::class, $this->test_instance );

		$this->test_instance->register();

		$plugin_file = $this->get_private_property( $this->test_instance, 'plugin_file' );
		$this->assertEquals( 'amp/amp.php', $plugin_file );

		$this->assertEquals( 10, has_action( "network_admin_plugin_action_links_{$plugin_file}", [ $this->test_instance, 'add_warning_sign_to_network_deactivate_action' ] ) );
		$this->assertEquals( 10, has_action( 'plugin_row_meta', [ $this->test_instance, 'add_warning_to_plugin_meta' ] ) );
	}

	/**
	 * @covers ::add_event()
	 * @covers ::deactivate()
	 */
	public function test_deactivating_two_events() {
		$original_cron = get_option( 'cron' );

		update_option(
			'cron',
			[
				time()                   => [
					'event_one' => [ 'args' => [] ],
				],
				time() + HOUR_IN_SECONDS => [
					'event_two' => [ 'args' => [] ],
				],
			]
		);

		$this->assertCount( 2, _get_cron_array() );

		$this->test_instance->add_event( 'event_one' );
		$this->test_instance->add_event( 'event_two' );

		$this->test_instance->deactivate( false );
		$this->assertCount( 0, _get_cron_array() );

		update_option( 'cron', $original_cron );
	}

	/**
	 * @covers ::add_event()
	 * @covers ::deactivate()
	 */
	public function test_deactivating_one_of_two_events() {
		$original_cron = get_option( 'cron' );

		update_option(
			'cron',
			[
				time()                   => [
					'event_one' => [ 'args' => [] ],
				],
				time() + HOUR_IN_SECONDS => [
					'event_two' => [ 'args' => [] ],
				],
			]
		);

		$this->assertCount( 2, _get_cron_array() );

		$this->test_instance->add_event( 'event_one' );

		$this->test_instance->deactivate( false );
		$this->assertCount( 1, _get_cron_array() );

		update_option( 'cron', $original_cron );
	}

	/**
	 * @covers ::get_warning_icon()
	 * @covers ::add_warning_sign_to_network_deactivate_action()
	 */
	public function test_network_deactivate_warning() {
		// Tested method uses multisite functions.
		if ( ! function_exists( 'wp_is_large_network' ) ) {
			require_once ABSPATH . WPINC . '/ms-functions.php';
		}

		add_filter( 'wp_is_large_network', '__return_true' );
		wp_register_style( 'amp-icons', 'http://site.com/file.css', [], '1' );

		$actions = [
			'deactivate' => '<a></a>',
		];

		$new_actions = $this->test_instance->add_warning_sign_to_network_deactivate_action( $actions );

		$this->assertTrue( wp_style_is( 'amp-icons' ) );
		$this->assertStringContainsString( '<span style="vertical-align: middle">', $new_actions['deactivate'] );

		remove_filter( 'wp_is_large_network', '__return_true' );
	}
}
