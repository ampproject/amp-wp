<?php

// Get image
$post_ID =  get_the_ID();
$feat_image = wp_get_attachment_url( get_post_thumbnail_id( $post_ID ) );
$feat_image_meta = wp_get_attachment_metadata( get_post_thumbnail_id( $post_ID ) );

// Skip featured image if no featured image is available.
if ( ! $feat_image ) {
	return;
}

// Get image caption
$image = get_post( get_post_thumbnail_id( $post_ID ) );
$image_caption = $image->post_excerpt;
$image_title = $image->post_title;

// Get image aspect ratio
$full_height = $feat_image_meta['height'];
$full_width = $feat_image_meta['width'];

// Get large image dimensions for srcset
$large_width = $feat_image_meta['sizes']['large']['width'];
$large_feat_image = wp_get_attachment_image_src( get_post_thumbnail_id( $post_ID ), 'large' );
$large_feat_image_srcset = $large_feat_image[0] . ' ' . $large_width . 'w,';

// Get medium image dimensions for srcset
$medium_width = $feat_image_meta['sizes']['medium']['width'];
$medium_feat_image = wp_get_attachment_image_src( get_post_thumbnail_id( $post_ID ), 'medium' );
$medium_feat_image_srcset = $medium_feat_image[0] . ' ' . $medium_width . 'w';

// Compose srcset
if ( $large_width && $medium_width ) {

	$srcset = ' srcset="';

	if ( $large_width ) {
		$srcset .= $large_feat_image_srcset;
	}

	if ( $medium_width ) {
		$srcset .= $medium_feat_image_srcset;
	}

	$srcset .= '" ';
}

// Regex to find the featured image by ID
$regex1 = sprintf( '#<amp-img.+class=("|"[^"]+ )wp-image-%d("| [^"]+").+>(\s*<.+>)*[^ ]?</amp-img>#im', get_post_thumbnail_id( $post_ID ) );
$regex2 = sprintf( '#<figure.+id=("|"[^"]+ )attachment_%d("| [^"]+").+>(\s*<.+>)*[^ ]?</figure>#im', get_post_thumbnail_id( $post_ID ) );

// Get the post content
$content = $this->get( 'post_amp_content' ); // amphtml content; no kses

/*
 * If a featured image exists and an image with the same
 * ID exists in the_content(), skip the featured image markup
 * - Prevents duplicate images
 */
if ( false == preg_match( $regex1, $content ) || false == preg_match( $regex2, $content ) ) { ?>

<figure class="amp-wp-article-featured-image wp-caption">
	<amp-img alt="<?php esc_attr_e( $image_title ); ?>" src="<?php the_post_thumbnail_url(); ?>" <?php echo $srcset; ?> height="<?php esc_attr_e( $full_height, 'amp' ); ?>" width="<?php esc_attr_e( $full_width, 'amp' ); ?>" layout="responsive" itemprop="image"></amp-img>
	<?php if ( $image_caption ) { ?>
		<p class="wp-caption-text"><?php echo $image_caption; ?></p>
	<?php } ?>
</figure>

<?php }