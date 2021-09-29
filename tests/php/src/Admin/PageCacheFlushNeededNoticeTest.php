<?php
/**
 * Tests for OptionsMenu.
 *
 * @package AmpProject\AmpWP\Tests
 */

namespace AmpProject\AmpWP\Tests\Admin;

use AmpProject\AmpWP\Admin\PageCacheFlushNeededNotice;
use AmpProject\AmpWP\Tests\TestCase;
use Exception;
use WPDieException;

/**
 * Class PageCacheFlushNeededNoticeTest
 *
 * @coversDefaultClass \AmpProject\AmpWP\Admin\PageCacheFlushNeededNotice
 */
class PageCacheFlushNeededNoticeTest extends TestCase {

	/**
	 * Instance of PageCacheFlushNeededNotice
	 *
	 * @var PageCacheFlushNeededNotice
	 */
	public $instance;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {

		parent::setUp();
		$this->instance = new PageCacheFlushNeededNotice();
	}

	/**
	 * @covers ::register
	 */
	public function test_register() {

		$this->instance->register();

		$this->assertEquals(
			10,
			has_action( 'amp_page_cache_flush_needed', [ $this->instance, 'trigger_admin_notice' ] )
		);

		$this->assertEquals(
			10,
			has_action(
				'wp_ajax_' . PageCacheFlushNeededNotice::AJAX_ACTION,
				[ $this->instance, 'ajax_dismiss_amp_notice' ]
			)
		);

		$this->assertEquals(
			10,
			has_action( 'admin_notices', [ $this->instance, 'render_notice' ] )
		);
	}

	/**
	 * @covers ::trigger_admin_notice
	 */
	public function test_trigger_admin_notice() {

		$this->instance->trigger_admin_notice();

		$notices = get_option( PageCacheFlushNeededNotice::OPTION_NAME, [] );

		$this->assertContains( PageCacheFlushNeededNotice::NOTICE_ID, $notices );

	}

	/**
	 * @covers ::ajax_dismiss_amp_notice
	 */
	public function test_ajax_dismiss_amp_notice() {

		/**
		 * Flush previous data.
		 * phpcs:disable WordPress.Security.NonceVerification.Missing
		 * phpcs:disable WordPress.Security.NonceVerification.Recommended
		 */
		unset( $_POST['notice'], $_REQUEST['nonce'] );
		// phpcs:enable

		$callback_wp_die_ajax = static function () {
			return static function ( $message ) {
				throw new WPDieException( $message );
			};
		};

		add_filter( 'wp_doing_ajax', '__return_true' );
		add_filter( 'wp_die_ajax_handler', $callback_wp_die_ajax );

		$_REQUEST['nonce'] = wp_create_nonce( PageCacheFlushNeededNotice::AJAX_ACTION );

		/**
		 * Test 1: Without any data.
		 */
		ob_start();
		$exception = null;
		try {
			$this->instance->ajax_dismiss_amp_notice();
		} catch ( Exception $ex ) {
			$exception = $ex;
		}
		$output        = ob_get_clean();
		$actual_output = $output . $exception->getMessage();

		$this->assertInstanceOf( WPDieException::class, $exception );
		$this->assertEquals( '0', $actual_output );


		/**
		 * Test 2: With data, but site option is empty.
		 */
		$_POST['notice'] = PageCacheFlushNeededNotice::NOTICE_ID;

		ob_start();
		$exception = null;
		try {
			$this->instance->ajax_dismiss_amp_notice();
		} catch ( Exception $ex ) {
			$exception = $ex;
		}
		$output        = ob_get_clean();
		$actual_output = $output . $exception->getMessage();

		$this->assertEquals( '0', $actual_output );

		/**
		 * Test 3: With data, And valid option.
		 */
		update_option(
			PageCacheFlushNeededNotice::OPTION_NAME,
			[ PageCacheFlushNeededNotice::NOTICE_ID ]
		);

		ob_start();
		$exception = null;
		try {
			$this->instance->ajax_dismiss_amp_notice();
		} catch ( Exception $ex ) {
			$exception = $ex;
		}
		$output        = ob_get_clean();
		$actual_output = $output . $exception->getMessage();

		$this->assertEquals( '1', $actual_output );

		// Restore data and filters.
		remove_filter( 'wp_doing_ajax', '__return_true' );
		remove_filter( 'wp_die_ajax_handler', $callback_wp_die_ajax );

		delete_option( PageCacheFlushNeededNotice::OPTION_NAME );

		/**
		 * phpcs:disable WordPress.Security.NonceVerification.Missing
		 * phpcs:disable WordPress.Security.NonceVerification.Recommended
		 */
		unset( $_POST['notice'], $_REQUEST['nonce'] );
		// phpcs:enable
	}

	/**
	 * @covers ::render_notice
	 */
	public function test_render_notice() {

		// Flush previous data.
		delete_option( PageCacheFlushNeededNotice::OPTION_NAME );

		/**
		 * Test 1: Without option data data.
		 */
		ob_start();
		$this->instance->render_notice();
		$output = ob_get_clean();

		$this->assertEmpty( $output );

		/**
		 * Test 2: With option data.
		 */
		update_option(
			PageCacheFlushNeededNotice::OPTION_NAME,
			[ PageCacheFlushNeededNotice::NOTICE_ID ]
		);

		ob_start();
		$this->instance->render_notice();
		$output = ob_get_clean();

		$this->assertContains( 'amp-plugin-notice', $output );

		/**
		 * Test 3: With option data But on AMP setting screen.
		 */
		global $current_screen;
		set_current_screen( 'toplevel_page_amp-options' );

		ob_start();
		$this->instance->render_notice();
		$output = ob_get_clean();
		$this->assertEmpty( $output );


		// Restore data.
		delete_option( PageCacheFlushNeededNotice::OPTION_NAME );
		$current_screen = null;
	}
}
