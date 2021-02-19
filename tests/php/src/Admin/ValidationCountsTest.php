<?php
/**
 * Tests for ValidationCountsTest class.
 *
 * @package AmpProject\AmpWP\Tests
 */

namespace AmpProject\AmpWP\Tests\Admin;

use AmpProject\AmpWP\Admin\ValidationCounts;
use AmpProject\AmpWP\DevTools\UserAccess;
use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use WP_UnitTestCase;

/**
 * Tests for ValidationCounts class.
 *
 * @coversDefaultClass \AmpProject\AmpWP\Admin\ValidationCounts
 */
class ValidationCountsTest extends WP_UnitTestCase {

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
	public function setUp() {
		parent::setUp();

		$this->instance = new ValidationCounts();
	}

	public function test__construct() {
		$this->assertInstanceOf( ValidationCounts::class, $this->instance );
		$this->assertInstanceOf( Delayed::class, $this->instance );
		$this->assertInstanceOf( Conditional::class, $this->instance );
		$this->assertInstanceOf( Service::class, $this->instance );
		$this->assertInstanceOf( Registerable::class, $this->instance );
	}

	/**
	 * Test ::get_registration_action().
	 *
	 * @covers ::get_registration_action()
	 */
	public function test_get_registration_action() {
		self::assertEquals( 'admin_init', ValidationCounts::get_registration_action() );
	}

	/**
	 * Test ::register().
	 *
	 * @covers ::register()
	 */
	public function test_register() {
		$this->instance->register();

		$this->assertEquals( 10, has_action( 'admin_enqueue_scripts', [ $this->instance, 'enqueue_scripts' ] ) );
	}

	/**
	 * Test ::is_needed().
	 *
	 * @covers ::is_needed()
	 */
	public function test_is_needed() {
		$this->assertFalse( ValidationCounts::is_needed() );

		$admin_user = self::factory()->user->create_and_get( [ 'role' => 'administrator' ] );
		wp_set_current_user( $admin_user->ID );
		update_user_meta( $admin_user->ID, UserAccess::USER_FIELD_DEVELOPER_TOOLS_ENABLED, wp_json_encode( true ) );

		$this->assertTrue( ValidationCounts::is_needed() );
	}
}
