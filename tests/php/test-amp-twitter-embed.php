<?php

class AMP_Twitter_Embed_Test extends WP_UnitTestCase {
	public function get_conversion_data() {
		return array(
			'no_embed'                             => array(
				'<p>Hello world.</p>',
				'<p>Hello world.</p>' . PHP_EOL,
			),
			'url_simple'                           => array(
				'https://twitter.com/wordpress/status/987437752164737025' . PHP_EOL,
				'<p><amp-twitter data-tweetid="987437752164737025" layout="responsive" width="600" height="480"></amp-twitter></p>' . PHP_EOL,
			),
			'url_with_big_tweet_id'                => array(
				'https://twitter.com/wordpress/status/705219971425574912' . PHP_EOL,
				'<p><amp-twitter data-tweetid="705219971425574912" layout="responsive" width="600" height="480"></amp-twitter></p>' . PHP_EOL,
			),

			'timeline_url_with_profile'            => array(
				'https://twitter.com/wordpress' . PHP_EOL,
				'<p><amp-twitter data-timeline-source-type="profile" data-timeline-screen-name="wordpress" layout="responsive" width="600" height="480"></amp-twitter></p>' . PHP_EOL,
			),
			'timeline_url_with_likes'              => array(
				'https://twitter.com/wordpress/likes' . PHP_EOL,
				'<p><amp-twitter data-timeline-source-type="likes" data-timeline-screen-name="wordpress" layout="responsive" width="600" height="480"></amp-twitter></p>' . PHP_EOL,
			),
			'timeline_url_with_list'               => array(
				'https://twitter.com/wordpress/lists/random_list' . PHP_EOL,
				'<p><amp-twitter data-timeline-source-type="list" data-timeline-slug="random_list" data-timeline-owner-screen-name="wordpress" layout="responsive" width="600" height="480"></amp-twitter></p>' . PHP_EOL,
			),
			'timeline_url_with_list2'              => array(
				'https://twitter.com/robertnyman/lists/web-gdes' . PHP_EOL,
				'<p><amp-twitter data-timeline-source-type="list" data-timeline-slug="web-gdes" data-timeline-owner-screen-name="robertnyman" layout="responsive" width="600" height="480"></amp-twitter></p>' . PHP_EOL,
			),

			'shortcode_without_id'                 => array(
				'[tweet]' . PHP_EOL,
				'' . PHP_EOL,
			),
			'shortcode_simple'                     => array(
				'[tweet 987437752164737025]' . PHP_EOL,
				'<amp-twitter data-tweetid="987437752164737025" layout="responsive" width="600" height="480"></amp-twitter>' . PHP_EOL,
			),
			'shortcode_with_tweet_attribute'       => array(
				'[tweet tweet=987437752164737025]' . PHP_EOL,
				'<amp-twitter data-tweetid="987437752164737025" layout="responsive" width="600" height="480"></amp-twitter>' . PHP_EOL,
			),
			'shortcode_with_big_tweet_id'          => array(
				'[tweet 705219971425574912]' . PHP_EOL,
				'<amp-twitter data-tweetid="705219971425574912" layout="responsive" width="600" height="480"></amp-twitter>' . PHP_EOL,
			),
			'shortcode_with_url'                   => array(
				'[tweet https://twitter.com/wordpress/status/987437752164737025]' . PHP_EOL,
				'<amp-twitter data-tweetid="987437752164737025" layout="responsive" width="600" height="480"></amp-twitter>' . PHP_EOL,
			),
			'shortcode_with_url_with_big_tweet_id' => array(
				'[tweet https://twitter.com/wordpress/status/705219971425574912]' . PHP_EOL,
				'<amp-twitter data-tweetid="705219971425574912" layout="responsive" width="600" height="480"></amp-twitter>' . PHP_EOL,
			),
			'shortcode_with_non_numeric_tweet_id'  => array(
				'[tweet abcd]' . PHP_EOL,
				'' . PHP_EOL,
			),
		);
	}

