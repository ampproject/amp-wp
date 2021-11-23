<?php
/**
 * Test cases for CallbackWrapper
 *
 * @package AmpProject\AmpWP\EntityRegistrantDetection\Tests
 */

namespace AmpProject\AmpWP\EntityRegistrantDetection\Tests;

use AmpProject\AmpWP\DevTools\CallbackReflection;
use AmpProject\AmpWP\EntityRegistrantDetection\CallbackWrapper;
use AmpProject\AmpWP\EntityRegistrantDetection\EntityRegistrantDetectionManager;
use AmpProject\AmpWP\Tests\DependencyInjectedTestCase;
use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;

/**
 * @coversDefaultClass \AmpProject\AmpWP\EntityRegistrantDetection\CallbackWrapper
 */
class CallbackWrapperTest extends DependencyInjectedTestCase {

	use PrivateAccess;

	/**
	 * Instance of CallbackWrapper
	 *
	 * @var CallbackWrapper
	 */
	public $instance;

	/**
	 * Set up.
	 *
	 * @inheritdoc
	 */
	public function setUp() {

		parent::setUp();

		$callback = [
			'function'      => [ __CLASS__, 'register_entities' ],
			'accepted_args' => 0,
			'priority'      => 10,
			'hook'          => 'init',
		];

		$this->instance = $this->injector->make( CallbackWrapper::class, compact( 'callback' ) );

	}

	/**
	 * Tear down.
	 *
	 * @inheritDoc
	 */
	public function tearDown() {

		parent::tearDown();

		self::unregister_entities();
	}

	/**
	 * @covers ::__construct()
	 */
	public function test_construct() {

		$this->assertInstanceOf(
			EntityRegistrantDetectionManager::class,
			$this->get_private_property( $this->instance, 'detection_manager' )
		);

		$this->assertInstanceOf(
			CallbackReflection::class,
			$this->get_private_property( $this->instance, 'callback_reflection' )
		);

	}

	/**
	 * Register entities.
	 */
	public static function register_entities() {

		register_post_type( 'amp_test_post_type', [] );

		register_taxonomy( 'amp_test_taxonomy', [] );

		add_shortcode( 'amp_test_shortcode', '__return_empty_string' );

		register_block_type(
			'amp/test-block',
			[
				'render_callback' => '__return_empty_string',
			]
		);
	}

	/**
	 * Unregister entities.
	 */
	public static function unregister_entities() {
		unregister_post_type( 'amp_test_post_type' );

		unregister_taxonomy( 'amp_test_taxonomy' );

		remove_shortcode( 'amp_test_shortcode' );

		unregister_block_type( 'amp/test-block' );
	}

	/**
	 * @covers ::__invoke()
	 * @covers ::prepare()
	 * @covers ::finalize()
	 * @covers ::set_source()
	 * @covers ::get_callback_function()
	 * @covers ::get_registered_entities()
	 */
	public function test_callback_wrapper() {

		call_user_func( $this->instance );

		$registered_entities = $this->get_private_property( $this->instance, 'registered_entities' );
		$source              = $this->get_private_property( $this->instance, 'source' );

		$this->assertContains( 'amp_test_post_type', $registered_entities['post_type'] );
		$this->assertContains( 'amp_test_taxonomy', $registered_entities['taxonomy'] );
		$this->assertContains( 'amp_test_shortcode', $registered_entities['shortcode'] );
		$this->assertContains( 'amp/test-block', $registered_entities['block'] );

		$this->assertArrayHasKey( 'function', $source );
		$this->assertArrayHasKey( 'hook', $source );
		$this->assertArrayHasKey( 'priority', $source );
	}

	/**
	 * @covers ::offsetExists()
	 */
	public function test_offsetExists() {

		$this->assertTrue( isset( $this->instance['function'] ) );

		$this->assertfalse( isset( $this->instance['invalid_offset'] ) );
	}

	/**
	 * @covers ::offsetGet()
	 */
	public function test_offsetGet() {

		$callback = $this->get_private_property( $this->instance, 'callback' );

		$this->assertEquals( $callback['function'], $this->instance['function'] );

		$this->assertNull( $this->instance['invalid_offset'] );
	}

	/**
	 * @covers ::offsetSet()
	 */
	public function test_offsetSet() {

		$original_function = $this->instance['function'];

		$this->instance['function'] = '__return_empty_string';
		$this->instance[]           = 'some_value';

		$callback = $this->get_private_property( $this->instance, 'callback' );

		$this->assertEquals( '__return_empty_string', $callback['function'] );
		$this->assertEquals( 'some_value', $callback[0] );

		unset( $this->instance[0] );
		$this->instance['function'] = $original_function;
	}

	/**
	 * @covers ::offsetUnset()
	 */
	public function test_offsetUnset() {
		$original_function = $this->instance['function'];

		unset( $this->instance['function'] );

		$callback = $this->get_private_property( $this->instance, 'callback' );

		$this->assertFalse( isset( $callback['function'] ) );

		$this->instance['function'] = $original_function;
	}
}
