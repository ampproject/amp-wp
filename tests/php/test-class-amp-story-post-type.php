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
		AMP_Options_Manager::update_option( 'experiences', [ AMP_Options_Manager::STORIES_EXPERIENCE ] );
	}

	/**
	 * Reset the permalink structure to the state before the tests.
	 *
	 * @global WP_Rewrite $wp_rewrite
	 */
	public function tearDown() {
		global $wp_rewrite;

		AMP_Options_Manager::update_option( 'experiences', [ AMP_Options_Manager::WEBSITE_EXPERIENCE ] );
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

		AMP_Options_Manager::update_option( 'experiences', [ AMP_Options_Manager::WEBSITE_EXPERIENCE ] );
		AMP_Story_Post_Type::register();
		$this->assertFalse( post_type_exists( AMP_Story_Post_Type::POST_TYPE_SLUG ) );

		AMP_Options_Manager::update_option( 'experiences', [ AMP_Options_Manager::STORIES_EXPERIENCE ] );
		AMP_Story_Post_Type::register();
		$this->assertTrue( post_type_exists( AMP_Story_Post_Type::POST_TYPE_SLUG ) );
	}

	/**
	 * Test the_single_story_card.
	 *
	 * @covers AMP_Story_Post_Type::the_single_story_card()
	 */
	public function test_the_single_story_card() {
		$featured_image_dimensions = [ [ 1200, 1300 ], [ 1300, 1400 ], [ 1400, 1500 ] ];
		$stories                   = $this->create_story_posts_with_featured_images( $featured_image_dimensions );

		foreach ( $stories as $story ) {
			$card_markup = get_echo(
				[ 'AMP_Story_Post_Type', 'the_single_story_card' ],
				[
					[
						'post' => $story,
						'size' => AMP_Story_Media::STORY_LANDSCAPE_IMAGE_SIZE,
					],
				]
			);
			$this->assertContains( get_the_permalink( $story->ID ), $card_markup );
			$this->assertContains( ' class="latest_stories__link"', $card_markup );
			// Because there's no 'disable_link' argument, this should have an <a> with an href.
			$this->assertContains( '<a href=', $card_markup );
		}

		$first_story = reset( $stories );
		$card_markup = get_echo(
			[ 'AMP_Story_Post_Type', 'the_single_story_card' ],
			[
				[
					'post'         => $first_story,
					'size'         => AMP_Story_Media::STORY_LANDSCAPE_IMAGE_SIZE,
					'disable_link' => true,
				],
			]
		);
		$this->assertNotContains( '<a', $card_markup );

		// If the 'post' argument isn't either an int or a WP_Post, this shouldn't output anything.
		$card_markup = get_echo(
			[ 'AMP_Story_Post_Type', 'the_single_story_card' ],
			[ [ 'post' => 'foo post' ] ]
		);
		$this->assertEmpty( $card_markup );
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
		$wrong_templates   = [ 'embed-testimonal.php', 'embed.php' ];
		$correct_template  = sprintf( 'embed-%s.php', AMP_Story_Post_Type::POST_TYPE_SLUG );
		$expected_template = 'embed-amp-story.php';
		$correct_templates = [ $correct_template, 'embed.php' ];

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
		$amp_story_post = self::factory()->post->create_and_get( [ 'post_type' => AMP_Story_Post_Type::POST_TYPE_SLUG ] );
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
		$this->assertEmpty( AMP_Story_Post_Type::override_story_embed_callback( null, [] ) );

		// The conditional is not satisfied, so this should return null.
		$wrong_url   = 'https://incorrect-domain.com/example-story';
		$wrong_block = [
			'attrs'     => [ 'url' => $wrong_url ],
			'blockName' => 'core/incorrect-block',
		];
		$this->assertEquals( null, AMP_Story_Post_Type::override_story_embed_callback( null, $wrong_block ) );

		// The conditional is only partially satisfied, as the URL is still wrong.
		$correct_block_name = 'core-embed/wordpress';
		$wrong_url          = 'https://incorrect-domain.com/example-story';
		$wrong_block        = [
			'attrs'     => [ 'url' => $wrong_url ],
			'blockName' => $correct_block_name,
		];
		$this->assertEquals( null, AMP_Story_Post_Type::override_story_embed_callback( null, $wrong_block ) );

		// The conditional is now satisfied, so this should return the overriden callback.
		$story_posts    = $this->create_story_posts_with_featured_images( [ 400, 400 ] );
		$amp_story_post = reset( $story_posts );
		$correct_url    = get_post_permalink( $amp_story_post );
		$correct_block  = [
			'attrs'     => [ 'url' => $correct_url ],
			'blockName' => $correct_block_name,
		];

		$overriden_render_callback = AMP_Story_Post_Type::override_story_embed_callback( null, $correct_block );
		$this->assertContains( get_permalink( $amp_story_post ), $overriden_render_callback );
		$this->assertContains( get_the_post_thumbnail_url( $amp_story_post ), $overriden_render_callback );

		// This should override the callback even if the site uses HTTPS and the permalink uses HTTP.
		$_SERVER['HTTPS'] = 'on';
		$correct_block    = [
			'attrs'     => [ 'url' => set_url_scheme( $correct_url, 'http' ) ],
			'blockName' => $correct_block_name,
		];

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
			[
				'className'     => [
					'type' => 'string',
				],
				'storiesToShow' => [
					'type'    => 'number',
					'default' => 5,
				],
				'order'         => [
					'type'    => 'string',
					'default' => 'desc',
				],
				'orderBy'       => [
					'type'    => 'string',
					'default' => 'date',
				],
				'useCarousel'   => [
					'type'    => 'boolean',
					'default' => true,
				],
			],
			$latest_stories_block->attributes
		);
		$this->assertEquals( null, $latest_stories_block->editor_script );
		$this->assertEquals( null, $latest_stories_block->editor_style );
		$this->assertEquals( $block_name, $latest_stories_block->name );
		$this->assertEquals( [ 'AMP_Story_Post_Type', 'render_block_latest_stories' ], $latest_stories_block->render_callback );
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

		$attributes = [
			'storiesToShow' => 10,
			'order'         => 'desc',
			'orderBy'       => 'date',
			'useCarousel'   => true,
		];

		// Create mock AMP story posts to test.
		$minimum_height = 200;
		$dimensions     = [ [ $minimum_height, 200 ], [ 300, 400 ], [ 500, 600 ] ];
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
		$wrong_post     = self::factory()->post->create_and_get();

		// The post type is not amp_story, so this should return the same $output it's passed.
		$this->assertEquals( $initial_output, AMP_Story_Post_Type::remove_title_from_embed( $initial_output, $wrong_post ) );

		// The post type is correct, but the <blockquote> does not have the expected class, so this should again return the same $output.
		$correct_post              = self::factory()->post->create_and_get( [ 'post_type' => AMP_Story_Post_Type::POST_TYPE_SLUG ] );
		$block_quote_without_class = '<blockquote>Example Title</blockquote>';
		$output_with_blockquote    = $block_quote_without_class . $initial_output;
		$this->assertEquals( $output_with_blockquote, AMP_Story_Post_Type::remove_title_from_embed( $output_with_blockquote, $correct_post ) );

		// All of the conditions are satisfied, so this should remove the <blockquote> and the elements it contains.
		$correct_post           = self::factory()->post->create_and_get( [ 'post_type' => AMP_Story_Post_Type::POST_TYPE_SLUG ] );
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
		$non_amp_story         = self::factory()->post->create_and_get();

		// This isn't an AMP story embed, so it shouldn't change the markup.
		$this->assertEquals( $original_embed_markup, AMP_Story_Post_Type::change_embed_iframe_attributes( $original_embed_markup, $non_amp_story ) );

		// This is an AMP story embed, but the markup doesn't have an <iframe>, so it shouldn't be changed.
		$amp_story                   = self::factory()->post->create_and_get( [ 'post_type' => AMP_Story_Post_Type::POST_TYPE_SLUG ] );
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
		$stories = [];
		foreach ( $featured_images as $dimensions ) {
			$new_story = self::factory()->post->create_and_get(
				[ 'post_type' => AMP_Story_Post_Type::POST_TYPE_SLUG ]
			);
			$stories[] = $new_story;

			// Create the featured image.
			$thumbnail_id = wp_insert_attachment(
				[
					'post_mime_type' => 'image/jpeg',
				],
				'https://example.com/foo-image.jpeg',
				$new_story->ID
			);
			set_post_thumbnail( $new_story, $thumbnail_id );

			wp_update_attachment_metadata(
				$thumbnail_id,
				[
					'width'  => $dimensions[0],
					'height' => $dimensions[1],
				]
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
				return [
					'ad-attributes' => [
						'type'      => 'doubleclick',
						'data-slot' => '/30497360/a4a/amp_story_dfp_example',
					],
				];
			}
		);

		$actual = get_echo( 'amp_print_story_auto_ads' );

		$this->assertStringStartsWith( '<amp-story-auto-ads', $actual );
		$this->assertContains( '<script type="application/json">{"ad-attributes":{"type":"doubleclick"', $actual );
	}

	/**
	 * Test AMP_Story_Post_Type::filter_rest_request_for_kses().
	 *
	 * @covers AMP_Story_Post_Type::filter_rest_request_for_kses
	 */
	public function test_filter_rest_request_for_kses() {
		AMP_Story_Post_Type::register();

		$author_id = self::factory()->user->create( [ 'role' => 'author' ] );
		wp_set_current_user( $author_id );

		$story = self::factory()->post->create(
			[
				'post_type' => AMP_Story_Post_Type::POST_TYPE_SLUG,
			]
		);

		$unsanitized_content = '<!-- wp:amp/amp-story-page {"autoAdvanceAfterDuration":0,"backgroundColors":"[{\u0022color\u0022:\u0022#abb8c3\u0022},{\u0022color\u0022:null}]"} -->
<amp-story-page style="background-color:#ffffff" id="1371b14f-c7c3-4b9a-bd47-e24f2b8a1f11" class="wp-block-amp-amp-story-page"><amp-story-grid-layer template="fill" style="background-image:linear-gradient(to bottom, #abb8c3, transparent);opacity:1"></amp-story-grid-layer><!-- wp:amp/amp-story-text {"placeholder":"Write text…","tagName":"h1","customTextColor":"#ffffff","backgroundColor":"vivid-red","autoFontSize":45,"positionTop":10,"rotationAngle":-27} -->
<amp-story-grid-layer template="vertical" data-block-name="amp/amp-story-text"><div class="amp-story-block-wrapper" style="position:absolute;top:10%;left:5%;width:76.22%;height:10.85%"><h1 style="background-color:rgba(207, 46, 46, 1);color:#ffffff;display:flex;transform:rotate(-27deg)" class="wp-block-amp-amp-story-text has-text-color has-background has-vivid-red-background-color" id="ccf08639-cb18-4c65-b35d-8cf2347c700b"><amp-fit-text layout="flex-item" class="amp-text-content">Hello World</amp-fit-text></h1></div></amp-story-grid-layer>
<!-- /wp:amp/amp-story-text --></amp-story-page><!-- /wp:amp/amp-story-page -->';

		$expected = '<!-- wp:amp/amp-story-page {"autoAdvanceAfterDuration":0,"backgroundColors":"[{\u0022color\u0022:\u0022#abb8c3\u0022},{\u0022color\u0022:null}]"} -->
<amp-story-page style="background-color:#ffffff" id="1371b14f-c7c3-4b9a-bd47-e24f2b8a1f11" class="wp-block-amp-amp-story-page"><amp-story-grid-layer template="fill" style="background-image:linear-gradient(to bottom, #abb8c3, transparent);opacity:1"></amp-story-grid-layer><!-- wp:amp/amp-story-text {"placeholder":"Write text…","tagName":"h1","customTextColor":"#ffffff","backgroundColor":"vivid-red","autoFontSize":45,"positionTop":10,"rotationAngle":-27} -->
<amp-story-grid-layer template="vertical" data-block-name="amp/amp-story-text"><div class="amp-story-block-wrapper" style="position:absolute;top:10%;left:5%;width:76.22%;height:10.85%"><h1 style="background-color:rgba(207, 46, 46, 1);color:#ffffff;display:flex;transform:rotate(-27deg)" class="wp-block-amp-amp-story-text has-text-color has-background has-vivid-red-background-color" id="ccf08639-cb18-4c65-b35d-8cf2347c700b"><amp-fit-text layout="flex-item" class="amp-text-content">Hello World</amp-fit-text></h1></div></amp-story-grid-layer>
<!-- /wp:amp/amp-story-text --></amp-story-page><!-- /wp:amp/amp-story-page -->';

		$request = new WP_REST_Request( 'PUT', sprintf( '/wp/v2/%s/%d', AMP_Story_Post_Type::POST_TYPE_SLUG, $story ) );
		$request->add_header( 'content-type', 'application/x-www-form-urlencoded' );
		$request->set_body_params(
			[
				'content' => $unsanitized_content,
			]
		);

		$response = rest_get_server()->dispatch( $request );
		$new_data = $response->get_data();

		$this->assertEquals( $expected, $new_data['content']['raw'] );
	}

	/**
	 * Test AMP_Story_Post_Type::render_block_with_grid_layer().
	 *
	 * @covers AMP_Story_Post_Type::render_block_with_grid_layer
	 */
	public function test_render_block_with_grid_layer() {
		$GLOBALS['post'] = self::factory()->post->create_and_get(
			[
				'post_type' => AMP_Story_Post_Type::POST_TYPE_SLUG,
			]
		);

		$block_content = '<p>Some block.</p>';
		$block         = [
			'attrs'     => [
				'positionTop'       => 0,
				'positionLeft'      => 15,
				'width'             => 328,
				'height'            => 553,
				'ampAnimationType'  => 'pulse',
				'ampAnimationDelay' => '500',
				'ampAnimationAfter' => 123,
				'someRandomAtt'     => 'random',
			],
			'blockName' => 'amp/amp-story-text',
		];
		$expected      = '<amp-story-grid-layer template="vertical"><div class="amp-story-block-wrapper" style="position:absolute;top:0%;left:15%;width:100.00%;height:100.00%;" animate-in="pulse" animate-in-delay="500" animate-in-after="123"><p>Some block.</p></div></amp-story-grid-layer>';

		$filtered_block = AMP_Story_Post_Type::render_block_with_grid_layer( $block_content, $block );
		$this->assertEquals( $expected, $filtered_block );
	}

	/**
	 * Test AMP_Story_Post_Type::render_block_with_grid_layer() when wrapper already exists.
	 *
	 * @covers AMP_Story_Post_Type::render_block_with_grid_layer
	 */
	public function test_render_block_with_grid_layer_with_existing_wrappers() {
		$GLOBALS['post'] = self::factory()->post->create_and_get(
			[
				'post_type' => AMP_Story_Post_Type::POST_TYPE_SLUG,
			]
		);

		$block_content = '<amp-story-grid-layer><p>Some block.</p></amp-story-grid-layer>';
		$block         = [
			'attrs'     => [
				'positionTop'  => 0,
				'positionLeft' => 15,
			],
			'blockName' => 'amp/amp-story-text',
		];
		$expected      = '<amp-story-grid-layer><p>Some block.</p></amp-story-grid-layer>';

		$filtered_block = AMP_Story_Post_Type::render_block_with_grid_layer( $block_content, $block );
		$this->assertEquals( $expected, $filtered_block );
	}

	/**
	 * Test AMP_Story_Post_Type::render_block_with_grid_layer() with not movable block type.
	 *
	 * @covers AMP_Story_Post_Type::render_block_with_grid_layer
	 */
	public function test_render_block_with_grid_layer_with_not_movable_block() {
		$GLOBALS['post'] = self::factory()->post->create_and_get(
			[
				'post_type' => AMP_Story_Post_Type::POST_TYPE_SLUG,
			]
		);

		$block_content = '<p>Some block.</p>';
		$block         = [
			'attrs'     => [
				'positionTop'  => 0,
				'positionLeft' => 15,
			],
			'blockName' => 'amp/amp-story-cta',
		];
		$expected      = '<p>Some block.</p>';

		$filtered_block = AMP_Story_Post_Type::render_block_with_grid_layer( $block_content, $block );
		$this->assertEquals( $expected, $filtered_block );
	}
}