	/**
	 * @dataProvider get_conversion_data
	 */
	public function test__conversion( $source, $expected ) {
		$embed = new AMP_Twitter_Embed_Handler();
		$embed->register_embed();
		$filtered_content = apply_filters( 'the_content', $source );

		$this->assertEquals( $expected, $filtered_content );
	}

	public function get_scripts_data() {
		return array(
			'not_converted' => array(
				'<p>Hello World.</p>',
				array(),
			),
			'converted'     => array(
				'https://twitter.com/altjoen/status/987437752164737025' . PHP_EOL,
				array( 'amp-twitter' => true ),
			),
		);
	}

	/**
	 * @dataProvider get_scripts_data
	 */
	public function test__get_scripts( $source, $expected ) {
		$embed = new AMP_Twitter_Embed_Handler();
		$embed->register_embed();
		$source = apply_filters( 'the_content', $source );

		$whitelist_sanitizer = new AMP_Tag_And_Attribute_Sanitizer( AMP_DOM_Utils::get_dom_from_content( $source ) );
		$whitelist_sanitizer->sanitize();

		$scripts = array_merge(
			$embed->get_scripts(),
			$whitelist_sanitizer->get_scripts()
		);

		$this->assertEquals( $expected, $scripts );
	}

	/**
	 * Data for test__raw_embed_sanitizer.
	 *
	 * @return array
	 */
	public function get_raw_embed_dataset() {
		return array(
			'no_embed'                         => array(
				'<p>Hello world.</p>',
				'<p>Hello world.</p>',
			),
			'embed_blockquote_without_twitter' => array(
				'<blockquote>lorem ipsum</blockquote>',
				'<blockquote>lorem ipsum</blockquote>',
			),

			'blockquote_embed'                 => array(
				wpautop( '<blockquote class="twitter-tweet" data-lang="en"><p lang="en" dir="ltr">Celebrate the WordPress 15th Anniversary on May 27 <a href="https://t.co/jv62WkI9lr">https://t.co/jv62WkI9lr</a> <a href="https://t.co/4ZECodSK78">pic.twitter.com/4ZECodSK78</a></p>&mdash; WordPress (@WordPress) <a href="https://twitter.com/WordPress/status/987437752164737025?ref_src=twsrc%5Etfw">April 20, 2018</a></blockquote> <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>' ), // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript, WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine
				'<amp-twitter width="600" height="480" layout="responsive" data-tweetid="987437752164737025"></amp-twitter>' . "\n\n",
			),

			'blockquote_embed_not_autop'       => array(
				'<blockquote class="twitter-tweet" data-lang="en"><p lang="en" dir="ltr">Celebrate the WordPress 15th Anniversary on May 27 <a href="https://t.co/jv62WkI9lr">https://t.co/jv62WkI9lr</a> <a href="https://t.co/4ZECodSK78">pic.twitter.com/4ZECodSK78</a></p>&mdash; WordPress (@WordPress) <a href="https://twitter.com/WordPress/status/987437752164737025?ref_src=twsrc%5Etfw">April 20, 2018</a></blockquote> <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>', // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript, WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine
				'<amp-twitter width="600" height="480" layout="responsive" data-tweetid="987437752164737025"></amp-twitter> ',
			),
		);
	}

	/**
	 * Test raw_embed_sanitizer.
	 *
	 * @param string $source  Content.
	 * @param string $expected Expected content.
	 * @dataProvider get_raw_embed_dataset
	 * @covers AMP_Instagram_Embed_Handler::sanitize_raw_embeds()
	 */
	public function test__raw_embed_sanitizer( $source, $expected ) {
		$dom   = AMP_DOM_Utils::get_dom_from_content( $source );
		$embed = new AMP_Twitter_Embed_Handler();

		$embed->sanitize_raw_embeds( $dom );

		$content = AMP_DOM_Utils::get_content_from_dom( $dom );

		$this->assertEquals( $expected, $content );
	}
}
