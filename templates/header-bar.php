<nav class="amp-wp-title-bar">
	<div>
		<a href="<?php echo esc_url( $this->get( 'home_url' ) ); ?>">
			<?php $site_icon_url = $this->get( 'site_icon_url' ); ?>
			<?php $classes = $site_icon_url ? 'amp-wp-site-icon' : 'amp-wp-site-icon amp-wp-hidden'; ?>
			<amp-img src="<?php echo esc_url( $site_icon_url ); ?>" width="32" height="32" class="<?php echo esc_attr( $classes ); ?>"></amp-img>

			<?php echo esc_html( $this->get( 'blog_name' ) ); ?>
		</a>
	</div>
</nav>
