<?php
/**
 * Test case for EntityRegistrantDetectionManager
 *
 * @package AmpProject\AmpWP\EntityRegistrantDetection\Tests
 */

namespace AmpProject\AmpWP\EntityRegistrantDetection\Tests;

use AmpProject\AmpWP\EntityRegistrantDetection\CallbackWrapper;
use AmpProject\AmpWP\EntityRegistrantDetection\EntityRegistrantDetectionManager;
use AmpProject\AmpWP\Tests\DependencyInjectedTestCase;
use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;

/**
 * @coversDefaultClass \AmpProject\AmpWP\EntityRegistrantDetection\EntityRegistrantDetectionManager
 */
class EntityRegistrantDetectionManagerTest extends DependencyInjectedTestCase {

	use PrivateAccess;

	/**
	 * Instance of EntityRegistrantDetectionManager
	 *
	 * @var EntityRegistrantDetectionManager
	 */
	public $instance;

	/**
	 * Mocked source information.
	 *
	 * @var array
	 */
	public $source = [];

	/**
	 * Set up.
	 *
	 * @inheritdoc
	 */
	public function setUp() {

		parent::setUp();

		$this->instance = $this->injector->make( EntityRegistrantDetectionManager::class );

		$this->source = [
			'type'     => 'plugin',
			'name'     => 'dummy-plugin',
			'file'     => 'includes/entity-registration.php',
			'line'     => 10,
			'function' => 'dummy_function_name',
			'hook'     => 'init',
			'priority' => 5,
		];
	}

	/**
	 * @covers ::get_registration_action()
	 */
	public function test_get_registration_action() {

		$this->assertEquals(
			'plugins_loaded',
			EntityRegistrantDetectionManager::get_registration_action()
		);
	}

	/**
	 * @covers ::is_needed()
	 */
	public function test_is_needed() {

		$this->assertFalse( EntityRegistrantDetectionManager::is_needed() );

		// Mock User.
		wp_set_current_user(
			self::factory()->user->create(
				[
					'role' => 'administrator',
				]
			)
		);

		$this->assertFalse( EntityRegistrantDetectionManager::is_needed() );

		// Mock $_GET.
		$_GET[ EntityRegistrantDetectionManager::NONCE_QUERY_VAR ] = EntityRegistrantDetectionManager::get_nonce();

		$this->assertTrue( EntityRegistrantDetectionManager::is_needed() );

		unset( $_GET[ EntityRegistrantDetectionManager::NONCE_QUERY_VAR ] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * @covers ::get_nonce()
	 */
	public function test_get_nonce() {

		$this->assertEquals(
			wp_hash( EntityRegistrantDetectionManager::NONCE_QUERY_VAR . wp_nonce_tick(), 'nonce' ),
			EntityRegistrantDetectionManager::get_nonce()
		);
	}

	/**
	 * @covers ::verify_nonce()
	 */
	public function test_verify_nonce() {

		$this->assertFalse( EntityRegistrantDetectionManager::verify_nonce( 'invalid_nonce_value' ) );

		$nonce = EntityRegistrantDetectionManager::get_nonce();
		$this->assertTrue( EntityRegistrantDetectionManager::verify_nonce( $nonce ) );
	}

	/**
	 * @covers ::add_source()
	 */
	public function test_add_source_post_type() {

		$this->assertFalse(
			EntityRegistrantDetectionManager::add_source(
				'invalid_entity_type',
				'invalid_entity',
				$this->source
			)
		);

		EntityRegistrantDetectionManager::add_source( 'post_type', 'invalid_post', $this->source );
		EntityRegistrantDetectionManager::add_source( 'post_type', 'post', $this->source );

		$post_types_source = $this->get_private_property( $this->instance, 'post_types_source' );

		$this->assertArrayNotHasKey( 'invalid_post', $post_types_source );

		$this->assertArraySubset(
			[
				'name'   => 'Posts',
				'slug'   => 'post',
				'source' => $this->source,
			],
			$post_types_source['post']
		);

	}

	/**
	 * @covers ::add_source()
	 */
	public function test_add_source_taxonomy() {

		EntityRegistrantDetectionManager::add_source( 'taxonomy', [ 'invalid_taxonomy', 'category' ], $this->source );

		$taxonomies_source = $this->get_private_property( $this->instance, 'taxonomies_source' );

		$this->assertArrayNotHasKey( 'invalid_taxonomy', $taxonomies_source );

		$this->assertArraySubset(
			[
				'name'   => 'Categories',
				'slug'   => 'category',
				'source' => $this->source,
			],
			$taxonomies_source['category']
		);

	}

	/**
	 * @covers ::add_source()
	 */
	public function test_add_source_shortcode() {

		EntityRegistrantDetectionManager::add_source( 'shortcode', [ 'invalid_shortcode', 'gallery' ], $this->source );

		$shortcodes_source = $this->get_private_property( $this->instance, 'shortcodes_source' );

		$this->assertArrayNotHasKey( 'invalid_shortcode', $shortcodes_source );

		$this->assertArraySubset(
			[
				'tag'    => 'gallery',
				'source' => $this->source,
			],
			$shortcodes_source['gallery']
		);

	}

	/**
	 * @covers ::add_source()
	 */
	public function test_add_source_block() {

		EntityRegistrantDetectionManager::add_source( 'block', [ 'invalid_block', 'core/button' ], $this->source );

		$blocks_source = $this->get_private_property( $this->instance, 'blocks_source' );

		$this->assertArrayNotHasKey( 'invalid_block', $blocks_source );

		$this->assertArraySubset(
			[
				'name'   => 'core/button',
				'title'  => 'core/button',
				'source' => $this->source,
			],
			$blocks_source['core/button']
		);

		$this->assertArrayHasKey( 'attributes', $blocks_source['core/button'] );
	}

	/**
	 * @covers ::register()
	 */
	public function test_register() {

		$this->instance->register();

		$int_min = defined( 'PHP_INT_MIN' ) ? PHP_INT_MIN : ~PHP_INT_MAX; // phpcs:ignore PHPCompatibility.Constants.NewConstants.php_int_minFound

		$this->assertEquals(
			$int_min,
			has_action( 'all', [ $this->instance, 'wrap_hook_callbacks' ] )
		);
	}

	/**
	 * @covers ::wrap_hook_callbacks()
	 */
	public function test_wrap_hook_callbacks() {

		global $wp_filter;

		$previous_wp_filter = $wp_filter;

		$this->instance->wrap_hook_callbacks( 'shutdown' );
		$this->assertEquals( $previous_wp_filter['shutdown'], $wp_filter['shutdown'] );

		add_action( 'init', [ __CLASS__, 'dummy_function' ] );

		$this->instance->wrap_hook_callbacks( 'init' );

		$this->assertInstanceOf(
			CallbackWrapper::class,
			$wp_filter['init']->callbacks[10][ __CLASS__ . '::dummy_function' ]['function']
		);

		remove_action( 'init', [ __CLASS__, 'dummy_function' ] );

		$wp_filter = $previous_wp_filter;
		unset( $previous_wp_filter );
	}

	/**
	 * Dummy function.
	 */
	public static function dummy_function() {
	}

	/**
	 * @covers ::wrapped_callback
	 */
	public function test_wrapped_callback() {

		$callable = [
			'function'      => __CLASS__ . '::dummy_function',
			'accepted_args' => 0,
		];

		$this->assertInstanceOf(
			CallbackWrapper::class,
			$this->call_private_static_method(
				EntityRegistrantDetectionManager::class,
				'wrapped_callback',
				[ $callable ]
			)
		);
	}
}
