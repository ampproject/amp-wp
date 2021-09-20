<?php
/**
 * Tests for Twitter Embeds.
 *
 * @package AMP
 */

use AmpProject\AmpWP\Tests\Helpers\WithoutBlockPreRendering;
use AmpProject\AmpWP\Tests\TestCase;

/**
 * Class AMP_Twitter_Embed_Handler_Test
 *
 * @covers AMP_Twitter_Embed_Handler
 */
class AMP_Twitter_Embed_Handler_Test extends TestCase {

	use WithoutBlockPreRendering {
		setUp as public prevent_block_pre_render;
	}

	/**
	 * oEmbed response for the tweet ID 987437752164737025.
	 *
	 * @var string
	 */
	protected $oembed_response_1 = '{"url":"https:\/\/twitter.com\/WordPress\/status\/987437752164737025","author_name":"WordPress","author_url":"https:\/\/twitter.com\/WordPress","html":"\u003Cblockquote class=\"twitter-tweet\" data-width=\"500\" data-dnt=\"true\"\u003E\u003Cp lang=\"en\" dir=\"ltr\"\u003ECelebrate the WordPress 15th Anniversary on May 27 \u003Ca href=\"https:\/\/t.co\/jv62WkI9lr\"\u003Ehttps:\/\/t.co\/jv62WkI9lr\u003C\/a\u003E \u003Ca href=\"https:\/\/t.co\/4ZECodSK78\"\u003Epic.twitter.com\/4ZECodSK78\u003C\/a\u003E\u003C\/p\u003E&mdash; WordPress (@WordPress) \u003Ca href=\"https:\/\/twitter.com\/WordPress\/status\/987437752164737025?ref_src=twsrc%5Etfw\"\u003EApril 20, 2018\u003C\/a\u003E\u003C\/blockquote\u003E\n\u003Cscript async src=\"https:\/\/platform.twitter.com\/widgets.js\" charset=\"utf-8\"\u003E\u003C\/script\u003E\n","width":500,"height":null,"type":"rich","cache_age":"3153600000","provider_name":"Twitter","provider_url":"https:\/\/twitter.com","version":"1.0"}';

	/**
	 * oEmbed response for the tweet ID 705219971425574912.
	 *
	 * @var string
	 */
	protected $oembed_response_2 = '{"url":"https:\/\/twitter.com\/sengineland\/status\/705219971425574912","author_name":"Search Engine Land","author_url":"https:\/\/twitter.com\/sengineland","html":"\u003Cblockquote class=\"twitter-tweet\" data-width=\"500\" data-dnt=\"true\"\u003E\u003Cp lang=\"en\" dir=\"ltr\"\u003EOn our way to the \u003Ca href=\"https:\/\/twitter.com\/hashtag\/GoogleDance?src=hash&amp;ref_src=twsrc%5Etfw\"\u003E#GoogleDance\u003C\/a\u003E! \u003Ca href=\"https:\/\/twitter.com\/hashtag\/SMX?src=hash&amp;ref_src=twsrc%5Etfw\"\u003E#SMX\u003C\/a\u003E \uD83D\uDC83\uD83C\uDFFB \u003Ca href=\"https:\/\/t.co\/N8kZ9M3eN4\"\u003Epic.twitter.com\/N8kZ9M3eN4\u003C\/a\u003E\u003C\/p\u003E&mdash; Search Engine Land (@sengineland) \u003Ca href=\"https:\/\/twitter.com\/sengineland\/status\/705219971425574912?ref_src=twsrc%5Etfw\"\u003EMarch 3, 2016\u003C\/a\u003E\u003C\/blockquote\u003E\n\u003Cscript async src=\"https:\/\/platform.twitter.com\/widgets.js\" charset=\"utf-8\"\u003E\u003C\/script\u003E\n","width":500,"height":null,"type":"rich","cache_age":"3153600000","provider_name":"Twitter","provider_url":"https:\/\/twitter.com","version":"1.0"}';

