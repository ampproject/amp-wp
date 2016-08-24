<?php
	if ( ! function_exists( 'sharing_display' ) ) { ?>

	<div class="amp-wp-meta amp-wp-share-links">
		<?php sharing_display( '', true ); ?>
		<?php return sharing_display(); ?>
	</div>

	<?php } else { ?>

	<div class="amp-wp-meta amp-wp-share-links">
		Share this:
		<amp-social-share type="twitter" width="40" height="40"></amp-social-share>
		<amp-social-share type="facebook" width="40" height="40"></amp-social-share>
		<amp-social-share type="pinterest" width="40" height="40"></amp-social-share>
		<amp-social-share type="linkedin" width="40" height="40"></amp-social-share>
		<amp-social-share type="gplus" width="40" height="40"></amp-social-share>
		<amp-social-share type="email" width="40" height="40"></amp-social-share>

	<?php /*
		<a href="http://facebook.com"><span class="screen-reader-text">Facebook</span></a>
		<a href="http://youtube.com"><span class="screen-reader-text">YouTube</span></a>
		<a href="http://pinterest.com"><span class="screen-reader-text">Pinterest</span></a>
		<a href="http://vimeo.com"><span class="screen-reader-text">Vimeo</span></a>
	*/ ?>
	</div>

<?php } ?>