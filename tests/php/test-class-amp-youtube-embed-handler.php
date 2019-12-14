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

	protected $youtube_video_id = 'kfVsfOSbJY0';

	/**
	 * Response for YouTube oEmbed request.
	 *
	 * @see Test_AMP_YouTube_Embed_Handler::$youtube_video_id
	 * @var string
	 */
	protected $youtube_oembed_response = '{"height":270,"type":"video","author_name":"rebecca","thumbnail_url":"https:\/\/i.ytimg.com\/vi\/kfVsfOSbJY0\/hqdefault.jpg","provider_url":"https:\/\/www.youtube.com\/","title":"Rebecca Black - Friday","version":"1.0","width":480,"thumbnail_height":360,"html":"\u003ciframe width=\"480\" height=\"270\" src=\"https:\/\/www.youtube.com\/embed\/kfVsfOSbJY0?feature=oembed\" frameborder=\"0\" allow=\"accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture\" allowfullscreen\u003e\u003c\/iframe\u003e","thumbnail_width":480,"provider_name":"YouTube","author_url":"https:\/\/www.youtube.com\/user\/rebecca"}';

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

		add_filter( 'pre_http_request', [ $this, 'mock_http_request' ], 10, 3 );
	}

	/**
	 * After a test method runs, reset any state in WordPress the test method might have changed.
	 */
	public function tearDown() {
		remove_filter( 'pre_http_request', [ $this, 'mock_http_request' ] );
		parent::tearDown();
	}

	/**
	 * Mock HTTP request.
	 *
	 * @param mixed  $preempt Whether to preempt an HTTP request's return value. Default false.
	 * @param mixed  $r       HTTP request arguments.
	 * @param string $url     The request URL.
	 * @return array Response data.
	 */
	public function mock_http_request( $preempt, $r, $url ) {
		$host = wp_parse_url( $url, PHP_URL_HOST );

		if ( ! in_array( $host, [ 'youtu.be', 'youtube.com', 'www.youtube.com' ], true ) ) {
			return $preempt;
		}

		/**
		 * The URL http://www.youtube.com/watch?v=kfVsfOSbJY0?hl=en&fs=1&w=425&h=349 is invalid as
		 * it has multiple query vars. Checking if `?v=kfVsfOSbJY0?hl=en` is in the URL should be a
		 * sufficient check when that URL is being mocked.
		 */
		if ( false !== strpos( $url, '%3Fv%3DkfVsfOSbJY0%3Fhl%3Den' ) ) {
			return $preempt;
		}

		unset( $r );

		return [
			'body'          => $this->youtube_oembed_response,
			'headers'       => [],
			'response'      => [
				'code'    => 200,
				'message' => 'ok',
			],
			'cookies'       => [],
			'http_response' => null,
		];
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
		$vimeo_shortcode = $this->handler->video_override( '', $attr_vimeo );
		$this->assertEquals( '', $vimeo_shortcode );

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
				'<p><amp-youtube data-videoid="kfVsfOSbJY0" layout="responsive" width="480" height="270" title="Rebecca Black - Friday"><a placeholder href="https://www.youtube.com/watch?v=kfVsfOSbJY0"><img src="https://i.ytimg.com/vi/kfVsfOSbJY0/hqdefault.jpg" layout="fill" object-fit="cover" alt="Rebecca Black - Friday"></img></a></amp-youtube></p>' . PHP_EOL,
				'<p><amp-youtube data-videoid="kfVsfOSbJY0" layout="responsive" width="480" height="270"><a placeholder href="https://www.youtube.com/watch?v=kfVsfOSbJY0"><img src="https://i.ytimg.com/vi/kfVsfOSbJY0/hqdefault.jpg" layout="fill" object-fit="cover"></img></a></amp-youtube></p>' . PHP_EOL,
			],

			'url_short'                        => [
				'https://youtu.be/kfVsfOSbJY0' . PHP_EOL,
				'<p><amp-youtube data-videoid="kfVsfOSbJY0" layout="responsive" width="480" height="270" title="Rebecca Black - Friday"><a placeholder href="https://youtu.be/kfVsfOSbJY0"><img src="https://i.ytimg.com/vi/kfVsfOSbJY0/hqdefault.jpg" layout="fill" object-fit="cover" alt="Rebecca Black - Friday"></img></a></amp-youtube></p>' . PHP_EOL,
				'<p><amp-youtube data-videoid="kfVsfOSbJY0" layout="responsive" width="480" height="270"><a placeholder href="https://youtu.be/kfVsfOSbJY0"><img src="https://i.ytimg.com/vi/kfVsfOSbJY0/hqdefault.jpg" layout="fill" object-fit="cover"></img></a></amp-youtube></p>' . PHP_EOL,
			],

			'url_with_querystring'             => [
				'http://www.youtube.com/watch?v=kfVsfOSbJY0&hl=en&fs=1&w=425&h=349' . PHP_EOL,
				'<p><amp-youtube data-videoid="kfVsfOSbJY0" layout="responsive" width="480" height="270" title="Rebecca Black - Friday"><a placeholder href="http://www.youtube.com/watch?v=kfVsfOSbJY0&amp;hl=en&amp;fs=1&amp;w=425&amp;h=349"><img src="https://i.ytimg.com/vi/kfVsfOSbJY0/hqdefault.jpg" layout="fill" object-fit="cover" alt="Rebecca Black - Friday"></img></a></amp-youtube></p>' . PHP_EOL,
				'<p><amp-youtube data-videoid="kfVsfOSbJY0" layout="responsive" width="480" height="270"><a placeholder href="http://www.youtube.com/watch?v=kfVsfOSbJY0&amp;hl=en&amp;fs=1&amp;w=425&amp;h=349"><img src="https://i.ytimg.com/vi/kfVsfOSbJY0/hqdefault.jpg" layout="fill" object-fit="cover"></img></a></amp-youtube></p>' . PHP_EOL,
			],

			// Several reports of invalid URLs that have multiple `?` in the URL.
			'url_with_querystring_and_extra_?' => [
				'http://www.youtube.com/watch?v=kfVsfOSbJY0?hl=en&fs=1&w=425&h=349' . PHP_EOL,
				'<p>http://www.youtube.com/watch?v=kfVsfOSbJY0?hl=en&#038;fs=1&#038;w=425&#038;h=349</p>' . PHP_EOL,
			],

			'embed_url'                        => [
				'https://www.youtube.com/embed/kfVsfOSbJY0' . PHP_EOL,
				'<p><amp-youtube data-videoid="kfVsfOSbJY0" layout="responsive" width="480" height="270" title="Rebecca Black - Friday"><a placeholder href="https://youtube.com/watch?v=kfVsfOSbJY0"><img src="https://i.ytimg.com/vi/kfVsfOSbJY0/hqdefault.jpg" layout="fill" object-fit="cover" alt="Rebecca Black - Friday"></img></a></amp-youtube></p>' . PHP_EOL,
				'<p><amp-youtube data-videoid="kfVsfOSbJY0" layout="responsive" width="480" height="270"><a placeholder href="https://youtube.com/watch?v=kfVsfOSbJY0"><img src="https://i.ytimg.com/vi/kfVsfOSbJY0/hqdefault.jpg" layout="fill" object-fit="cover"></img></a></amp-youtube></p>' . PHP_EOL,
			],
		];
	}

	/**
	 * @dataProvider get_conversion_data
	 */
	public function test__conversion( $source, $expected, $fallback_for_expected = null ) {
		$this->handler->register_embed();
		$filtered_content = apply_filters( 'the_content', $source );

		if (
			version_compare( strtok( get_bloginfo( 'version' ), '-' ), '5.2', '<' )
			&& null !== $fallback_for_expected
		) {
			$this->assertEquals( $fallback_for_expected, $filtered_content );
		} else {
			$this->assertEquals( $expected, $filtered_content );
		}
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