	/**
	 * Set up each test.
	 */
	public function set_up() {
		$this->prevent_block_pre_render();

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
		if ( in_array( 'external-http', $_SERVER['argv'], true ) ) {
			return $preempt;
		}

		$host = wp_parse_url( $url, PHP_URL_HOST );

		if ( 'publish.twitter.com' !== $host ) {
			return $preempt;
		}

		if ( false !== strpos( $url, '987437752164737025' ) ) {
			$body = $this->oembed_response_1;
		} else {
			$body = $this->oembed_response_2;
		}

		unset( $r );

		return [
			'body'          => $body,
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
	 * Get conversion data.
	 *
	 * @return array
	 */
	public function get_conversion_data() {
		$overflow_button = '<button overflow type="button">See more</button>';

		return [
			'no_embed'                  => [
				'<p>Hello world.</p>',
				'<p>Hello world.</p>' . PHP_EOL,
			],
			'url_simple'                => [
				'https://twitter.com/wordpress/status/987437752164737025' . PHP_EOL,
				"<amp-twitter width=\"auto\" height=\"197\" layout=\"fixed-height\" data-tweetid=\"987437752164737025\" class=\"twitter-tweet\" data-width=\"500\" data-dnt=\"true\">$overflow_button<blockquote class=\"twitter-tweet\" data-width=\"500\" data-dnt=\"true\" placeholder=\"\">\n<p lang=\"en\" dir=\"ltr\">Celebrate the WordPress 15th Anniversary on May 27 <a href=\"https://t.co/jv62WkI9lr\">https://t.co/jv62WkI9lr</a> <a href=\"https://t.co/4ZECodSK78\">pic.twitter.com/4ZECodSK78</a></p>\n<p>— WordPress (@WordPress) <a href=\"https://twitter.com/WordPress/status/987437752164737025?ref_src=twsrc%5Etfw\">April 20, 2018</a></p></blockquote></amp-twitter>\n\n",
			],
			'url_with_big_tweet_id'     => [
				'https://twitter.com/wordpress/status/705219971425574912' . PHP_EOL,
				"<amp-twitter width=\"auto\" height=\"197\" layout=\"fixed-height\" data-tweetid=\"705219971425574912\" class=\"twitter-tweet\" data-width=\"500\" data-dnt=\"true\">$overflow_button<blockquote class=\"twitter-tweet\" data-width=\"500\" data-dnt=\"true\" placeholder=\"\">\n<p lang=\"en\" dir=\"ltr\">On our way to the <a href=\"https://twitter.com/hashtag/GoogleDance?src=hash&amp;ref_src=twsrc%5Etfw\">#GoogleDance</a>! <a href=\"https://twitter.com/hashtag/SMX?src=hash&amp;ref_src=twsrc%5Etfw\">#SMX</a> 💃🏻 <a href=\"https://t.co/N8kZ9M3eN4\">pic.twitter.com/N8kZ9M3eN4</a></p>\n<p>— Search Engine Land (@sengineland) <a href=\"https://twitter.com/sengineland/status/705219971425574912?ref_src=twsrc%5Etfw\">March 3, 2016</a></p></blockquote></amp-twitter>\n\n",
			],
			'timeline_url_with_profile' => [
				'https://twitter.com/wordpress' . PHP_EOL,
				"<p><amp-twitter data-timeline-source-type=\"profile\" data-timeline-screen-name=\"wordpress\" width=\"auto\" height=\"197\" layout=\"fixed-height\">$overflow_button</amp-twitter></p>\n",
			],
			'timeline_url_with_likes'   => [
				'https://twitter.com/wordpress/likes' . PHP_EOL,
				"<p><amp-twitter data-timeline-source-type=\"likes\" data-timeline-screen-name=\"wordpress\" width=\"auto\" height=\"197\" layout=\"fixed-height\">$overflow_button</amp-twitter></p>\n",
			],
			'timeline_url_with_list'    => [
				'https://twitter.com/wordpress/lists/random_list' . PHP_EOL,
				"<p><amp-twitter data-timeline-source-type=\"list\" data-timeline-slug=\"random_list\" data-timeline-owner-screen-name=\"wordpress\" width=\"auto\" height=\"197\" layout=\"fixed-height\">$overflow_button</amp-twitter></p>\n",
			],
			'timeline_url_with_list2'   => [
				'https://twitter.com/robertnyman/lists/web-gdes' . PHP_EOL,
				"<p><amp-twitter data-timeline-source-type=\"list\" data-timeline-slug=\"web-gdes\" data-timeline-owner-screen-name=\"robertnyman\" width=\"auto\" height=\"197\" layout=\"fixed-height\">$overflow_button</amp-twitter></p>\n",
			],
		];
	}

	/**
	 * Test conversion.
	 *
	 * @dataProvider get_conversion_data
	 * @param string $source   Source.
	 * @param string $expected Expected content.
	 */
	public function test__conversion( $source, $expected ) {
		$embed = new AMP_Twitter_Embed_Handler();
		$embed->register_embed();

		$filtered_content = apply_filters( 'the_content', $source );
		$dom              = AMP_DOM_Utils::get_dom_from_content( $filtered_content );
		$embed->sanitize_raw_embeds( $dom );

		$content = AMP_DOM_Utils::get_content_from_dom( $dom );

		$this->assertEquals( $expected, $content );
	}

	public function get_scripts_data() {
		return [
			'not_converted' => [
				'<p>Hello World.</p>',
				[],
			],
			'converted'     => [
				'https://twitter.com/altjoen/status/987437752164737025' . PHP_EOL,
				[ 'amp-twitter' => true ],
			],
		];
	}

	/**
	 * Test get_scripts().
	 *
	 * @dataProvider get_scripts_data
	 *
	 * @param string $source   Source content.
	 * @param array  $expected Expected scripts.
	 */
	public function test__get_scripts( $source, $expected ) {
		$embed = new AMP_Twitter_Embed_Handler();
		$embed->register_embed();

		$filtered_content = apply_filters( 'the_content', $source );
		$dom              = AMP_DOM_Utils::get_dom_from_content( $filtered_content );
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
	 * @return array Data.
	 */
	public function get_raw_embed_dataset() {
		$overflow_button = '<button overflow type="button">See more</button>';

		return [
			'no_embed'                                => [
				'<p>Hello world.</p>',
				'<p>Hello world.</p>',
			],
			'embed_blockquote_without_twitter'        => [
				'<blockquote>lorem ipsum</blockquote>',
				'<blockquote>lorem ipsum</blockquote>',
			],

			'blockquote_embed'                        => [
				wpautop( '<blockquote class="twitter-tweet" data-lang="en"><p lang="en" dir="ltr">Celebrate the WordPress 15th Anniversary on May 27 <a href="https://t.co/jv62WkI9lr">https://t.co/jv62WkI9lr</a> <a href="https://t.co/4ZECodSK78">pic.twitter.com/4ZECodSK78</a></p>-- WordPress (@WordPress) <a href="https://twitter.com/WordPress/status/987437752164737025?ref_src=twsrc%5Etfw">April 20, 2018</a></blockquote> <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>' ), // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript, WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine
				'<amp-twitter width="auto" height="197" layout="fixed-height" data-tweetid="987437752164737025" class="twitter-tweet" data-lang="en">' . $overflow_button . '<blockquote class="twitter-tweet" data-lang="en" placeholder="">' . "\n" . '<p lang="en" dir="ltr">Celebrate the WordPress 15th Anniversary on May 27 <a href="https://t.co/jv62WkI9lr">https://t.co/jv62WkI9lr</a> <a href="https://t.co/4ZECodSK78">pic.twitter.com/4ZECodSK78</a></p>' . "\n" . '<p>-- WordPress (@WordPress) <a href="https://twitter.com/WordPress/status/987437752164737025?ref_src=twsrc%5Etfw">April 20, 2018</a></p></blockquote></amp-twitter>' . "\n\n",
			],

			'blockquote_embed_with_data_conversation' => [
				wpautop( '<blockquote class="twitter-tweet" data-conversation="none"><p lang="en" dir="ltr">Celebrate the WordPress 15th Anniversary on May 27 <a href="https://t.co/jv62WkI9lr">https://t.co/jv62WkI9lr</a> <a href="https://t.co/4ZECodSK78">pic.twitter.com/4ZECodSK78</a></p>&mdash; WordPress (@WordPress) <a href="https://twitter.com/WordPress/status/987437752164737025?ref_src=twsrc%5Etfw">April 20, 2018</a></blockquote> <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>' ), // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript, WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine
				'<amp-twitter width="auto" height="197" layout="fixed-height" data-tweetid="987437752164737025" class="twitter-tweet" data-conversation="none">' . $overflow_button . '<blockquote class="twitter-tweet" data-conversation="none" placeholder="">' . "\n" . '<p lang="en" dir="ltr">Celebrate the WordPress 15th Anniversary on May 27 <a href="https://t.co/jv62WkI9lr">https://t.co/jv62WkI9lr</a> <a href="https://t.co/4ZECodSK78">pic.twitter.com/4ZECodSK78</a></p>' . "\n" . '<p>— WordPress (@WordPress) <a href="https://twitter.com/WordPress/status/987437752164737025?ref_src=twsrc%5Etfw">April 20, 2018</a></p></blockquote></amp-twitter>' . "\n\n",
			],

			'blockquote_embed_with_data_theme'        => [
				wpautop( '<blockquote class="twitter-tweet" data-theme="en"><p lang="en" dir="ltr">Celebrate the WordPress 15th Anniversary on May 27 <a href="https://t.co/jv62WkI9lr">https://t.co/jv62WkI9lr</a> <a href="https://t.co/4ZECodSK78">pic.twitter.com/4ZECodSK78</a></p>&mdash; WordPress (@WordPress) <a href="https://twitter.com/WordPress/status/987437752164737025?ref_src=twsrc%5Etfw">April 20, 2018</a></blockquote> <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>' ), // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript, WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine
				'<amp-twitter width="auto" height="197" layout="fixed-height" data-tweetid="987437752164737025" class="twitter-tweet" data-theme="en">' . $overflow_button . '<blockquote class="twitter-tweet" data-theme="en" placeholder="">' . "\n" . '<p lang="en" dir="ltr">Celebrate the WordPress 15th Anniversary on May 27 <a href="https://t.co/jv62WkI9lr">https://t.co/jv62WkI9lr</a> <a href="https://t.co/4ZECodSK78">pic.twitter.com/4ZECodSK78</a></p>' . "\n" . '<p>— WordPress (@WordPress) <a href="https://twitter.com/WordPress/status/987437752164737025?ref_src=twsrc%5Etfw">April 20, 2018</a></p></blockquote></amp-twitter>' . "\n\n",
			],

			'blockquote_embed_not_autop'              => [
				'<blockquote class="twitter-tweet" data-lang="en"><p lang="en" dir="ltr">Celebrate the WordPress 15th Anniversary on May 27 <a href="https://t.co/jv62WkI9lr">https://t.co/jv62WkI9lr</a> <a href="https://t.co/4ZECodSK78">pic.twitter.com/4ZECodSK78</a></p>-- WordPress (@WordPress) <a href="https://twitter.com/WordPress/status/987437752164737025?ref_src=twsrc%5Etfw">April 20, 2018</a></blockquote> <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>', // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript, WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine
				'<amp-twitter width="auto" height="197" layout="fixed-height" data-tweetid="987437752164737025" class="twitter-tweet" data-lang="en">' . $overflow_button . '<blockquote class="twitter-tweet" data-lang="en" placeholder=""><p lang="en" dir="ltr">Celebrate the WordPress 15th Anniversary on May 27 <a href="https://t.co/jv62WkI9lr">https://t.co/jv62WkI9lr</a> <a href="https://t.co/4ZECodSK78">pic.twitter.com/4ZECodSK78</a></p>-- WordPress (@WordPress) <a href="https://twitter.com/WordPress/status/987437752164737025?ref_src=twsrc%5Etfw">April 20, 2018</a></blockquote></amp-twitter> ',
			],
		];
	}

	/**
	 * Test raw_embed_sanitizer.
	 *
	 * @param string $source  Content.
	 * @param string $expected Expected content.
	 * @dataProvider get_raw_embed_dataset
	 * @covers AMP_Twitter_Embed_Handler::sanitize_raw_embeds()
	 */
	public function test__raw_embed_sanitizer( $source, $expected ) {
		$dom   = AMP_DOM_Utils::get_dom_from_content( $source );
		$embed = new AMP_Twitter_Embed_Handler();

		$embed->sanitize_raw_embeds( $dom );

		$content = AMP_DOM_Utils::get_content_from_dom( $dom );

		$this->assertEquals( $expected, $content );
	}
}
