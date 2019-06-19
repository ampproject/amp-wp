<?php
/**
 * Template for the amp_story embed.
 * Applies when embedding an AMP story, like by entering its URL in the WordPress (embed) block.
 * This is mainly taken from wp-includes/theme-compat/embed.php.
 *
 * @package AMP
 */

get_header( 'embed' );

if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();
		?>
		<div class="amp-story-embed">
			<?php
			AMP_Story_Post_Type::the_single_story_card(
				array(
					'post' => get_post(),
					'size' => AMP_Story_Post_Type::STORY_CARD_IMAGE_SIZE,
				)
			);
			?>
		</div>
		<?php
	}
} else {
	get_template_part( 'embed', '404' );
}

get_footer( 'embed' );
