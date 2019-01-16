<?php
/**
 * Legacy template for the AMP author byline.
 *
 * @package AMP
 */

$post_author = $this->get( 'post_author' );
$avatar_url  = get_avatar_url(
	$post_author->user_email,
	array(
		'size' => 24,
	)
);
?>
<li class="amp-wp-byline">
	<?php if ( function_exists( 'get_avatar_url' ) ) : ?>
	<amp-img src="<?php echo esc_url( $avatar_url ); ?>" width="24" height="24" layout="fixed"></amp-img>
	<?php endif; ?>
	<span class="amp-wp-author"><?php echo esc_html( $post_author->display_name ); ?></span>
</li>
