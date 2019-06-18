<?php
/**
 * Test AMP_Story_Post_Type.
 *
 * @package AMP
 */

/**
 * Test AMP_Story_Post_Type.
 */
class AMP_Story_Post_Type_Test extends WP_UnitTestCase {

	/**
	 * Set up.
	 */
	public function setUp() {
		parent::setUp();

		if ( ! AMP_Story_Post_Type::has_required_block_capabilities() ) {
			$this->markTestSkipped( 'The function register_block_type() is not present, so the AMP Story post type was not registered.' );
		}

		if ( class_exists( 'WP_Block_Type_Registry' ) ) {
			foreach ( WP_Block_Type_Registry::get_instance()->get_all_registered() as $block ) {
				if ( 'amp/' === substr( $block->name, 0, 4 ) ) {
					WP_Block_Type_Registry::get_instance()->unregister( $block->name );
				}
			}
		}

		global $wp_styles;
		$wp_styles = null;
		AMP_Options_Manager::update_option( 'experiences', array( AMP_Options_Manager::STORIES_EXPERIENCE ) );
	}

	/**
	 * Reset the permalink structure to the state before the tests.
	 *
	 * @global WP_Rewrite $wp_rewrite
	 */
	public function tearDown() {
		global $wp_rewrite;

		AMP_Options_Manager::update_option( 'experiences', array( AMP_Options_Manager::WEBSITE_EXPERIENCE ) );
		unregister_post_type( AMP_Story_Post_Type::POST_TYPE_SLUG );

		$wp_rewrite->set_permalink_structure( false );
		unset( $_SERVER['HTTPS'] );
		unset( $GLOBALS['current_screen'] );
		parent::tearDown();
	}

	/**
	 * Test requires opt_in.
	 *
	 * @covers \AMP_Story_Post_Type::register()
	 */
	public function test_requires_opt_in() {
		unregister_post_type( AMP_Story_Post_Type::POST_TYPE_SLUG );

		AMP_Options_Manager::update_option( 'experiences', array( AMP_Options_Manager::WEBSITE_EXPERIENCE ) );
		AMP_Story_Post_Type::register();
		$this->assertFalse( post_type_exists( AMP_Story_Post_Type::POST_TYPE_SLUG ) );

		AMP_Options_Manager::update_option( 'experiences', array( AMP_Options_Manager::STORIES_EXPERIENCE ) );
		AMP_Story_Post_Type::register();
		$this->assertTrue( post_type_exists( AMP_Story_Post_Type::POST_TYPE_SLUG ) );
	}

	/**
	 * Test the_single_story_card.
	 *
	 * @covers AMP_Story_Post_Type::the_single_story_card()
	 */
	public function test_the_single_story_card() {
		$featured_image_dimensions = array( array( 1200, 1300 ), array( 1300, 1400 ), array( 1400, 1500 ) );
		$stories                   = $this->create_story_posts_with_featured_images( $featured_image_dimensions );

		foreach ( $stories as $story ) {
			ob_start();
			AMP_Story_Post_Type::the_single_story_card(
				array(
					'post' => $story,
					'size' => AMP_Story_Post_Type::STORY_LANDSCAPE_IMAGE_SIZE,
				)
			);
			$card_markup = ob_get_clean();
			$this->assertContains( get_the_permalink( $story->ID ), $card_markup );
			$this->assertContains( ' class="latest_stories__link"', $card_markup );
			// Because there's no 'disable_link' argument, this should have an <a> with an href.
			$this->assertContains( '<a href=', $card_markup );
		}

		$first_story = reset( $stories );
		ob_start();
		AMP_Story_Post_Type::the_single_story_card(
			array(
				'post'         => $first_story,
				'size'         => AMP_Story_Post_Type::STORY_LANDSCAPE_IMAGE_SIZE,
				'disable_link' => true,
			)
		);
		$this->assertNotContains( '<a', ob_get_clean() );

		// If the 'post' argument isn't either an int or a WP_Post, this shouldn't output anything.
		ob_start();
		AMP_Story_Post_Type::the_single_story_card( array( 'post' => 'foo post' ) );
		$this->assertEmpty( ob_get_clean() );
	}

