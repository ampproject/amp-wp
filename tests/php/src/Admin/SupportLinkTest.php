<?php
/**
 * Tests for SupportLink.
 *
 * @package AmpProject\AmpWP\Tests
 */

namespace AmpProject\AmpWP\Tests\Admin;

use AmpProject\AmpWP\Admin\SupportLink;
use AmpProject\AmpWP\Option;
use AMP_Options_Manager;
use AMP_Validation_Manager;
use AMP_Theme_Support;
use AMP_Validated_URL_Post_Type;
use AmpProject\AmpWP\Tests\Helpers\MockAdminUser;
use AmpProject\AmpWP\Tests\TestCase;

/**
 * Tests for Support Link.
 *
 * @group support-admin
 * @coversDefaultClass \AmpProject\AmpWP\Admin\SupportLink
 */
class SupportLinkTest extends TestCase {

	use MockAdminUser;

	/**
	 * Instance of SupportLink
	 *
	 * @var SupportLink
	 */
	public $instance;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function set_up() {

		parent::set_up();

		if ( ! class_exists( 'WP_Site_Health' ) ) {
			$this->markTestSkipped( 'Test requires Site Health.' );
		}

		$this->instance = new SupportLink();
	}

	/**
	 * @covers ::get_registration_action()
	 */
	public function test_get_registration_action() {
		$this->assertEquals(
			'wp_loaded',
			SupportLink::get_registration_action()
		);
	}

	/**
	 * @covers ::is_needed()
	 */
	public function test_is_needed() {

		// Test 1: Test for users that don't have permission.
		$this->assertFalse( SupportLink::is_needed() );

		// Test 2: Test with admin user.
		$this->mock_admin_user();

		$this->assertTrue( SupportLink::is_needed() );
	}

	/**
	 * @covers ::register()
	 */
	public function test_register() {

		set_current_screen( 'index.php' );

		$this->instance->register();

		$this->assertEquals( 105, has_action( 'admin_bar_menu', [ $this->instance, 'admin_bar_menu' ] ) );

		$this->assertEquals(
			10,
			has_filter(
				'amp_validated_url_status_actions',
				[
					$this->instance,
					'amp_validated_url_status_actions',
				]
			)
		);

		$this->assertEquals(
			PHP_INT_MAX,
			has_filter(
				'post_row_actions',
				[
					$this->instance,
					'post_row_actions',
				]
			)
		);

		$this->assertEquals( 10, has_filter( 'plugin_row_meta', [ $this->instance, 'plugin_row_meta' ] ) );

		set_current_screen( 'front' );
	}

	/**
	 * @covers ::admin_bar_menu()
	 */
	public function test_admin_bar_menu() {

		// Mock Admin user.
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );

		// Set AMP mode.
		AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::STANDARD_MODE_SLUG );

		require_once ABSPATH . WPINC . '/class-wp-admin-bar.php';
		$admin_bar = new \WP_Admin_Bar();

		$this->go_to( home_url( '/' ) );

		// AMP-first mode.
		AMP_Validation_Manager::add_admin_bar_menu_items( $admin_bar );
		$this->instance->admin_bar_menu( $admin_bar );

		$node = $admin_bar->get_node( 'amp-support' );

		$this->assertInstanceOf( 'stdClass', $node );
		$this->assertStringContainsString( 'page=amp-support', $node->href );
	}

	/**
	 * @covers ::amp_validated_url_status_actions()
	 */
	public function test_amp_validated_url_status_actions() {

		$post = $this->factory()->post->create_and_get(
			[
				'post_type' => AMP_Validated_URL_Post_Type::POST_TYPE_SLUG,
			]
		);

		$actions = $this->instance->amp_validated_url_status_actions( [], $post );

		$this->assertStringContainsString(
			'page=amp-support',
			$actions['amp-support']
		);

		$this->assertStringContainsString(
			sprintf( 'url=%s', rawurlencode( $post->post_title ) ),
			$actions['amp-support']
		);
	}

	/**
	 * Test post_row_actions method.
	 *
	 * @covers ::post_row_actions()
	 */
	public function test_post_row_actions() {

		// Test 1: With different post type.
		$post = $this->factory()->post->create_and_get();

		$actions = $this->instance->post_row_actions( [], $post );
		$this->assertEmpty( $actions );

		// Test 2: With "amp_validated_url" post type.
		$post = $this->factory()->post->create_and_get(
			[
				'post_type' => AMP_Validated_URL_Post_Type::POST_TYPE_SLUG,
			]
		);

		$actions = $this->instance->post_row_actions( [], $post );

		$this->assertStringContainsString(
			'page=amp-support',
			$actions['amp-support']
		);

		$this->assertStringContainsString(
			sprintf( 'url=%s', rawurlencode( $post->post_title ) ),
			$actions['amp-support']
		);
	}

	/**
	 * @covers ::plugin_row_meta()
	 */
	public function test_plugin_row_meta() {

		// Test 1: For other than AMP plugin
		$output = $this->instance->plugin_row_meta( [], 'hello-dolly.php' );
		$this->assertEmpty( $output );

		// Test 2: For AMP plugin
		$output = $this->instance->plugin_row_meta( [], 'amp/amp.php' );

		$should_have = sprintf(
			'<a href="%s">%s</a>',
			esc_url(
				add_query_arg(
					[ 'page' => 'amp-support' ],
					admin_url( 'admin.php' )
				)
			),
			esc_html__( 'Get support', 'amp' )
		);

		$this->assertContains( $should_have, $output );

	}
}
