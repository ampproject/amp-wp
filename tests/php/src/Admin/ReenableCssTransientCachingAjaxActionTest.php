<?php
/**
 * Tests for ReenableCssTransientCachingAjaxAction class.
 *
 * @package AmpProject\AmpWP\Tests
 */

namespace AmpProject\AmpWP\Tests\Admin;

use AMP_Options_Manager;
use AmpProject\AmpWP\Admin\ReenableCssTransientCachingAjaxAction;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Tests\TestCase;
use AmpProject\AmpWP\Option;
use Exception;
use WPDieException;

/**
 * Tests for ReenableCssTransientCachingAjaxAction class.
 *
 * @coversDefaultClass \AmpProject\AmpWP\Admin\ReenableCssTransientCachingAjaxAction
 */
class ReenableCssTransientCachingAjaxActionTest extends TestCase {

	/**
	 * Test instance.
	 *
	 * @var ReenableCssTransientCachingAjaxAction
	 */
	private $instance;

	/**
	 * Set up.
	 *
	 * @inheritdoc
	 */
	public function set_up() {
		parent::set_up();

		global $wp_scripts;
		$wp_scripts = null;

		$this->instance = new ReenableCssTransientCachingAjaxAction();
	}

	/**
	 * Tear down.
	 *
	 * @inheritdoc
	 */
	public function tear_down() {
		global $wp_scripts;
		$wp_scripts = null;

		parent::tear_down();
	}

	public function test__construct() {
		$this->assertInstanceOf( ReenableCssTransientCachingAjaxAction::class, $this->instance );
		$this->assertInstanceOf( Service::class, $this->instance );
		$this->assertInstanceOf( Registerable::class, $this->instance );
	}

	/**
	 * Tests ReenableCssTransientCachingAjaxAction::register
	 *
	 * @covers ::register
	 */
	public function test_register() {
		$this->instance->register();

		$this->assertEquals( 10, has_action( 'admin_enqueue_scripts', [ $this->instance, 'register_ajax_script' ] ) );
		$this->assertEquals( 10, has_action( 'wp_ajax_amp_reenable_css_transient_caching', [ $this->instance, 'reenable_css_transient_caching' ] ) );
	}

	/** @covers ::register_ajax_script */
	public function test_register_ajax_script_not_hook() {
		$this->instance->register_ajax_script( 'nope' );
		$this->assertFalse( wp_script_is( 'wp-util', 'enqueued' ) );
		$this->assertFalse( wp_script_is( 'jquery', 'enqueued' ) );
		$this->assertEmpty( wp_scripts()->get_data( 'wp-util', 'after' ) );
	}

	/** @return array */
	public function get_data_to_test_register_ajax_script_yes_hook() {
		return [
			'without_site_health_plugin' => [
				'hook_suffix' => 'site-health.php',
			],
			'with_site_health_plugin'    => [
				'hook_suffix' => 'tools_page_health-check',
			],
		];
	}

	/**
	 * @dataProvider get_data_to_test_register_ajax_script_yes_hook
	 * @covers ::register_ajax_script
	 * @param string $hook_suffix
	 */
	public function test_register_ajax_script_yes_hook( $hook_suffix ) {
		$this->instance->register_ajax_script( $hook_suffix );
		$this->assertTrue( wp_script_is( 'jquery', 'enqueued' ) );
		$this->assertTrue( wp_script_is( 'wp-util', 'enqueued' ) );
		$after = wp_scripts()->get_data( 'wp-util', 'after' );
		$this->assertStringContainsString( '$( \'.health-check-body\' )', implode( "\n", $after ) );
	}

	/** @return array */
	public function get_data_to_test_reenable_css_transient_caching() {
		return [
			'no_nonce'            => [
				'initial_value'   => true,
				'nonce_action'    => null,
				'user'            => null,
				'expected_output' => '-1',
				'final_value'     => true,
			],
			'no_auth'             => [
				'initial_value'   => true,
				'nonce_action'    => ReenableCssTransientCachingAjaxAction::AJAX_ACTION,
				'user'            => null,
				'expected_output' => '{"success":false,"data":"Unauthorized."}',
				'final_value'     => true,
			],
			'bad_auth'            => [
				'initial_value'   => true,
				'nonce_action'    => ReenableCssTransientCachingAjaxAction::AJAX_ACTION,
				'user'            => 'author',
				'expected_output' => '{"success":false,"data":"Unauthorized."}',
				'final_value'     => true,
			],
			'good_auth_bad_nonce' => [
				'initial_value'   => true,
				'nonce_action'    => 'other_action',
				'user'            => 'administrator',
				'expected_output' => '-1',
				'final_value'     => true,
			],
			'good_auth'           => [
				'initial_value'   => true,
				'nonce_action'    => ReenableCssTransientCachingAjaxAction::AJAX_ACTION,
				'user'            => 'administrator',
				'expected_output' => '{"success":true,"data":"CSS transient caching was re-enabled."}',
				'final_value'     => false,
			],
			'good_auth_no_change' => [
				'initial_value'   => false,
				'nonce_action'    => ReenableCssTransientCachingAjaxAction::AJAX_ACTION,
				'user'            => 'administrator',
				'expected_output' => '{"success":false,"data":"CSS transient caching could not be re-enabled."}',
				'final_value'     => false,
			],
		];
	}

	/**
	 * @covers ::reenable_css_transient_caching
	 * @dataProvider get_data_to_test_reenable_css_transient_caching
	 *
	 * @param bool        $initial_value
	 * @param string|null $nonce_action
	 * @param string|null $user
	 * @param string      $expected_output
	 * @param bool        $final_value
	 */
	public function test_reenable_css_transient_caching( $initial_value, $nonce_action, $user, $expected_output, $final_value ) {
		AMP_Options_Manager::update_option( Option::DISABLE_CSS_TRANSIENT_CACHING, $initial_value );
		add_filter( 'wp_doing_ajax', '__return_true' );
		add_filter(
			'wp_die_ajax_handler',
			static function () {
				return function ( $message ) {
					throw new WPDieException( $message );
				};
			}
		);

		if ( $user ) {
			wp_set_current_user( self::factory()->user->create( [ 'role' => $user ] ) );
		}
		if ( $nonce_action ) {
			$_REQUEST['nonce'] = wp_create_nonce( $nonce_action );
		}

		ob_start();
		$exception = null;
		try {
			$this->instance->reenable_css_transient_caching();
		} catch ( Exception $ex ) {
			$exception = $ex;
		}
		$output = ob_get_clean();
		$this->assertInstanceOf( WPDieException::class, $exception );
		$actual_output = $output . $exception->getMessage();

		$this->assertEquals( $expected_output, $actual_output );
		$this->assertEquals(
			$final_value,
			AMP_Options_Manager::get_option( Option::DISABLE_CSS_TRANSIENT_CACHING )
		);
	}
}
