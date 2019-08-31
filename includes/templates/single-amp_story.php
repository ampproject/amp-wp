<?php
/**
 * Template for amp_story post type.
 *
 * @package AMP
 */

the_post();

$metadata = amp_get_schemaorg_metadata();
?>
<!DOCTYPE html>
<html amp <?php language_attributes(); ?>>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">
		<title><?php echo esc_html( wp_get_document_title() ); ?></title>
		<?php
		wp_enqueue_scripts();
		wp_scripts()->do_items( [ 'amp-runtime' ] ); // @todo Duplicate with AMP_Theme_Support::enqueue_assets().
		wp_styles()->do_items();
		?>
		<?php rel_canonical(); ?>
		<?php amp_add_generator_metadata(); ?>
		<script type="application/ld+json"><?php echo wp_json_encode( $metadata, JSON_UNESCAPED_UNICODE ); ?></script>
		<style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style><noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>
	</head>
	<body>
		<?php
		if ( isset( $metadata['publisher']['logo']['url'] ) ) {
			$publisher_logo_src = $metadata['publisher']['logo']['url'];
		} elseif ( isset( $metadata['publisher']['logo'] ) && is_string( $metadata['publisher']['logo'] ) ) {
			$publisher_logo_src = $metadata['publisher']['logo'];
		} else {
			$publisher_logo_src = admin_url( 'images/wordpress-logo.png' );
		}
		$publisher = isset( $metadata['publisher']['name'] ) ? $metadata['publisher']['name'] : get_option( 'blogname' );

		$meta_images = AMP_Story_Media::get_story_meta_images();
		?>
		<amp-story
			standalone
			<?php
			/**
			 * Filters whether the story supports landscape.
			 *
			 * @param bool    $supports_landscape Whether supports landscape. Currently false by default, but this will change in the future (e.g. via user toggle).
			 * @param wp_Post $post               The current amp_story post object.
			 */
			if ( apply_filters( 'amp_story_supports_landscape', false, get_post() ) ) {
				echo 'supports-landscape';
			}
			?>
			publisher-logo-src="<?php echo esc_url( $publisher_logo_src ); ?>"
			publisher="<?php echo esc_attr( $publisher ); ?>"
			title="<?php the_title_attribute(); ?>"
			poster-portrait-src="<?php echo esc_url( $meta_images['poster-portrait'] ); ?>"
			<?php if ( isset( $meta_images['poster-square'] ) ) : ?>
				poster-square-src="<?php echo esc_url( $meta_images['poster-square'] ); ?>"
			<?php endif; ?>
			<?php if ( isset( $meta_images['poster-landscape'] ) ) : ?>
				poster-landscape-src="<?php echo esc_url( $meta_images['poster-landscape'] ); ?>"
			<?php endif; ?>
		>
			<?php
			amp_print_story_auto_ads();
			the_content();
			amp_print_analytics( '' );
			?>
			
            <amp-story-bookend layout=nodisplay>
                <script type="application/json">
            {
                "bookendVersion": "v1.0",
                "shareProviders": [
                    "linkedin",
                    "whatsapp",
                    "Twitter"
                ],
                "components": [
                    {
                        "type": "heading",
                        "text": "Up Next"
                    }
<?php
					// to loop post in bookend
					$loop_length=3;  //specify number of posts ahead you want to display
					$check_help=0;    //require as a checkpoint to follow different paths in loop
					global $post;
					$revert_post_content=$post;  //save current global post content to setback changes to current post after loop execution ends
					for($loop_start = 1; $loop_start <= $loop_length; $loop_start++)
					{
						if( $loop_start === 1)
						{
							$nextPost = get_next_post();
						}
						else
						{
							global $post;
							$post = get_next_post();
							setup_postdata( $post );

							if($check_help===1)
							{
								$nextPost = $post;
								$check_help=2;
							}
							else
							{
								$nextPost = get_next_post();
								$check_help=0;
							}
						}
						if(get_permalink($nextPost) != get_permalink($post))   // to check when last post arrives to loop back to first posts
						{
						}
						else
						{
							// getting the link of oldest story (1st story)
							$args = array(
								'numberposts'      => 2,
								'category'         => 0,
								'orderby'          => 'date',
								'order'            => 'ASC', // the 1st array element will be 1st story(oldest story)
								'include'          => array(),
								'exclude'          => array(),
								'meta_key'         => '',
								'meta_value'       => '',
								'post_type'        => 'amp_story',
								'suppress_filters' => true,
							);
							$get_post_for_story=get_posts($args);
							$first_story=$get_post_for_story[0]; // 0 will give the 1st  story here ( oldest story)

							global $post;
							$post = $first_story;
							setup_postdata( $post );
							if($check_help!=0 )
							{
								$nextPost = get_next_post();
							}
							else
							{
								$nextPost = $post;
								$check_help=1;
							}

						}


						// to stop looping of stories
						if(get_permalink($revert_post_content)!== get_permalink($nextPost))
						{
							echo ',{';
							if ( $loop_start === 1 ) {
								echo ' "type": "landscape", ';
							} else {
								echo ' "type": "small", ';
							}
							echo '"title": "';
							echo $nextPost->post_title;
							echo '",';
							echo '"url": "';
							echo get_permalink( $nextPost );
							echo '",';
							echo '"image": "';
							echo get_the_post_thumbnail_url( $nextPost->ID );
							echo '"';
							echo '}';
						}
						else{
							$loop_start=$loop_length+2;
						}
						if( $loop_start >= $loop_length)
						{ //wp_reset_postdata();
							$post=$revert_post_content;}
					}
					?>
  ]
      }
     </script>
            </amp-story-bookend>
		</amp-story>

		<?php
		// Note that \AMP_Story_Post_Type::filter_frontend_print_styles_array() will limit which styles are printed.
		print_late_styles();
		?>
	</body>
</html>
