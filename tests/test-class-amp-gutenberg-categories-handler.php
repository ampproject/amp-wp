<?php
/**
 * Tests for AMP_Gutenberg_Categories_Handler.
 *
 * @package AMP
 * @since 1.0
 */

/**
 * Tests for AMP_Gutenberg_Categories_Handler.
 *
 * @package AMP
 * @covers AMP_Gutenberg_Categories_Handler
 */
class Test_AMP_Gutenberg_Categories_Handler extends WP_UnitTestCase {

	/**
	 * Instance of the tested class.
	 *
	 * @var AMP_Gutenberg_Categories_Handler.
	 */
	public $instance;

	/**
	 * Set up test.
	 */
	public function setUp() {
		parent::setUp();
		$this->instance = new AMP_Gutenberg_Categories_Handler();

		// Require gutenberg file to be able to run the tests.
		if ( file_exists( AMP__DIR__ . '/../gutenberg/gutenberg.php' ) ) {
			require_once AMP__DIR__ . '/../gutenberg/gutenberg.php';
		}
	}

	/**
	 * Test register_embed.
	 *
	 * @covers AMP_Gutenberg_Categories_Handler::register_embed()
	 */
	public function test_register_embed() {
		if ( function_exists( 'gutenberg_init' ) ) {
			$this->instance->register_embed();
			$this->assertEquals( 10, has_action( 'the_post', array( $this->instance, 'override_category_block_render_callback' ) ) );
		}
	}
}
