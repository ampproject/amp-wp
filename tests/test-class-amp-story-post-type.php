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
