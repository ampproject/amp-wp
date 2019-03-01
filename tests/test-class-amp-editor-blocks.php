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
			$this->assertEquals( 10, has_action( 'wp_kses_allowed_html', array( $this->instance, 'whitelist_block_atts_in_wp_kses_allowed_html' ) ) );
			$this->assertEquals( 10, has_action( 'init', array( $this->instance, 'register_latest_stories_block' ) ) );

			// Because amp_is_canonical() is false, these
			$this->assertFalse( has_action( 'the_content', array( $this->instance, 'tally_content_requiring_amp_scripts' ) ) );
			$this->assertFalse( has_action( 'wp_print_footer_scripts', array( $this->instance, 'print_dirty_amp_scripts' ) ) );

			add_theme_support( 'amp' );
			$this->instance->init();

			// Now that amp_is_canonical() is true, these action hooks should be added.
			$this->assertEquals( 10, has_action( 'the_content', array( $this->instance, 'tally_content_requiring_amp_scripts' ) ) );
			$this->assertEquals( 10, has_action( 'wp_print_footer_scripts', array( $this->instance, 'print_dirty_amp_scripts' ) ) );
			rmeove_theme_support( 'amp' );
		}
	}

	/**
	 * Test render_block_latest_stories.
	 *
	 * @covers \AMP_Editor_Blocks::render_block_latest_stories()
	 */
	public function test_render_block_latest_stories() {
		$attributes = array(
			'storiesToShow' => 10,
			'order'         => 'desc',
			'orderBy'       => 'date',
		);

		// Create mock AMP story posts to test.
		$stories = array();
		for ( $i = 0; $i < 5; $i++ ) {
			$new_story = $this->factory()->post->create_and_get(
				array( 'post_type' => AMP_Story_Post_Type::POST_TYPE_SLUG )
			);
			array_push( $stories, $new_story );
		}

		$rendered_block = $this->instance->render_block_latest_stories( $attributes );
		$this->assertContains( '<amp-carousel', $rendered_block );

		foreach ( $stories as $story ) {
			$this->assertContains( get_the_permalink( $story->ID ), $rendered_block );
			$this->assertContains( get_the_post_thumbnail( $story->ID ), $rendered_block );
		}
	}
}
