<!doctype html>
<html amp <?php language_attributes(); ?>>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no">
	<?php do_action( 'amp_post_template_head', $this ); ?>
	<script async custom-element="amp-social-share" src="https://cdn.ampproject.org/v0/amp-social-share-0.1.js"></script>
	<style amp-custom>
		<?php $this->load_parts( array( 'style' ) ); ?>
		<?php do_action( 'amp_post_template_css', $this ); ?>
	</style>
</head>

<body itemscope="itemscope" itemtype="http://schema.org/WebPage">

<?php $this->load_parts( array( 'header' ) ); ?>

<article class="amp-wp-article" itemscope="itemscope" itemtype="http://schema.org/BlogPosting" itemprop="blogPost">

	<header class="amp-wp-article-header">
		<h1 class="amp-wp-title" itemprop="headline"><?php echo wp_kses_data( $this->get( 'post_title' ) ); ?></h1>
		<?php $this->load_parts( array( 'meta-author' ) ); ?>
		<?php $this->load_parts( array( 'meta-time' ) ); ?>
	</header>

	<?php $this->load_parts( array( 'featured-image' ) ); ?>

	<div class="amp-wp-article-content" itemprop="articleBody">
		<?php // Get the post content
			$content = $this->get( 'post_amp_content' ); // amphtml content; no kses
			echo $content; ?>
	</div>

	<footer class="amp-wp-article-footer">
		<?php $this->load_parts( array( 'meta-sharing' ) ); ?>
		<?php $this->load_parts( array( 'meta-taxonomy' ) ); ?>
		<?php $this->load_parts( array( 'meta-comments-link' ) ); ?>
	</footer>

</article>

<?php $this->load_parts( array( 'footer-wordads' ) ); ?>

<?php $this->load_parts( array( 'footer' ) ); ?>

<?php do_action( 'amp_post_template_footer', $this ); ?>

</body>
</html>