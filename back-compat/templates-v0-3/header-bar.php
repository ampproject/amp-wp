<?php
/**
 * Legacy template for the AMP title bar.
 *
 * @package AMP
 */

$site_icon_url      = $this->get( 'site_icon_url' );
$canonical_link_url = $this->get( 'post_canonical_link_url' );
?>

<nav class="amp-wp-title-bar">
	<div>
		<a href="<?php echo esc_url( $this->get( 'home_url' ) ); ?>">
			<?php if ( $site_icon_url ) : ?>
				<amp-img src="<?php echo esc_url( $site_icon_url ); ?>" width="32" height="32" class="amp-wp-site-icon"></amp-img>
			<?php endif; ?>

			<?php echo esc_html( $this->get( 'blog_name' ) ); ?>
		</a>
		<?php if ( $canonical_link_url ) : ?>
			<?php $canonical_link_text = $this->get( 'post_canonical_link_text' ); ?>
			<a class="amp-wp-canonical-link" href="<?php echo esc_url( $canonical_link_url ); ?>">
				<?php echo esc_html( $canonical_link_text ); ?>
			</a>
		<?php endif; ?>
	</div>
</nav>
