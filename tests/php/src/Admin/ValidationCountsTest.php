<?php
/**
 * Tests for ValidationCountsTest class.
 *
 * @package AmpProject\AmpWP\Tests
 */

namespace AmpProject\AmpWP\Tests\Admin;

use AMP_Options_Manager;
use AMP_Theme_Support;
use AMP_Validated_URL_Post_Type;
use AmpProject\AmpWP\Admin\RESTPreloader;
use AmpProject\AmpWP\Admin\ValidationCounts;
use AmpProject\AmpWP\DevTools\UserAccess;
use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\HasRequirements;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\Services;
use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;
use AmpProject\AmpWP\Tests\TestCase;

/**
 * Tests for ValidationCounts class.
 *
 * @coversDefaultClass \AmpProject\AmpWP\Admin\ValidationCounts
 */
class ValidationCountsTest extends TestCase {

	use PrivateAccess;

	/**
	 * Test instance.
	 *
	 * @var ValidationCounts
	 */
	private $instance;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function set_up() {
		parent::set_up();

		set_current_screen( 'edit' );
		get_current_screen()->post_type = 'post';

		$this->instance = new ValidationCounts( new RESTPreloader() );
	}

	/** @covers ::__construct() */
	public function test__construct() {
		$this->assertInstanceOf( ValidationCounts::class, $this->instance );
		$this->assertInstanceOf( Delayed::class, $this->instance );
		$this->assertInstanceOf( Conditional::class, $this->instance );
		$this->assertInstanceOf( Service::class, $this->instance );
		$this->assertInstanceOf( Registerable::class, $this->instance );
		$this->assertInstanceOf( HasRequirements::class, $this->instance );
	}

	/**
	 * Test ::get_registration_action().
	 *
	 * @covers ::get_registration_action()
	 */
	public function test_get_registration_action() {
		self::assertEquals( 'admin_enqueue_scripts', ValidationCounts::get_registration_action() );
	}

	/** @covers ::get_requirements() */
	public function test_get_requirements() {
		$this->assertSame(
			[ 'dependency_support', 'dev_tools.user_access' ],
			ValidationCounts::get_requirements()
		);
	}

	/**
	 * Test ::register().
	 *
	 * @covers ::register()
	 */
	public function test_register() {
		$this->instance->register();

		$this->assertTrue( wp_script_is( ValidationCounts::ASSETS_HANDLE ) );
		$this->assertTrue( wp_style_is( ValidationCounts::ASSETS_HANDLE ) );
	}

	/**
	 * Test ::is_needed().
	 *
	 * @covers ::is_needed()
	 */
	public function test_is_needed() {
		$this->assertFalse( ValidationCounts::is_needed() );

		// If dependency support is absent, then abort because is_needed will never be true.
		if ( ! Services::get( 'dependency_support' )->has_support() ) {
			return;
		}

		$admin_user = self::factory()->user->create_and_get( [ 'role' => 'administrator' ] );
		wp_set_current_user( $admin_user->ID );

		// Should be needed when in Transitional mode and not on a devtools screen.
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::TRANSITIONAL_MODE_SLUG );
		$this->assertTrue( ValidationCounts::is_needed() );

		// Should not be needed when in Reader mode and not on a devtools screen.
		unset( $_GET['post'], $_GET['post_type'], $_GET['taxonomy'], $_GET['action'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::READER_MODE_SLUG );
		$this->assertFalse( ValidationCounts::is_needed() );

		// Should not be needed when in Reader mode, user access has not been configured and on a devtools screen.
		$_GET['post_type'] = AMP_Validated_URL_Post_Type::POST_TYPE_SLUG;
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::READER_MODE_SLUG );
		$this->assertFalse( ValidationCounts::is_needed() );

		// Should not be needed when in Reader mode, on a devtools screen, and dev tools is disabled for the user.
		$_GET['post_type'] = AMP_Validated_URL_Post_Type::POST_TYPE_SLUG;
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::READER_MODE_SLUG );
		update_user_meta( $admin_user->ID, UserAccess::USER_FIELD_DEVELOPER_TOOLS_ENABLED, wp_json_encode( false ) );
		$this->assertFalse( ValidationCounts::is_needed() );

		// Should be needed when in Reader mode, on a devtools screen, and dev tools is enabled for the user.
		$_GET['post_type'] = AMP_Validated_URL_Post_Type::POST_TYPE_SLUG;
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::READER_MODE_SLUG );
		update_user_meta( $admin_user->ID, UserAccess::USER_FIELD_DEVELOPER_TOOLS_ENABLED, wp_json_encode( true ) );
		$this->assertTrue( ValidationCounts::is_needed() );

		// Should be needed when not on a dev tools screen, dev tools has never been configured for the user, but is enabled through a filter.
		add_filter( 'amp_dev_tools_user_default_enabled', '__return_true' );
		unset( $_GET['post'], $_GET['post_type'], $_GET['taxonomy'], $_GET['action'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		delete_user_meta( $admin_user->ID, UserAccess::USER_FIELD_DEVELOPER_TOOLS_ENABLED );
		$this->assertTrue( ValidationCounts::is_needed() );
	}

	/** @return array */
	public function maybe_add_preload_rest_paths_data() {
		return [
			'no_type_post'                => [
				'set_up'              => static function () {
					set_current_screen( 'user' );
				},
				'should_preload_path' => false,
			],
			'post_type_post'              => [
				'set_up'              => static function () {
					set_current_screen( 'edit' );
					get_current_screen()->post_type = 'post';
				},
				'should_preload_path' => false,
			],
			'post_type_page'              => [
				'set_up'              => static function () {
					set_current_screen( 'edit' );
					get_current_screen()->post_type = 'page';
				},
				'should_preload_path' => false,
			],
			'post_type_amp_validated_url' => [
				'set_up'              => static function () {
					set_current_screen( 'edit' );
					get_current_screen()->post_type = AMP_Validated_URL_Post_Type::POST_TYPE_SLUG;
				},
				'should_preload_path' => true,
			],
			'settings_screen'             => [
				'set_up'              => function () {
					global $pagenow, $plugin_page, $menu;
					$pagenow     = 'admin.php';
					$plugin_page = 'amp-options';
					$menu        = [
						[
							2 => $plugin_page,
						],
					];
					$this->assertEquals( AMP_Options_Manager::OPTION_NAME, get_admin_page_parent() );
				},
				'should_preload_path' => true,
			],
		];
	}

	/**
	 * @dataProvider maybe_add_preload_rest_paths_data
	 * @covers ::maybe_add_preload_rest_paths()
	 */
	public function test_maybe_add_preload_rest_paths( callable $set_up, $should_preload_path ) {
		if ( ! function_exists( 'rest_preload_api_request' ) ) {
			$this->markTestIncomplete( 'REST preload is not available so skipping.' );
		}

		$set_up();

		$this->call_private_method( $this->instance, 'maybe_add_preload_rest_paths' );

		$rest_preloader = $this->get_private_property( $this->instance, 'rest_preloader' );
		$paths          = $this->get_private_property( $rest_preloader, 'paths' );

		if ( $should_preload_path ) {
			$this->assertContains( '/amp/v1/unreviewed-validation-counts', $paths );
		} else {
			$this->assertNotContains( '/amp/v1/unreviewed-validation-counts', $paths );
		}
	}
}
