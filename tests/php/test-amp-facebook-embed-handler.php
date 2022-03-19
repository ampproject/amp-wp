<?php
/**
 * Class AMP_Facebook_Embed_Test
 *
 * @package AMP
 */

use AmpProject\AmpWP\Tests\Helpers\MarkupComparison;
use AmpProject\AmpWP\Tests\Helpers\WithoutBlockPreRendering;
use AmpProject\AmpWP\Tests\TestCase;

/**
 * Test AMP_Facebook_Embed_Handler_Test
 *
 * @covers AMP_Facebook_Embed_Handler
 */
class AMP_Facebook_Embed_Handler_Test extends TestCase {

	use MarkupComparison;
	use WithoutBlockPreRendering {
		set_up as public prevent_block_pre_render;
	}

	/**
	 * Get scripts data.
	 *
	 * @return array Scripts.
	 */
	public function get_scripts_data() {
		return [
			'not_converted' => [
				'<p>Hello World.</p>',
				[],
			],
			'converted'     => [
				'https://www.facebook.com/zuck/posts/10102593740125791' . PHP_EOL,
				[ 'amp-facebook' => true ],
			],
		];
	}

	/**
	 * Test get_scripts().
	 *
	 * @dataProvider get_scripts_data
	 * @param string $source Source.
	 * @param array  $expected Expected scripts.
	 */
	public function test__get_scripts( $source, $expected ) {
		$embed = new AMP_Facebook_Embed_Handler();
		$embed->register_embed();

		$filtered_content = apply_filters( 'the_content', $source );

		$dom = AMP_DOM_Utils::get_dom_from_content( $filtered_content );
		$embed->sanitize_raw_embeds( $dom );

		$validating_sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$validating_sanitizer->sanitize();

		$scripts = array_merge(
			$embed->get_scripts(),
			$validating_sanitizer->get_scripts()
		);

		$this->assertEquals( $expected, $scripts );
	}

