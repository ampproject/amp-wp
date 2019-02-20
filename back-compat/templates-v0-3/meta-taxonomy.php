<?php
/**
 * Legacy template for the AMP post taxonomy term lists.
 *
 * @package AMP
 */

$categories = get_the_category_list( _x( ', ', 'Used between list items, there is a space after the comma.', 'amp' ) );
?>
<?php if ( $categories ) : ?>
	<li class="amp-wp-tax-category">
		<span class="screen-reader-text">Categories:</span>
		<?php echo $categories; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</li>
<?php endif; ?>

<?php $tags = get_the_tag_list( '', _x( ', ', 'Used between list items, there is a space after the comma.', 'amp' ) ); ?>
<?php if ( $tags && ! is_wp_error( $tags ) ) : ?>
	<li class="amp-wp-tax-tag">
		<span class="screen-reader-text">Tags:</span>
		<?php echo $tags; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</li>
<?php endif; ?>
