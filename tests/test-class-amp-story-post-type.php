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
	 * Reset the permalink structure to the state before the tests.
	 *
	 * @global WP_Rewrite $wp_rewrite
	 */
	public function tearDown() {
		global $wp_rewrite;

		$wp_rewrite->set_permalink_structure( false );
		parent::tearDown();
	}

	/**
	 * Test the_single_story_card.
	 *
	 * @covers AMP_Story_Post_Type::the_single_story_card()
	 */
	public function test_the_single_story_card() {
		$number_of_stories = 10;
		$stories           = $this->create_story_posts( $number_of_stories );

		foreach ( $stories as $story ) {
			ob_start();
			AMP_Story_Post_Type::the_single_story_card( $story );
			$card_markup    = ob_get_clean();
			$featured_image = get_post_thumbnail_id( $story );
			$this->assertContains( get_the_permalink( $story->ID ), $card_markup );
			$this->assertContains(
				wp_get_attachment_image(
					$featured_image,
					AMP_Story_Post_Type::STORY_CARD_IMAGE_SIZE,
					false,
					array(
						'alt'   => get_the_title( $story ),
						'class' => 'latest-stories__featured-img',
					)
				),
				$card_markup
			);
		}
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
		if ( ! function_exists( 'register_block_type' ) ) {
			$this->markTestSkipped( 'The function register_block_type() is not present, so the AMP Story post type was not registered.' );
		}

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

		if ( ! function_exists( 'register_block_type' ) ) {
			$this->markTestSkipped( 'The function register_block_type() is not present, so the AMP Story post type was not registered.' );
		}

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
		$story_posts    = $this->create_story_posts( 1 );
		$amp_story_post = reset( $story_posts );
		$correct_url    = get_post_permalink( $amp_story_post );
		$correct_block  = array(
			'attrs'     => array( 'url' => $correct_url ),
			'blockName' => $correct_block_name,
		);

		$overriden_render_callback = AMP_Story_Post_Type::override_story_embed_callback( null, $correct_block );
		$this->assertContains( get_permalink( $amp_story_post ), $overriden_render_callback );
		$this->assertContains( get_the_post_thumbnail_url( $amp_story_post ), $overriden_render_callback );
	}

	/**
	 * Creates a given number of amp_story posts.
	 *
	 * @param int $number_of_stories An array of strings.
	 * @return array $stories The created AMP story posts.
	 */
	public function create_story_posts( $number_of_stories ) {
		$stories = array();
		for ( $i = 0; $i < $number_of_stories; $i++ ) {
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
		}

		return $stories;
	}
}
