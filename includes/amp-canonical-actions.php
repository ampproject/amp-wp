<?php

require_once( AMP__DIR__ . '/includes/utils/class-amp-dom-utils.php' );
require_once( AMP__DIR__ . '/includes/utils/class-amp-html-utils.php' );
require_once( AMP__DIR__ . '/includes/utils/class-amp-string-utils.php' );

require_once( AMP__DIR__ . '/includes/class-amp-content.php' );

require_once( AMP__DIR__ . '/includes/sanitizers/class-amp-blacklist-sanitizer.php' );
require_once( AMP__DIR__ . '/includes/sanitizers/class-amp-img-sanitizer.php' );
require_once( AMP__DIR__ . '/includes/sanitizers/class-amp-video-sanitizer.php' );
require_once( AMP__DIR__ . '/includes/sanitizers/class-amp-iframe-sanitizer.php' );
require_once( AMP__DIR__ . '/includes/sanitizers/class-amp-audio-sanitizer.php' );

require_once( AMP__DIR__ . '/includes/embeds/class-amp-twitter-embed.php' );
require_once( AMP__DIR__ . '/includes/embeds/class-amp-youtube-embed.php' );
require_once( AMP__DIR__ . '/includes/embeds/class-amp-gallery-embed.php' );
require_once( AMP__DIR__ . '/includes/embeds/class-amp-instagram-embed.php' );
require_once( AMP__DIR__ . '/includes/embeds/class-amp-vine-embed.php' );
require_once( AMP__DIR__ . '/includes/embeds/class-amp-facebook-embed.php' );

function build_post_content() {

	$post = get_post();

	$content_max_width = 1200;
	if ( isset( $GLOBALS['content_width'] ) && $GLOBALS['content_width'] > 0 ) {
		$content_max_width = $GLOBALS['content_width'];
	}

	$amp_content = new AMP_Content( $post->post_content,
		array(
			'AMP_Twitter_Embed_Handler' => array(),
			'AMP_YouTube_Embed_Handler' => array(),
			'AMP_Instagram_Embed_Handler' => array(),
			'AMP_Vine_Embed_Handler' => array(),
			'AMP_Facebook_Embed_Handler' => array(),
			'AMP_Gallery_Embed_Handler' => array(),
		),
		array(
			 'AMP_Blacklist_Sanitizer' => array(),
			 'AMP_Img_Sanitizer' => array(),
			 'AMP_Video_Sanitizer' => array(),
			 'AMP_Audio_Sanitizer' => array(),
			 'AMP_Iframe_Sanitizer' => array(
				 'add_placeholder' => true,
			 ),
		),
		array(
			'content_max_width' => $content_max_width,
		)
	);

	return $amp_content;

}

// Generate the AMP post content early on (runs the_content filters but skips our filter below)
$GLOBALS['amp_content'] = build_post_content();

// "the_content" filter was already invoked now, so attempt remove all filters
remove_all_filters('the_content');

// Callbacks for adding AMP-related things to the main theme when used as canonical
add_action( 'wp_head', 'amp_canonical_add_scripts' );
add_action( 'wp_head', 'amp_canonical_add_boilerplate_css' );
add_action( 'wp_footer', 'amp_deregister_scripts' );

// the final filter that replaces the content, run at the very end just to
// make sure that no content filters run after it
add_filter( 'the_content', 'amp_the_content_filter', PHP_INT_MAX);

function amp_the_content_filter($content) {
	if(isset($GLOBALS['amp_content'])) {
		return $GLOBALS['amp_content']->get_amp_content();
	} else {
		return $content;
	}
}

function amp_deregister_scripts() {
  wp_deregister_script( 'wp-embed' );
}

function amp_canonical_add_boilerplate_css() {
	?>
	<style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style><noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>
	<?php
}

function amp_canonical_add_scripts() {

	$amp_runtime_script = 'https://cdn.ampproject.org/v0.js';

	// Always include AMP form & analytics, as almost every template has search form somewhere and is tracking page views
	$scripts = array_merge(
		array('amp-form' => 'https://cdn.ampproject.org/v0/amp-form-0.1.js'),
		array('amp-analytics' => 'https://cdn.ampproject.org/v0/amp-analytics-0.1.js'),
		$GLOBALS['amp_content']->get_amp_scripts()
	);

	foreach ( $scripts as $element => $script ) : ?>
		<script custom-element="<?php echo esc_attr( $element ); ?>" src="<?php echo esc_url( $script ); ?>" async></script>
	<?php endforeach; ?>
	<script src="<?php echo esc_url( $amp_runtime_script ); ?>" async></script>
	<?php

}