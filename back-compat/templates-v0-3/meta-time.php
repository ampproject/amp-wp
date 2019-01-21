<?php
/**
 * Legacy template for the AMP post date.
 *
 * @package AMP
 */

?>
<li class="amp-wp-posted-on">
	<time datetime="<?php echo esc_attr( date( 'c', $this->get( 'post_publish_timestamp' ) ) ); ?>">
		<?php
		echo esc_html(
			sprintf(
				/* translators: %s: the human-readable time difference. */
				__( '%s ago', 'amp' ),
				human_time_diff( $this->get( 'post_publish_timestamp' ), current_time( 'timestamp' ) )
			)
		);
		?>
	</time>
</li>