	/**
	 * Test get_embed_template.
	 *
	 * @covers AMP_Story_Post_Type::get_embed_template()
	 */
	public function test_get_embed_template() {
		$template          = '/srv/www/baz.php';
		$wrong_type        = 'post';
		$correct_type      = 'embed';
		$wrong_templates   = array( 'embed-testimonal.php', 'embed.php' );
		$correct_template  = sprintf( 'embed-%s.php', AMP_Story_Post_Type::POST_TYPE_SLUG );
		$expected_template = 'embed-amp-story.php';
		$correct_templates = array( $correct_template, 'embed.php' );

		$this->assertEquals( $template, AMP_Story_Post_Type::get_embed_template( $template, $wrong_type, $correct_templates ) );
		$this->assertEquals( $template, AMP_Story_Post_Type::get_embed_template( $template, $correct_type, $wrong_templates ) );
		$this->assertContains( $expected_template, AMP_Story_Post_Type::get_embed_template( $template, $correct_type, $correct_templates ) );
	}

	/**
	 * Test enqueue_embed_styling.
	 *
	 * @covers AMP_Story_Post_Type::enqueue_embed_styling()
	 */
	public function test_enqueue_embed_styling() {
		AMP_Story_Post_Type::register();

		// None of the conditional is satisfied, so this should not enqueue the stylesheet.
		AMP_Story_Post_Type::enqueue_embed_styling();
		$this->assertFalse( wp_style_is( AMP_Story_Post_Type::STORY_CARD_CSS_SLUG ) );

		// Only the first part of the conditional is satisfied, so this again should not enqueue the stylesheet.
		$this->go_to( add_query_arg( 'embed', '' ) );
		AMP_Story_Post_Type::enqueue_embed_styling();
		$this->assertFalse( wp_style_is( AMP_Story_Post_Type::STORY_CARD_CSS_SLUG ) );

		// Now that the conditional is satisfied, this should enqueue the stylesheet.
		$amp_story_post = $this->factory()->post->create_and_get( array( 'post_type' => AMP_Story_Post_Type::POST_TYPE_SLUG ) );
		$this->go_to( add_query_arg( 'embed', '', get_post_permalink( $amp_story_post ) ) );
		AMP_Story_Post_Type::enqueue_embed_styling();
	}

	/**
	 * Test override_story_embed_callback.
	 *
	 * @covers AMP_Story_Post_Type::override_story_embed_callback()
	 */
	public function test_override_story_embed_callback() {
		global $wp_rewrite;

		AMP_Story_Post_Type::register();

		/*
		 * It looks like embedding custom post types does not work with the plain permalink structure.
		 * Also, this adds the permastruct for the AMP story post type, like http://example.com/stories/example-story-name.
		 * It seems that it's not enough to call AMP_Story_Post_Type::register().
		 */
		$wp_rewrite->set_permalink_structure( '/%postname%/' );
		$wp_rewrite->add_permastruct( AMP_Story_Post_Type::POST_TYPE_SLUG, AMP_Story_Post_Type::REWRITE_SLUG . '/%' . AMP_Story_Post_Type::POST_TYPE_SLUG . '%' );

		// The second argument is an empty array, so this should simply exit.
		$this->assertEmpty( AMP_Story_Post_Type::override_story_embed_callback( null, array() ) );

		// The conditional is not satisfied, so this should return null.
		$wrong_url   = 'https://incorrect-domain.com/example-story';
		$wrong_block = array(
			'attrs'     => array( 'url' => $wrong_url ),
			'blockName' => 'core/incorrect-block',
		);
		$this->assertEquals( null, AMP_Story_Post_Type::override_story_embed_callback( null, $wrong_block ) );

		// The conditional is only partially satisfied, as the URL is still wrong.
		$correct_block_name = 'core-embed/wordpress';
		$wrong_url          = 'https://incorrect-domain.com/example-story';
		$wrong_block        = array(
			'attrs'     => array( 'url' => $wrong_url ),
			'blockName' => $correct_block_name,
		);
		$this->assertEquals( null, AMP_Story_Post_Type::override_story_embed_callback( null, $wrong_block ) );

		// The conditional is now satisfied, so this should return the overriden callback.
		$story_posts    = $this->create_story_posts_with_featured_images( array( 400, 400 ) );
		$amp_story_post = reset( $story_posts );
		$correct_url    = get_post_permalink( $amp_story_post );
		$correct_block  = array(
			'attrs'     => array( 'url' => $correct_url ),
			'blockName' => $correct_block_name,
		);

		$overriden_render_callback = AMP_Story_Post_Type::override_story_embed_callback( null, $correct_block );
		$this->assertContains( get_permalink( $amp_story_post ), $overriden_render_callback );
		$this->assertContains( get_the_post_thumbnail_url( $amp_story_post ), $overriden_render_callback );

		// This should override the callback even if the site uses HTTPS and the permalink uses HTTP.
		$_SERVER['HTTPS'] = 'on';
		$correct_block    = array(
			'attrs'     => array( 'url' => set_url_scheme( $correct_url, 'http' ) ),
			'blockName' => $correct_block_name,
		);

		$overriden_render_callback = AMP_Story_Post_Type::override_story_embed_callback( null, $correct_block );
		$this->assertContains( get_permalink( $amp_story_post ), $overriden_render_callback );
		$this->assertContains( get_the_post_thumbnail_url( $amp_story_post ), $overriden_render_callback );
	}

