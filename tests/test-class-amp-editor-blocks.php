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
			$this->assertEquals( 11, has_action( 'wp_loaded', array( $this->instance, 'register_block_latest_stories' ) ) );
			$this->assertEquals( 10, has_filter( 'wp_kses_allowed_html', array( $this->instance, 'whitelist_block_atts_in_wp_kses_allowed_html' ) ) );
			$this->assertEquals( 10, has_action( 'enqueue_block_assets', array( $this->instance, 'enqueue_block_assets' ) ) );

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
		$minimum_height = 200;
		$dimensions     = array( $minimum_height, 300, 500 );
		$stories        = $this->create_story_posts_with_featured_images( $dimensions );
		$rendered_block = $this->instance->render_block_latest_stories( $attributes );
		$this->assertContains( '<div class="latest-stories-carousel"', $rendered_block );
		$this->assertContains(
			sprintf(
				'height="%s"',
				$minimum_height
			),
			$rendered_block
		);

		foreach ( $stories as $story ) {
			$featured_image = get_post_thumbnail_id( $story );
			$this->assertContains( get_the_permalink( $story->ID ), $rendered_block );
			$this->assertContains(
				wp_get_attachment_image(
					$featured_image,
					$this->instance->image_size,
					false,
					array(
						'alt' => get_the_title( $story ),
					)
				),
				$rendered_block
			);
		}
	}

	/**
	 * Test get_minimum_dimension.
	 *
	 * @covers \AMP_Editor_Blocks::get_minimum_dimension()
	 */
	public function test_get_minimum_dimension() {
		$expected_min_height = 300;
		$dimensions          = array(
			$expected_min_height,
			400,
			500,
			600,
		);
		$stories             = $this->create_story_posts_with_featured_images( $dimensions );
		$this->assertEquals( $expected_min_height, $this->instance->get_minimum_dimension( 'height', $stories ) );

		$expected_min_width = 100;
		$dimensions         = array(
			$expected_min_width,
			200,
			300,
			800,
		);
		$stories            = $this->create_story_posts_with_featured_images( $dimensions );
		$this->assertEquals( $expected_min_width, $this->instance->get_minimum_dimension( 'width', $stories ) );

		// When an empty array() is passed, the minimum height should be 0.
		$this->assertEquals( 0, $this->instance->get_minimum_dimension( 'height', array() ) );
	}

	/**
	 * Test enqueue_block_assets().
	 *
	 * @covers \AMP_Editor_Blocks::enqueue_block_assets().
	 */
	public function test_enqueue_block_assets() {
		$this->instance->enqueue_block_assets();
		$styles          = wp_styles();
		$stylesheet_base = 'amp-blocks';
		$slug            = $stylesheet_base . '-style';
		$stylesheet      = $styles->registered[ $slug ];

		$this->assertEquals( $slug, $stylesheet->handle );
		$this->assertEquals( array(), $stylesheet->deps );
		$this->assertContains( $stylesheet_base . '.css', $stylesheet->src );
		$this->assertEquals( AMP__VERSION, $stylesheet->ver );
		$this->assertTrue( in_array( $slug, $styles->queue, true ) );
	}

	/**
	 * Test enqueue_block_editor_assets().
	 *
	 * @covers \AMP_Editor_Blocks::enqueue_block_editor_assets().
	 */
	public function test_enqueue_block_editor_assets() {
		$this->instance->enqueue_block_editor_assets();

		$slug    = 'amp-agnostic-blocks-compiled';
		$scripts = wp_scripts();
		$script  = $scripts->registered[ $slug ];

		$this->assertEquals( $slug, $script->handle );
		$this->assertEquals(
			array( 'wp-editor', 'wp-blocks', 'lodash', 'wp-i18n', 'wp-element', 'wp-components' ),
			$script->deps
		);
		$this->assertContains( $slug . '.js', $script->src );
		$this->assertEquals( AMP__VERSION, $script->ver );
		$this->assertTrue( in_array( $slug, $scripts->queue, true ) );
	}

	/**
	 * Creates amp_story posts with featured images of given heights.
	 *
	 * @param array $dimensions An array of strings.
	 * @return array $posts An array of WP_Post objects of the amp_story post type.
	 */
	public function create_story_posts_with_featured_images( $dimensions ) {
		$stories = array();
		foreach ( $dimensions as $dimension ) {
			$new_story = $this->factory()->post->create_and_get(
				array( 'post_type' => AMP_Story_Post_Type::POST_TYPE_SLUG )
			);
			array_push( $stories, $new_story );

			// Create the featured image.
			$thumbnail_id = wp_insert_attachment(
				array(
					'post_mime_type' => 'image/jpeg',
				),
				'https://example.com/foo-image.jpeg',
				$new_story->ID
			);
			set_post_thumbnail( $new_story, $thumbnail_id );

			wp_update_attachment_metadata(
				$thumbnail_id,
				array(
					'width'  => $dimension,
					'height' => $dimension,
				)
			);
		}

		return $stories;
	}
}
