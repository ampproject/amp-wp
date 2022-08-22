<?php
/**
 * Test TikTok embed.
 *
 * @package AMP.
 */

use AmpProject\AmpWP\Tests\Helpers\MarkupComparison;
use AmpProject\AmpWP\Tests\Helpers\WithoutBlockPreRendering;
use AmpProject\AmpWP\Tests\TestCase;

/**
 * Class Test_AMP_TikTok_Embed_Handler
 */
class Test_AMP_TikTok_Embed_Handler extends TestCase {

	use MarkupComparison;

	use WithoutBlockPreRendering {
		set_up as public prevent_block_pre_render;
	}

	/**
	 * Set up.
	 */
	public function set_up() {
		$this->prevent_block_pre_render();

		// Mock the HTTP request.
		add_filter( 'pre_http_request', [ $this, 'mock_http_request' ], 10, 3 );
	}

	/**
	 * After a test method runs, reset any state in WordPress the test method might have changed.
	 */
	public function tear_down() {
		remove_filter( 'pre_http_request', [ $this, 'mock_http_request' ] );
		parent::tear_down();
	}

	/**
	 * Mock HTTP request.
	 *
	 * @param mixed  $pre Whether to preempt an HTTP request's return value. Default false.
	 * @param mixed  $r   HTTP request arguments.
	 * @param string $url The request URL.
	 * @return array Response data.
	 */
	public function mock_http_request( $pre, $r, $url ) {
		if ( in_array( 'external-http', $_SERVER['argv'], true ) ) {
			return $pre;
		}

		if ( false === strpos( $url, 'tiktok.com' ) ) {
			return $pre;
		}

		$body = '{"version":"1.0","type":"video","title":"Scramble up ur name & I’ll try to guess it😍❤️ #foryoupage #petsoftiktok #aesthetic","author_url":"https://www.tiktok.com/@scout2015","author_name":"Scout and Suki","width":"100%","height":"100%","html":"<blockquote class=\"tiktok-embed\" cite=\"https://www.tiktok.com/@scout2015/video/6718335390845095173\" data-video-id=\"6718335390845095173\" style=\"max-width: 605px;min-width: 325px;\" > <section> <a target=\"_blank\" title=\"@scout2015\" href=\"https://www.tiktok.com/@scout2015\">@scout2015</a> <p>Scramble up ur name & I’ll try to guess it😍❤️ <a title=\"foryoupage\" target=\"_blank\" href=\"https://www.tiktok.com/tag/foryoupage\">#foryoupage</a> <a title=\"petsoftiktok\" target=\"_blank\" href=\"https://www.tiktok.com/tag/petsoftiktok\">#petsoftiktok</a> <a title=\"aesthetic\" target=\"_blank\" href=\"https://www.tiktok.com/tag/aesthetic\">#aesthetic</a></p> <a target=\"_blank\" title=\"♬ original sound - tiff\" href=\"https://www.tiktok.com/music/original-sound-6689804660171082501\">♬ original sound - tiff</a> </section> </blockquote> <script async src=\"https://www.tiktok.com/embed.js\"></script>","thumbnail_width":720,"thumbnail_height":1280,"thumbnail_url":"https://p16-sign-va.tiktokcdn.com/obj/tos-maliva-p-0068/06kv6rfcesljdjr45ukb0000d844090v0200000a05?x-expires=1600473600&x-signature=UYga2liJB%2Bb8auK8ejCI%2FFRLTX0%3D","provider_url":"https://www.tiktok.com","provider_name":"TikTok"}'; // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript

		return [
			'body'     => $body,
			'response' => [
				'code'    => 200,
				'message' => 'OK',
			],
		];
	}

