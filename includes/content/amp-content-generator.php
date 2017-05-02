<?php
require_once(AMP__DIR__ . '/includes/utils/class-amp-dom-utils.php');
require_once(AMP__DIR__ . '/includes/utils/class-amp-html-utils.php');
require_once(AMP__DIR__ . '/includes/utils/class-amp-string-utils.php');

require_once(AMP__DIR__ . '/includes/content/class-amp-content.php');

require_once(AMP__DIR__ . '/includes/sanitizers/class-amp-blacklist-sanitizer.php');
require_once(AMP__DIR__ . '/includes/sanitizers/class-amp-img-sanitizer.php');
require_once(AMP__DIR__ . '/includes/sanitizers/class-amp-video-sanitizer.php');
require_once(AMP__DIR__ . '/includes/sanitizers/class-amp-iframe-sanitizer.php');
require_once(AMP__DIR__ . '/includes/sanitizers/class-amp-audio-sanitizer.php');
require_once(AMP__DIR__ . '/includes/sanitizers/class-amp-style-sanitizer.php');

require_once(AMP__DIR__ . '/includes/embeds/class-amp-twitter-embed.php');
require_once(AMP__DIR__ . '/includes/embeds/class-amp-youtube-embed.php');
require_once(AMP__DIR__ . '/includes/embeds/class-amp-gallery-embed.php');
require_once(AMP__DIR__ . '/includes/embeds/class-amp-instagram-embed.php');
require_once(AMP__DIR__ . '/includes/embeds/class-amp-vine-embed.php');
require_once(AMP__DIR__ . '/includes/embeds/class-amp-facebook-embed.php');
require_once(AMP__DIR__ . '/includes/embeds/class-amp-dailymotion-embed.php');
require_once(AMP__DIR__ . '/includes/embeds/class-amp-soundcloud-embed.php');

class AMPContentGenerator
{
	public static function amp_canonical_retrieve_content( $current_post ) {

		$content_max_width = 1200;

		if ( isset( $GLOBALS['content_width']) && $GLOBALS['content_width'] > 0 ) {
			$content_max_width = $GLOBALS['content_width'];
		}

		$amp_content = new AMP_Content(
			$current_post->post_content,
			array(
				'AMP_Twitter_Embed_Handler' => array(),
				'AMP_YouTube_Embed_Handler' => array(),
				'AMP_DailyMotion_Embed_Handler' => array(),
				'AMP_SoundCloud_Embed_Handler' => array(),
				'AMP_Instagram_Embed_Handler' => array(),
				'AMP_Vine_Embed_Handler' => array(),
				'AMP_Facebook_Embed_Handler' => array(),
				'AMP_Gallery_Embed_Handler' => array(),
			),
			array(
				'AMP_Style_Sanitizer' => array(),
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
}