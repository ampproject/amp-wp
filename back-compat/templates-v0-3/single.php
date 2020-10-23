<?php
/**
 * Legacy template for the AMP post.
 *
 * @package AMP
 */

/**
 * Context.
 *
 * @var AMP_Post_Template $this
 */

?>
<!doctype html>
<html amp <?php language_attributes(); ?>>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1">
	<?php
	/** This action is documented in templates/html-start.php */
	do_action( 'amp_post_template_head', $this );
	?>

	<style amp-custom>
	<?php $this->load_parts( [ 'style' ] ); ?>
	<?php
	/** This action is documented in templates/html-start.php */
	do_action( 'amp_post_template_css', $this );
	?>
	</style>
</head>
<body>
<?php $this->load_parts( [ 'header-bar' ] ); ?>
<div class="amp-wp-content">
	<h1 class="amp-wp-title"><?php echo wp_kses_data( $this->get( 'post_title' ) ); ?></h1>
	<ul class="amp-wp-meta">
		<?php
		/**
		 * Filters the template parts to load in the meta area of the AMP legacy post template from before v0.4.
		 *
		 * @since 0.2
		 * @deprecated
		 *
		 * @param string[] Templates to load.
		 */
		$this->load_parts( apply_filters( 'amp_post_template_meta_parts', [ 'meta-author', 'meta-time', 'meta-taxonomy' ] ) );
		?>
	</ul>
	<?php echo $this->get( 'post_amp_content' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</div>
<?php
/** This action is documented in templates/html-start.php */
do_action( 'amp_post_template_footer', $this );
?>
</body>
</html>
