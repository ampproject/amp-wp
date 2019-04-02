<?php
/**
 * Single view template.
 *
 * @package AMP
 */

/**
 * Context.
 *
 * @var AMP_Post_Template $this
 */

$this->load_parts( array( 'html-start' ) );
?>

<?php $this->load_parts( array( 'header' ) ); ?>

<article class="amp-wp-article">
	<header class="amp-wp-article-header">
		<h1 class="amp-wp-title"><?php echo esc_html( $this->get( 'post_title' ) ); ?></h1>
		<?php $this->load_parts( apply_filters( 'amp_post_article_header_meta', array( 'meta-author', 'meta-time' ) ) ); ?>
	</header>

	<?php $this->load_parts( array( 'featured-image' ) ); ?>

	<div class="amp-wp-article-content">
		<?php echo $this->get( 'post_amp_content' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</div>

	<footer class="amp-wp-article-footer">
		<?php $this->load_parts( apply_filters( 'amp_post_article_footer_meta', array( 'meta-taxonomy', 'meta-comments-link' ) ) ); ?>
	</footer>
</article>

<?php $this->load_parts( array( 'footer' ) ); ?>

<?php
$this->load_parts( array( 'html-end' ) );
