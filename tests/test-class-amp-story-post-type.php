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
		AMP_Story_Post_Type::enqueue_embed_styling();
		// None of the conditional is satisfied, so this should not enqueue the stylesheet.
		$this->assertFalse( wp_style_is( AMP_Story_Post_Type::STORY_CARD_CSS_SLUG ) );

		// Only the first part of the conditional is satisfied, so this again should not enqueue the stylesheet.
		$GLOBALS['wp_query']->is_embed = true;
		AMP_Story_Post_Type::enqueue_embed_styling();
		$this->assertFalse( wp_style_is( AMP_Story_Post_Type::STORY_CARD_CSS_SLUG ) );

		$GLOBALS['wp_query']->is_embed       = true;
		$GLOBALS['wp_query']->is_singular    = true;
		$GLOBALS['wp_query']->queried_object = $this->factory()->post->create_and_get( array( 'post_type' => AMP_Story_Post_Type::POST_TYPE_SLUG ) );
		AMP_Story_Post_Type::enqueue_embed_styling();
		$this->assertTrue( wp_style_is( AMP_Story_Post_Type::STORY_CARD_CSS_SLUG ) );

		$stylesheet = wp_styles()->registered[ AMP_Story_Post_Type::STORY_CARD_CSS_SLUG ];
		$this->assertEquals( AMP_Story_Post_Type::STORY_CARD_CSS_SLUG, $stylesheet->handle );
		$this->assertEquals( 'all', $stylesheet->args );
		$this->assertEquals( array(), $stylesheet->deps );
		$this->assertContains( '.amp-story-embed', $stylesheet->extra['after'][0] );
		$this->assertContains( AMP_Story_Post_Type::STORY_CARD_CSS_SLUG, $stylesheet->src );
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
