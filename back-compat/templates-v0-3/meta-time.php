<?php
/**
 * Legacy template for the AMP post date.
 *
 * @package AMP
 */

/**
 * Context.
 *
 * @var AMP_Post_Template $this
 */

?>
<li class="amp-wp-posted-on">
	<time datetime="<?php echo esc_attr( gmdate( 'c', $this->get( 'post_publish_timestamp' ) ) ); ?>">
		<?php
		echo esc_html(
			sprintf(
				/* translators: %s: the human-readable time difference. */
				__( '%s ago', 'amp' ),
				human_time_diff( $this->get( 'post_publish_timestamp' ), time() )
			)
		);
		?>
	</time>
</li>