	/**
	 * Test register_block_latest_stories.
	 *
	 * @covers AMP_Story_Post_Type::register_block_latest_stories()
	 */
	public function test_register_block_latest_stories() {
		AMP_Story_Post_Type::register_block_latest_stories();

		set_current_screen( 'edit.php' );
		$block_name           = 'amp/amp-latest-stories';
		$latest_stories_block = WP_Block_Type_Registry::get_instance()->get_registered( $block_name );

		$this->assertNotNull( $latest_stories_block );
		$this->assertEquals(
			array(
				'className'     => array(
					'type' => 'string',
				),
				'storiesToShow' => array(
					'type'    => 'number',
					'default' => 5,
				),
				'order'         => array(
					'type'    => 'string',
					'default' => 'desc',
				),
				'orderBy'       => array(
					'type'    => 'string',
					'default' => 'date',
				),
				'useCarousel'   => array(
					'type'    => 'boolean',
					'default' => true,
				),
			),
			$latest_stories_block->attributes
		);
		$this->assertEquals( null, $latest_stories_block->editor_script );
		$this->assertEquals( null, $latest_stories_block->editor_style );
		$this->assertEquals( $block_name, $latest_stories_block->name );
		$this->assertEquals( array( 'AMP_Story_Post_Type', 'render_block_latest_stories' ), $latest_stories_block->render_callback );
		$this->assertEquals( null, $latest_stories_block->script );
		$this->assertEquals( null, $latest_stories_block->style );
	}

	/**
	 * Test render_block_latest_stories.
	 *
	 * @covers \AMP_Story_Post_Type::render_block_latest_stories()
	 */
	public function test_render_block_latest_stories() {
		AMP_Story_Post_Type::register();

		$attributes = array(
			'storiesToShow' => 10,
			'order'         => 'desc',
			'orderBy'       => 'date',
			'useCarousel'   => true,
		);

		// Create mock AMP story posts to test.
		$minimum_height = 200;
		$dimensions     = array( array( $minimum_height, 200 ), array( 300, 400 ), array( 500, 600 ) );
		$this->create_story_posts_with_featured_images( $dimensions );
		$rendered_block = AMP_Story_Post_Type::render_block_latest_stories( $attributes );
		$this->assertContains( '<amp-carousel', $rendered_block );
		$this->assertContains( '<div class="slide latest-stories__slide">', $rendered_block );
		$this->assertContains( '<div class="latest-stories__meta">', $rendered_block );

		// Assert that wp_enqueue_style() was called in the render callback.
		$this->assertTrue( wp_style_is( AMP_Story_Post_Type::STORY_CARD_CSS_SLUG, 'registered' ) );
		$this->assertTrue( wp_style_is( AMP_Story_Post_Type::STORY_CARD_CSS_SLUG, 'enqueued' ) );
	}

