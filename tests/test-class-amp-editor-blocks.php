<?php
/**
 * Tests for AMP_Editor_Blocks class.
 *
 * @package AMP
 */

/**
 * Tests for AMP_Editor_Blocks class.
 *
 * @covers AMP_Editor_Blocks
 */
class Test_AMP_Editor_Blocks extends \WP_UnitTestCase {

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
			$this->assertEquals( 10, has_action( 'enqueue_block_editor_assets', array( $this->instance, 'enqueue_block_editor_assets' ) ) );
			$this->assertEquals( 10, has_filter( 'wp_kses_allowed_html', array( $this->instance, 'whitelist_block_atts_in_wp_kses_allowed_html' ) ) );

			// Because amp_is_canonical() is false, these should not be hooked.
			$this->assertFalse( has_filter( 'the_content', array( $this->instance, 'tally_content_requiring_amp_scripts' ) ) );
			$this->assertFalse( has_action( 'wp_print_footer_scripts', array( $this->instance, 'print_dirty_amp_scripts' ) ) );

			add_theme_support( 'amp' );
			$this->instance->init();

			// Now that amp_is_canonical() is true, these action hooks should be added.
			$this->assertEquals( 10, has_filter( 'the_content', array( $this->instance, 'tally_content_requiring_amp_scripts' ) ) );
			$this->assertEquals( 10, has_action( 'wp_print_footer_scripts', array( $this->instance, 'print_dirty_amp_scripts' ) ) );
			remove_theme_support( 'amp' );
		}
	}

	/**
	 * Test enqueue_block_editor_assets().
	 *
	 * @covers \AMP_Editor_Blocks::enqueue_block_editor_assets()
	 */
	public function test_enqueue_block_editor_assets() {
		set_current_screen( 'admin.php' );
		$slug               = 'amp-editor-blocks';
		$expected_file_name = 'amp-editor-blocks.js';
		$this->instance->enqueue_block_editor_assets();
		$scripts = wp_scripts();
		$script  = $scripts->registered[ $slug ];

		$this->assertEquals( $slug, $script->handle );
		$this->assertEqualSets(
			array( 'wp-editor', 'wp-edit-post', 'wp-blocks', 'lodash', 'wp-i18n', 'wp-element', 'wp-components' ),
			$script->deps
		);
		$this->assertContains( $expected_file_name, $script->src );
		$this->assertEquals( AMP__VERSION, $script->ver );
		$this->assertTrue( in_array( $slug, $scripts->queue, true ) );
	}
}
