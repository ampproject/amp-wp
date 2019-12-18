<?php
/**
 * Template for amp_story post type.
 *
 * @package AMP
 */

the_post();

?>
<!DOCTYPE html>
<html amp <?php language_attributes(); ?>>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">

	<?php
	/**
	 * Prints scripts or data in the head tag on the front end.
	 *
	 * @since 1.3
	 */
	do_action( 'amp_story_head' );
	?>
</head>
<body>
<?php
$metadata = amp_get_schemaorg_metadata();
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
	publisher-logo-src="<?php echo esc_url( $publisher_logo_src ); ?>"
	publisher="<?php echo esc_attr( $publisher ); ?>"
	title="<?php the_title_attribute(); ?>"
	poster-portrait-src="<?php echo esc_url( $meta_images['poster-portrait'] ); ?>"
	<?php if ( isset( $meta_images['poster-square'] ) ) : ?>
		poster-square-src="<?php echo esc_url( $meta_images['poster-square'] ); ?>"
	<?php endif; ?>
>
	<?php
	amp_print_story_auto_ads();
	the_content();
	amp_print_analytics( '' );
	?>
</amp-story>
</body>
</html>