	/**
	 * Test remove_title_from_embed.
	 *
	 * @covers \AMP_Story_Post_Type::remove_title_from_embed()
	 */
	public function test_remove_title_from_embed() {
		$initial_output = '<iframe src="https://example.com/baz"></iframe>';
		$wrong_post     = $this->factory()->post->create_and_get();

		// The post type is not amp_story, so this should return the same $output it's passed.
		$this->assertEquals( $initial_output, AMP_Story_Post_Type::remove_title_from_embed( $initial_output, $wrong_post ) );

		// The post type is correct, but the <blockquote> does not have the expected class, so this should again return the same $output.
		$correct_post              = $this->factory()->post->create_and_get( array( 'post_type' => AMP_Story_Post_Type::POST_TYPE_SLUG ) );
		$block_quote_without_class = '<blockquote>Example Title</blockquote>';
		$output_with_blockquote    = $block_quote_without_class . $initial_output;
		$this->assertEquals( $output_with_blockquote, AMP_Story_Post_Type::remove_title_from_embed( $output_with_blockquote, $correct_post ) );

		// All of the conditions are satisfied, so this should remove the <blockquote> and the elements it contains.
		$correct_post           = $this->factory()->post->create_and_get( array( 'post_type' => AMP_Story_Post_Type::POST_TYPE_SLUG ) );
		$block_quote            = '<blockquote class="wp-embedded-content">Example Title</blockquote>';
		$output_with_blockquote = $block_quote . $initial_output;
		$this->assertEquals( $initial_output, AMP_Story_Post_Type::remove_title_from_embed( $output_with_blockquote, $correct_post ) );
	}

	/**
	 * Test change_embed_iframe_attributes.
	 *
	 * @covers \AMP_Story_Post_Type::change_embed_iframe_attributes()
	 */
	public function test_change_embed_iframe_attributes() {
		remove_theme_support( 'amp' );
		$original_embed_markup = '<iframe sandbox="allow-scripts" width="600" height="343" security="restricted" marginwidth="10" marginheight="10">';
		$non_amp_story         = $this->factory()->post->create_and_get();

		// This isn't an AMP story embed, so it shouldn't change the markup.
		$this->assertEquals( $original_embed_markup, AMP_Story_Post_Type::change_embed_iframe_attributes( $original_embed_markup, $non_amp_story ) );

		// This is an AMP story embed, but the markup doesn't have an <iframe>, so it shouldn't be changed.
		$amp_story                   = $this->factory()->post->create_and_get( array( 'post_type' => AMP_Story_Post_Type::POST_TYPE_SLUG ) );
		$embed_markup_without_iframe = '<div class="wp-embed"><img alt=baz src="https://example.com/baz.jpeg></div>';
		$this->assertEquals( $embed_markup_without_iframe, AMP_Story_Post_Type::change_embed_iframe_attributes( $embed_markup_without_iframe, $amp_story ) );

		// This is an AMP story embed, so it should change the height.
		$this->assertEquals(
			'<iframe sandbox="allow-scripts" width="600" height="468" security="restricted" marginwidth="10" marginheight="10">',
			AMP_Story_Post_Type::change_embed_iframe_attributes( $original_embed_markup, $amp_story )
		);
	}

	/**
	 * Creates amp_story posts with featured images of given heights.
	 *
	 * @param array $featured_images {
	 *     The featured image dimensions.
	 *
	 *     @type int width
	 *     @type int height
	 * }
	 * @return array $posts An array of WP_Post objects of the amp_story post type.
	 */
	public function create_story_posts_with_featured_images( $featured_images ) {
		$stories = array();
		foreach ( $featured_images as $dimensions ) {
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
					'width'  => $dimensions[0],
					'height' => $dimensions[1],
				)
			);
		}

		return $stories;
	}

	/**
	 * Test amp_print_story_auto_ads()
	 *
	 * @covers ::amp_print_story_auto_ads()
	 */
	public function test_amp_print_story_auto_ads_empty() {
		$actual = get_echo( 'amp_print_story_auto_ads' );

		$this->assertEmpty( $actual );
	}

	/**
	 * Test amp_print_story_auto_ads()
	 *
	 * @covers ::amp_print_story_auto_ads()
	 */
	public function test_amp_print_story_auto_ads() {
		add_filter(
			'amp_story_auto_ads_configuration',
			static function() {
				return array(
					'ad-attributes' => array(
						'type'      => 'doubleclick',
						'data-slot' => '/30497360/a4a/amp_story_dfp_example',
					),
				);
			}
		);

		$actual = get_echo( 'amp_print_story_auto_ads' );

		$this->assertStringStartsWith( '<amp-story-auto-ads', $actual );
		$this->assertContains( '<script type="application/json">{"ad-attributes":{"type":"doubleclick"', $actual );
	}
}
