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
		<title><?php echo esc_html( wp_get_document_title() ); ?></title>
		<?php
		wp_enqueue_scripts();
		wp_scripts()->do_items( array( 'amp-runtime' ) ); // @todo Duplicate with AMP_Theme_Support::enqueue_assets().
		wp_styles()->do_items( array( 'wp-block-library' ) ); // @todo We need to allow a theme to enqueue their own AMP story styles.
		?>
		<?php rel_canonical(); ?>
		<style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style><noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>
	</head>
	<body>
		<?php
		$metadata = amp_get_schemaorg_metadata();
		if ( isset( $metadata['publisher']['logo']['url'] ) ) {
			$publisher_logo_src = $metadata['publisher']['logo']['url']; // @todo Use amp-publisher-logo.
		} else {
			$publisher_logo_src = admin_url( 'images/wordpress-logo.png' );
		}
		$publisher = isset( $metadata['publisher']['name'] ) ? $metadata['publisher']['name'] : get_option( 'blogname' );

		// @todo poster-portrait-src can't be empty.
		$poster_portrait_src = null;
		if ( has_post_thumbnail() ) {
			$src = wp_get_attachment_image_src( get_post_thumbnail_id(), 'amp-story-poster-portrait' );
			if ( $src ) {
				$poster_portrait_src = $src[0];
			}
		}

		?>
		<amp-story
			standalone
			publisher-logo-src="<?php echo esc_url( $publisher_logo_src ); ?>"
			publisher="<?php echo esc_attr( $publisher ); ?>"
			title="<?php the_title_attribute(); ?>"
			<?php if ( ! empty( $poster_portrait_src ) ) : ?>
				poster-portrait-src="<?php echo esc_url( $poster_portrait_src ); ?>"
			<?php endif; ?>
		>
			<?php the_content(); ?>
		</amp-story>
	</body>
</html>
