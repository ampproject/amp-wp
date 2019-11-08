<?php
/**
 * Tests for AMP_YouTube_Embed_Handler.
 *
 * @package AMP
 * @since 0.7
 */

/**
 * Tests for AMP_YouTube_Embed_Handler.
 *
 * @covers AMP_YouTube_Embed_Handler
 */
class Test_AMP_YouTube_Embed_Handler extends WP_UnitTestCase {

	/**
	 * An instance of this embed handler.
	 *
	 * @var AMP_YouTube_Embed_Handler.
	 */
	public $handler;

	/**
	 * Set up each test.
	 */
	public function setUp() {
		parent::setUp();
		$this->handler = new AMP_YouTube_Embed_Handler();
	}

	/**
	 * Test video_override().
	 *
	 * @covers AMP_YouTube_Embed_Handler::video_override()
	 */
	public function test_video_override() {
		remove_all_filters( 'wp_video_shortcode_override' );
		$this->handler->register_embed();
		$youtube_id   = 'XOY3ZUO6P0k';
		$youtube_src  = 'https://youtu.be/' . $youtube_id;
		$attr_youtube = [
			'src' => $youtube_src,
		];

		$youtube_shortcode = $this->handler->video_override( '', $attr_youtube );
		$this->assertContains( '<amp-youtube', $youtube_shortcode );
		$this->assertContains( $youtube_id, $youtube_shortcode );

		$vimeo_id        = '64086087';
		$vimeo_src       = 'https://vimeo.com/' . $vimeo_id;
		$attr_vimeo      = [
			'src' => $vimeo_src,
		];
		$yimeo_shortcode = $this->handler->video_override( '', $attr_vimeo );
		$this->assertEquals( '', $yimeo_shortcode );

		$daily_motion_id        = 'x6bacgf';
		$daily_motion_src       = 'http://www.dailymotion.com/video/' . $daily_motion_id;
		$attr_daily_motion      = [
			'src' => $daily_motion_src,
		];
		$daily_motion_shortcode = $this->handler->video_override( '', $attr_daily_motion );
		$this->assertEquals( '', $daily_motion_shortcode );
		$no_attributes = $this->handler->video_override( '', [] );
		$this->assertEquals( '', $no_attributes );
		remove_all_filters( 'wp_video_shortcode_override' );
	}

	public function get_conversion_data() {
		return [
			'no_embed'                         => [
				'<p>Hello world.</p>',
				'<p>Hello world.</p>' . PHP_EOL,
			],

			'url_simple'                       => [
				'https://www.youtube.com/watch?v=kfVsfOSbJY0' . PHP_EOL,
				'<p><amp-youtube data-videoid="kfVsfOSbJY0" layout="responsive" width="500" height="281" title="Rebecca Black - Friday"><a fallback href="https://www.youtube.com/watch?v=kfVsfOSbJY0">Rebecca Black &#8211; Friday</a></amp-youtube></p>' . PHP_EOL,
			],

			'url_short'                        => [
				'https://youtu.be/kfVsfOSbJY0' . PHP_EOL,
				'<p><amp-youtube data-videoid="kfVsfOSbJY0" layout="responsive" width="500" height="281" title="Rebecca Black - Friday"><a fallback href="https://youtu.be/kfVsfOSbJY0">Rebecca Black &#8211; Friday</a></amp-youtube></p>' . PHP_EOL,
			],

			'url_with_querystring'             => [
				'http://www.youtube.com/watch?v=kfVsfOSbJY0&hl=en&fs=1&w=425&h=349' . PHP_EOL,
				'<p><amp-youtube data-videoid="kfVsfOSbJY0" layout="responsive" width="500" height="281" title="Rebecca Black - Friday"><a fallback href="http://www.youtube.com/watch?v=kfVsfOSbJY0&#038;hl=en&#038;fs=1&#038;w=425&#038;h=349">Rebecca Black &#8211; Friday</a></amp-youtube></p>' . PHP_EOL,
			],

			// Several reports of invalid URLs that have multiple `?` in the URL.
			'url_with_querystring_and_extra_?' => [
				'http://www.youtube.com/watch?v=kfVsfOSbJY0?hl=en&fs=1&w=425&h=349' . PHP_EOL,
				'<p>http://www.youtube.com/watch?v=kfVsfOSbJY0?hl=en&#038;fs=1&#038;w=425&#038;h=349</p>' . PHP_EOL,
			],

			'shortcode_unnamed_attr_as_url'    => [
				'[youtube http://www.youtube.com/watch?v=kfVsfOSbJY0]' . PHP_EOL,
				'<amp-youtube data-videoid="kfVsfOSbJY0" layout="responsive" width="600" height="338"></amp-youtube>' . PHP_EOL,
			],

			'embed_url'                        => [
				'https://www.youtube.com/embed/kfVsfOSbJY0' . PHP_EOL,
				'<p><amp-youtube data-videoid="kfVsfOSbJY0" layout="responsive" width="500" height="281" title="Rebecca Black - Friday"><a fallback href="https://youtube.com/watch?v=kfVsfOSbJY0">Rebecca Black &#8211; Friday</a></amp-youtube></p>' . PHP_EOL,
			],
		];
	}

	/**
	 * @dataProvider get_conversion_data
	 */
	public function test__conversion( $source, $expected ) {
		$this->handler->register_embed();
		$filtered_content = apply_filters( 'the_content', $source );

		$this->assertEquals( $expected, $filtered_content );
	}

	public function get_scripts_data() {
		return [
			'not_converted' => [
				'<p>Hello World.</p>',
				[],
			],
			'converted'     => [
				'https://www.youtube.com/watch?v=kfVsfOSbJY0' . PHP_EOL,
				[ 'amp-youtube' => true ],
			],
		];
	}

	/**
	 * @dataProvider get_scripts_data
	 */
	public function test__get_scripts( $source, $expected ) {
		$this->handler->register_embed();
		$source = apply_filters( 'the_content', $source );

		$whitelist_sanitizer = new AMP_Tag_And_Attribute_Sanitizer( AMP_DOM_Utils::get_dom_from_content( $source ) );
		$whitelist_sanitizer->sanitize();

		$scripts = array_merge(
			$this->handler->get_scripts(),
			$whitelist_sanitizer->get_scripts()
		);

		$this->assertEquals( $expected, $scripts );
	}

}
