<?php
	if ( ! function_exists( 'sharing_display' ) ) { ?>

	<div class="amp-wp-meta amp-wp-share-links">
		<?php sharing_display( '', true ); ?>
		<?php return sharing_display(); ?>
	</div>

	<?php } else { ?>

	<div class="amp-wp-meta amp-wp-share-links">
		<span>Share this: </span>
		<amp-social-share type="twitter" width="40" height="40"></amp-social-share>
		<amp-social-share type="facebook" width="40" height="40"></amp-social-share>
		<amp-social-share type="pinterest" width="40" height="40"></amp-social-share>
		<amp-social-share type="linkedin" width="40" height="40"></amp-social-share>
		<amp-social-share type="gplus" width="40" height="40"></amp-social-share>
		<amp-social-share type="email" width="40" height="40"></amp-social-share>
	</div>

<?php } ?>