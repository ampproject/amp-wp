<?php $categories = get_the_category_list( _x( ', ', 'Used between list items, there is a space after the comma.', 'amp' ) ); ?>
<?php if ( $categories ) : ?>
	<div class="amp-wp-meta amp-wp-tax-category" itemprop="about">
		Categories:
		<?php echo $categories; ?>
	</div>
<?php endif; ?>

<?php $tags = get_the_tag_list( '', _x( ', ', 'Used between list items, there is a space after the comma.', 'amp' ) ); ?>
<?php if ( $tags && ! is_wp_error( $tags ) ) : ?>
	<div class="amp-wp-meta amp-wp-tax-tag" itemprop="keywords">
		Tags:
		<?php echo $tags; ?>
	</div>
<?php endif; ?>
