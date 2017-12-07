<?php
/**
 * Footer template.
 *
 * @package AMP
 */

/**
 * Context.
 *
 * @var AMP_Post_Template $this
 */
?>
<footer class="amp-wp-footer">
	<div>
		<h2><?php echo esc_html( $this->get( 'blog_name' ) ); ?></h2>
		<p>
			<a href="<?php echo esc_url( esc_html__( 'https://wordpress.org/', 'amp' ) ); ?>">
				<?php
				// translators: %1$s is WordPress.
				echo esc_html( sprintf( __( 'Powered by %s', 'amp' ), 'WordPress' ) );
				?>
			</a>
		</p>
		<a href="#top" class="back-to-top"><?php esc_html_e( 'Back to top', 'amp' ); ?></a>
	</div>
</footer>

<?php do_action( 'amp_post_template_footer', $this ); ?>

</body>
</html>