	/**
	 * Get conversion data.
	 *
	 * @return array
	 */
	public function get_conversion_data() {
		return [
			'no_embed'                          => [
				'<p>Hello world.</p>',
				'<p>Hello world.</p>' . PHP_EOL,
			],

			'url_simple'                        => [
				'https://www.tiktok.com/@scout2015/video/6718335390845095173' . PHP_EOL,

				'
					<amp-tiktok layout="fixed-height" height="755" width="auto" data-src="https://www.tiktok.com/@scout2015/video/6718335390845095173">
						<blockquote class="tiktok-embed" cite="https://www.tiktok.com/@scout2015/video/6718335390845095173" data-video-id="6718335390845095173" placeholder>
							<section> <a target="_blank" title="@scout2015" href="https://www.tiktok.com/@scout2015">@scout2015</a>
							<p>Scramble up ur name &amp; I’ll try to guess it😍❤️ <a title="foryoupage" target="_blank" href="https://www.tiktok.com/tag/foryoupage">#foryoupage</a> <a title="petsoftiktok" target="_blank" href="https://www.tiktok.com/tag/petsoftiktok">#petsoftiktok</a> <a title="aesthetic" target="_blank" href="https://www.tiktok.com/tag/aesthetic">#aesthetic</a></p>
							<p> <a target="_blank" title="♬ original sound - tiff" href="https://www.tiktok.com/music/original-sound-6689804660171082501">♬ original sound – tiff</a> </p></section>
						</blockquote>
					</amp-tiktok>
				',
			],

			'tiktok-embed-code-with-wpautop'    => [
				'
					<blockquote class="tiktok-embed" cite="https://www.tiktok.com/@countingprimes/video/6988237085899574533" data-video-id="6988237085899574533" style="max-width: 605px;min-width: 325px;" > <section> <a target="_blank" title="@countingprimes" href="https://www.tiktok.com/@countingprimes">@countingprimes</a> <p>You can now embed TikTok\'s in AMP</p> <a target="_blank" title="♬ original sound - countingprimes" href="https://www.tiktok.com/music/original-sound-6988236987325057798">♬ original sound - countingprimes</a> </section> </blockquote> <script async src="https://www.tiktok.com/embed.js"></script>
				',
				'
					<amp-tiktok layout="fixed-height" height="719" width="auto" data-src="https://www.tiktok.com/@countingprimes/video/6988237085899574533">
						<blockquote class="tiktok-embed" cite="https://www.tiktok.com/@countingprimes/video/6988237085899574533" data-video-id="6988237085899574533" placeholder>
							<section>
								<a target="_blank" title="@countingprimes" href="https://www.tiktok.com/@countingprimes">@countingprimes</a>
								<p>You can now embed TikTok’s in AMP</p>
								<p> <a target="_blank" title="♬ original sound - countingprimes" href="https://www.tiktok.com/music/original-sound-6988236987325057798">♬ original sound – countingprimes</a> </p>
							</section>
						</blockquote>
					</amp-tiktok>
				',
			],

			'tiktok-embed-code-without-wpautop' => [
				'
					<!-- wp:html -->
					<blockquote class="tiktok-embed" cite="https://www.tiktok.com/@countingprimes/video/6988237085899574533" data-video-id="6988237085899574533" style="max-width: 605px;min-width: 325px;" > <section> <a target="_blank" title="@countingprimes" href="https://www.tiktok.com/@countingprimes">@countingprimes</a> <p>You can now embed TikTok\'s in AMP</p> <a target="_blank" title="♬ original sound - countingprimes" href="https://www.tiktok.com/music/original-sound-6988236987325057798">♬ original sound - countingprimes</a> </section> </blockquote> <script async src="https://www.tiktok.com/embed.js"></script>
					<!-- /wp:html -->
				',
				'
					<amp-tiktok layout="fixed-height" height="719" width="auto" data-src="https://www.tiktok.com/@countingprimes/video/6988237085899574533">
						<blockquote class="tiktok-embed" cite="https://www.tiktok.com/@countingprimes/video/6988237085899574533" data-video-id="6988237085899574533" placeholder>
							<section>
								<a target="_blank" title="@countingprimes" href="https://www.tiktok.com/@countingprimes">@countingprimes</a>
								<p>You can now embed TikTok’s in AMP</p>
								<a target="_blank" title="♬ original sound - countingprimes" href="https://www.tiktok.com/music/original-sound-6988236987325057798">♬ original sound – countingprimes</a>
							</section>
						</blockquote>
					</amp-tiktok>
				',
			],

			'amp-tiktok-passthrough'            => [
				'
				<!-- wp:html -->
				<amp-tiktok width="300" height="800" layout="intrinsic">
					<blockquote class="tiktok-embed" cite="https://www.tiktok.com/@countingprimes/video/6988237085899574533" data-video-id="6988237085899574533" style="max-width: 605px;min-width: 325px;">
						<section>
							<a target="_blank" title="@countingprimes" href="https://www.tiktok.com/@countingprimes">@countingprimes</a>
							<p>You can now embed TikTok’s in AMP</p>
							<a target="_blank" title="♬ original sound — countingprimes" href="https://www.tiktok.com/music/original-sound-6988236987325057798">♬ original sound — countingprimes</a>
						</section>
					</blockquote>
				</amp-tiktok>
				<!-- /wp:html -->
				',

				null,
			],
		];
	}

	/**
	 * Test conversion.
	 *
	 * @param string $source Source.
	 * @param string $expected Expected.
	 * @dataProvider get_conversion_data
	 */
	public function test_conversion( $source, $expected = null ) {
		if ( version_compare( '5.4-alpha', get_bloginfo( 'version' ), '>' ) ) {
			$this->markTestSkipped( 'The TikTok oEmbed provider is only available in 5.4-alpha and later' );
		}
		if ( ! $expected ) {
			$expected = $source;
		}

		$expected = preg_replace( '/<!--.*?-->/s', '', $expected );

		$embed = new AMP_TikTok_Embed_Handler();
		$embed->register_embed();

		$filtered_content = apply_filters( 'the_content', $source );
		$dom              = AMP_DOM_Utils::get_dom_from_content( $filtered_content );
		$embed->sanitize_raw_embeds( $dom );

		$actual = AMP_DOM_Utils::get_content_from_dom( $dom );

		// Remove new data attribute added recently.
		$actual = str_replace( ' data-embed-from="oembed"', '', $actual );

		// Remove refer param from URL.
		$actual = str_replace( '?refer=embed', '', $actual );

		$this->assertSimilarMarkup( $expected, $actual );
	}
}
