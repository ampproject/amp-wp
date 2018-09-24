<?php
/**
 * Tests for AMP_Core_Block_Handler.
 *
 * @package AMP
 * @since 1.0
 */

/**
 * Tests for AMP_Core_Block_Handler.
 *
 * @package AMP
 * @covers AMP_Core_Block_Handler
 */
class Test_AMP_Core_Block_Handler extends WP_UnitTestCase {

	/**
	 * Instance of the tested class.
	 *
	 * @var AMP_Core_Block_Handler.
	 */
	public $instance;

	/**
	 * Test block name.
	 *
	 * @var string
	 */
	public $test_block = 'core/test';

	/**
	 * Setup.
	 */
	public function setUp() {
		parent::setUp();

		// Require gutenberg file to be able to run the tests.
		if ( file_exists( AMP__DIR__ . '/../gutenberg/gutenberg.php' ) ) {
			require_once AMP__DIR__ . '/../gutenberg/gutenberg.php';

			if ( ! function_exists( 'register_block_type' ) ) {
				require_once AMP__DIR__ . '/../gutenberg/lib/class-wp-block-type.php';
				require_once AMP__DIR__ . '/../gutenberg/lib/class-wp-block-type-registry.php';
				require_once AMP__DIR__ . '/../gutenberg/lib/blocks.php';
			}
		}
	}

	/**
	 * Teardown.
	 */
	public function tearDown() {
		if ( function_exists( 'register_block_type' ) ) {
			$this->unregister_dummy_block();
		}

		parent::tearDown();
	}

	/**
	 * Register test block.
	 */
	public function register_dummy_block() {
		$settings = array(
			'render_callback' => '__return_true',
		);
		register_block_type( $this->test_block, $settings );
	}

	/**
	 * Unregister test block.
	 */
	public function unregister_dummy_block() {
		unregister_block_type( $this->test_block );
	}

	/**
	 * Test register_embed().
	 */
	public function test_register_embed() {

		if ( ! function_exists( 'register_block_type' ) ) {
			$this->markTestIncomplete( 'Files needed for testing missing.' );
		}

		$this->register_dummy_block();

		$this->instance             = new AMP_Core_Block_Handler();
		$this->instance->block_name = $this->test_block;

		$registry = WP_Block_Type_Registry::get_instance();
		$block    = $registry->get_registered( $this->test_block );

		$this->assertEquals( '__return_true', $block->render_callback );

		$this->instance->register_embed();

		$registry = WP_Block_Type_Registry::get_instance();
		$block    = $registry->get_registered( $this->test_block );

		$this->assertTrue( is_array( $block->render_callback ) );

		$this->unregister_dummy_block();
	}

	/**
	 * Test unregister_embed().
	 */
	public function test_unregister_embed() {
		if ( ! function_exists( 'register_block_type' ) ) {
			$this->markTestIncomplete( 'Files needed for testing missing.' );
		}

		$this->register_dummy_block();

		$this->instance             = new AMP_Core_Block_Handler();
		$this->instance->block_name = $this->test_block;

		$this->instance->register_embed();
		$this->instance->unregister_embed();

		$registry = WP_Block_Type_Registry::get_instance();
		$block    = $registry->get_registered( $this->test_block );

		$this->assertEquals( '__return_true', $block->render_callback );

		$this->unregister_dummy_block();
	}
}