	/**
	 * Data for test__raw_embed_sanitizer.
	 *
	 * @return array
	 */
	public function get_raw_embed_dataset() {
		$overflow_button = '<button overflow type="button">See more</button>';

		return [
			'no_embed_blockquote'           => [
				'<p>Hello world.</p>',
				'<p>Hello world.</p>',
			],

			'div_without_facebook_embed'    => [
				'<div>lorem ipsum</div>',
				'<div>lorem ipsum</div>',
			],

			'simple_url_https'              => [
				'https://www.facebook.com/zuck/posts/10102593740125791' . PHP_EOL,
				'<amp-facebook data-href="https://www.facebook.com/zuck/posts/10102593740125791" layout="responsive" width="600" height="400">' . $overflow_button . '</amp-facebook>' . PHP_EOL,
			],

			'notes_url'                     => [
				'https://www.facebook.com/notes/facebook-engineering/under-the-hood-the-javascript-sdk-truly-asynchronous-loading/10151176218703920/' . PHP_EOL,
				'<amp-facebook data-href="https://www.facebook.com/notes/facebook-engineering/under-the-hood-the-javascript-sdk-truly-asynchronous-loading/10151176218703920/" layout="responsive" width="600" height="400">' . $overflow_button . '</amp-facebook>' . PHP_EOL,
			],

			'photo_url'                     => [
				'https://www.facebook.com/photo.php?fbid=10102533316889441&set=a.529237706231.2034669.4&type=3&theater' . PHP_EOL,
				'<amp-facebook data-href="https://www.facebook.com/photo.php?fbid=10102533316889441&amp;set=a.529237706231.2034669.4&amp;type=3&amp;theater" layout="responsive" width="600" height="400">' . $overflow_button . '</amp-facebook>' . PHP_EOL,
			],

			'video_url'                     => [
				'https://www.facebook.com/zuck/videos/10102509264909801/' . PHP_EOL,
				'<amp-facebook data-href="https://www.facebook.com/zuck/videos/10102509264909801/" layout="responsive" width="600" height="400">' . $overflow_button . '</amp-facebook>' . PHP_EOL,
			],

			'post_embed'                    => [
				'<div class="fb-post" data-href="https://www.facebook.com/notes/facebook-engineering/under-the-hood-the-javascript-sdk-truly-asynchronous-loading/10151176218703920/"></div>',
				'<amp-facebook width="600" height="400" data-href="https://www.facebook.com/notes/facebook-engineering/under-the-hood-the-javascript-sdk-truly-asynchronous-loading/10151176218703920/" data-embed-as="post" layout="responsive">' . $overflow_button . '</amp-facebook>',
			],

			'post_with_fallbacks'           => [
				'
					<div class="fb-post" data-href="https://www.facebook.com/20531316728/posts/10154009990506729/" data-width="500" data-show-text="true">
						<blockquote cite="https://developers.facebook.com/20531316728/posts/10154009990506729/" class="fb-xfbml-parse-ignore"><!--blockquote_contents--></blockquote>
					</div>
				',
				'
					<amp-facebook width="500" height="400" data-href="https://www.facebook.com/20531316728/posts/10154009990506729/"  data-show-text="true" data-embed-as="post" layout="responsive">
						' . $overflow_button . '
						<blockquote cite="https://developers.facebook.com/20531316728/posts/10154009990506729/" class="fb-xfbml-parse-ignore" fallback=""><!--blockquote_contents--></blockquote>
					</amp-facebook>
				',
			],

			'video_embed'                   => [
				'<div class="fb-video" data-href="https://www.facebook.com/amanda.orr.56/videos/10212156330049017/" data-show-text="false"></div>',
				'<amp-facebook width="600" height="400" data-href="https://www.facebook.com/amanda.orr.56/videos/10212156330049017/" data-show-text="false" data-embed-as="video" layout="responsive">' . $overflow_button . '</amp-facebook>',
			],

			'page_embed'                    => [
				'
					<div class="fb-page" data-href="https://www.facebook.com/xwp.co/" data-width="340" data-height="432" data-hide-cover="true" data-show-facepile="true" data-show-posts="false">
						<div class="fb-xfbml-parse-ignore">
							<blockquote cite="https://www.facebook.com/xwp.co/"><!--blockquote_contents--></blockquote>
						</div>
					</div>
				',
				'
					<amp-facebook-page width="340" height="432" data-href="https://www.facebook.com/xwp.co/" data-hide-cover="true" data-show-facepile="true" data-show-posts="false" layout="responsive">
						' . $overflow_button . '
						<div class="fb-xfbml-parse-ignore" fallback="">
							<blockquote cite="https://www.facebook.com/xwp.co/"><!--blockquote_contents--></blockquote>
						</div>
					</amp-facebook-page>
				',
			],

			'like'                          => [
				'
					<div class="fb-like" data-href="https://developers.facebook.com/docs/plugins/" data-width="400" data-layout="standard" data-action="like" data-size="small" data-show-faces="true" data-share="true"></div>
				',
				'<amp-facebook-like width="400" height="400" data-href="https://developers.facebook.com/docs/plugins/" data-layout="standard" data-action="like" data-size="small" data-show-faces="true" data-share="true" layout="responsive">' . $overflow_button . '</amp-facebook-like>
				',
			],

			'comments'                      => [
				'
					<div class="fb-comments" data-href="https://developers.facebook.com/docs/plugins/comments#configurator" data-numposts="5"></div>
				',
				'<amp-facebook-comments width="600" height="400" data-href="https://developers.facebook.com/docs/plugins/comments#configurator" data-numposts="5" layout="responsive">' . $overflow_button . '</amp-facebook-comments>',
			],

			'comments_full_width'           => [
				'
					<div class="fb-comments" data-href="https://developers.facebook.com/docs/plugins/comments#configurator" data-width="100%" data-numposts="5"></div>
				',
				'<amp-facebook-comments width="auto" height="400" data-href="https://developers.facebook.com/docs/plugins/comments#configurator" data-numposts="5" layout="fixed-height">' . $overflow_button . '</amp-facebook-comments>',
			],

			'comments_full_width_2'         => [
				'
					<div class="fb-comments" data-href="https://developers.facebook.com/docs/plugins/comments#configurator" data-height="123" data-width="100%" data-numposts="5"></div>
				',
				'<amp-facebook-comments width="auto" height="123" data-href="https://developers.facebook.com/docs/plugins/comments#configurator" data-numposts="5" layout="fixed-height">' . $overflow_button . '</amp-facebook-comments>',
			],

			'comment_embed'                 => [
				'
					<div class="fb-comment-embed" data-href="https://www.facebook.com/zuck/posts/10102735452532991?comment_id=1070233703036185" data-width="500"></div>
				',
				'<amp-facebook width="500" height="400" data-href="https://www.facebook.com/zuck/posts/10102735452532991?comment_id=1070233703036185" data-embed-as="comment" layout="responsive">' . $overflow_button . '</amp-facebook>',
			],

			'remove_fb_root'                => [
				'<div id="fb-root"></div>' . str_repeat( '<script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v3.2"></script>', 5 ), // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
				'',
			],

			'remove_multiple_fb_root'       => [
				str_repeat( '<div id="fb-root"></div>', 5 ) . str_repeat( '<script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v3.2"></script>', 5 ), // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
				'',
			],

			'remove_empty_p_tag'            => [
				'<p><script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v3.2"></script></p><div id="fb-root"></div>', // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
				'',
			],

			'keep_p_tag_if_it_has_children' => [
				'<p><span id="foo-bar"></span><script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v3.2"></script></p><div id="fb-root"></div>', // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
				'<p><span id="foo-bar"></span></p>',
			],
		];
	}

	/**
	 * Test raw_embed_sanitizer.
	 *
	 * @param string $source  Content.
	 * @param string $expected Expected content.
	 * @dataProvider get_raw_embed_dataset
	 * @covers AMP_Facebook_Embed_Handler::sanitize_raw_embeds()
	 */
	public function test__raw_embed_sanitizer( $source, $expected ) {
		$embed = new AMP_Facebook_Embed_Handler();
		$embed->register_embed();

		$filtered_content = apply_filters( 'the_content', $source );

		$dom = AMP_DOM_Utils::get_dom_from_content( $filtered_content );
		$embed->sanitize_raw_embeds( $dom );

		$layout_sanitizer = new AMP_Layout_Sanitizer( $dom );
		$layout_sanitizer->sanitize();

		$content = AMP_DOM_Utils::get_content_from_dom( $dom );

		// Normalize blockquote contents to account for editing of published posts or variability of localized datetime strings.
		$content = preg_replace( '#(<blockquote.*?>).+?(</blockquote>)#s', '$1<!--blockquote_contents-->$2', $content );

		$this->assertEqualMarkup( $expected, $content );
	}
}
