<!doctype html>
<html amp>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no">
	<?php do_action( 'amp_post_template_head', $this ); ?>

	<style amp-custom>
	<?php $this->load_parts( array( 'style' ) ); ?>
	<?php do_action( 'amp_post_template_css', $this ); ?>
	</style>
</head>
<body>
<nav class="amp-wp-title-bar">
	<div>
		<a href="<?php echo esc_url( $this->get( 'home_url' ) ); ?>">
			<?php $site_icon_url = $this->get( 'site_icon_url' ); ?>
			<?php if ( $site_icon_url ) : ?>
				<amp-img src="<?php echo esc_url( $site_icon_url ); ?>" width="32" height="32" class="amp-wp-site-icon"></amp-img>
			<?php endif; ?>
			<?php echo esc_html( $this->get( 'blog_name' ) ); ?>
		</a>
	</div>
</nav>
<div class="amp-wp-content">
	<h1 class="amp-wp-title"><?php echo wp_kses_data( $this->get( 'post_title' ) ); ?></h1>
	<ul class="amp-wp-meta">
		<?php $this->load_parts( apply_filters( 'amp_post_template_meta_parts', array( 'meta-author', 'meta-time', 'meta-taxonomy' ) ) ); ?>
	</ul>
	<?php echo $this->get( 'post_amp_content' ); // amphtml content; no kses ?>
</div>
<?php do_action( 'amp_post_template_footer', $this ); ?>
</body>
</html>
