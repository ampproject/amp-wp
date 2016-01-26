<?php $post_author = $this->get( 'post_author' ); ?>
<li class="byline">
	<amp-img src="<?php echo esc_url( get_avatar_url( $post_author->user_email, array(
		'size' => 24,
	) ) ); ?>" width="24" height="24" layout="fixed"></amp-img>
	<span class="author"><?php echo esc_html( $post_author->display_name ); ?></span>
</li>
