<?php
/**
 * Tests for AMP_Editor_Blocks class.
 *
 * @package AMP
 */

use AmpProject\AmpWP\Option;
use Yoast\WPTestUtils\WPIntegration\TestCase;

/**
 * Tests for AMP_Editor_Blocks class.
 *
 * @covers AMP_Editor_Blocks
 */
class Test_AMP_Editor_Blocks extends TestCase {

	/**
	 * The tested instance.
	 *
	 * @var AMP_Editor_Blocks
	 */
	public $instance;

	/**
	 * Instantiates the tested class.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();
		$this->instance = new AMP_Editor_Blocks();
	}

	/**
	 * Test init.
	 *
	 * @covers \AMP_Editor_Blocks::init()
	 */
	public function test_init() {
		$this->instance->init();
		if ( function_exists( 'register_block_type' ) ) {
			$this->assertEquals( 10, has_filter( 'wp_kses_allowed_html', [ $this->instance, 'include_block_atts_in_wp_kses_allowed_html' ] ) );

			// Because amp_is_canonical() is false, these should not be hooked.
			$this->assertFalse( has_filter( 'the_content', [ $this->instance, 'tally_content_requiring_amp_scripts' ] ) );
			$this->assertFalse( has_action( 'wp_print_footer_scripts', [ $this->instance, 'print_dirty_amp_scripts' ] ) );

			AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::STANDARD_MODE_SLUG );
			$this->instance->init();

			// Now that amp_is_canonical() is true, these action hooks should be added.
			$this->assertEquals( 10, has_filter( 'the_content', [ $this->instance, 'tally_content_requiring_amp_scripts' ] ) );
			$this->assertEquals( 10, has_action( 'wp_print_footer_scripts', [ $this->instance, 'print_dirty_amp_scripts' ] ) );
			AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::READER_MODE_SLUG );
		}
	}
}
