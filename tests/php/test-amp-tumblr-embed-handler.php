<?php
/**
 * Class AMP_Tumblr_Embed_Handler_Test
 *
 * @package AMP
 */

use AmpProject\AmpWP\Tests\Helpers\MarkupComparison;

/**
 * Tests for Tumblr embeds.
 *
 * @coversDefaultClass \AMP_Tumblr_Embed_Handler
 */
class AMP_Tumblr_Embed_Handler_Test extends WP_UnitTestCase {

	use MarkupComparison;

	/**
	 * Set up.
	 */
	public function setUp() {
		parent::setUp();

		// Mock the HTTP request.
		add_filter( 'pre_http_request', [ $this, 'mock_http_request' ], 10, 3 );
	}

	/**
	 * Tear down.
	 */
	public function tearDown() {
		remove_filter( 'pre_http_request', [ $this, 'mock_http_request' ] );
		parent::tearDown();
	}

	/**
	 * Mock HTTP request.
	 *
	 * @param mixed  $pre Whether to preempt an HTTP request's return value. Default false.
	 * @param mixed  $r   HTTP request arguments.
	 * @param string $url The request URL.
	 * @return array Response data.
	 */
	public function mock_http_request( $pre, /** @noinspection PhpUnusedParameterInspection */ $r, $url ) {
		if ( in_array( 'external-http', $_SERVER['argv'], true ) ) {
			return $pre;
		}

		if ( false === strpos( $url, 'tumblr.com' ) ) {
			return $pre;
		}

		if ( false !== strpos( $url, 'grant-wood-american-gothic' ) ) {
			$body = '{"cache_age":3600,"url":"https:\/\/ifpaintingscouldtext.tumblr.com\/post\/92003045635\/grant-wood-american-gothic-1930","provider_url":"https:\/\/www.tumblr.com","provider_name":"Tumblr","author_name":"If Paintings Could Text","version":"1.0","author_url":"https:\/\/ifpaintingscouldtext.tumblr.com\/","type":"rich","html":"\u003Cdiv class=\u0022tumblr-post\u0022 data-href=\u0022https:\/\/embed.tumblr.com\/embed\/post\/2JT2XTaiTxO08wh21dqQrw\/92003045635\u0022 data-did=\u00227ce4825965cbd8bfd208f6aae43de7a528859aee\u0022  \u003E\u003Ca href=\u0022https:\/\/ifpaintingscouldtext.tumblr.com\/post\/92003045635\/grant-wood-american-gothic-1930\u0022\u003Ehttps:\/\/ifpaintingscouldtext.tumblr.com\/post\/92003045635\/grant-wood-american-gothic-1930\u003C\/a\u003E\u003C\/div\u003E\u003Cscript async src=\u0022https:\/\/assets.tumblr.com\/post.js\u0022\u003E\u003C\/script\u003E","height":null,"width":540}';
		} elseif ( false !== strpos( $url, 'how-do-vaccines-work' ) ) {
			// This has whitespace and a comment added as a child of the div.
			$body = '{"cache_age":3600,"url":"https:\/\/teded.tumblr.com\/post\/184736320764\/how-do-vaccines-work","provider_url":"https:\/\/www.tumblr.com","provider_name":"Tumblr","author_name":"TED-Ed - Gifs worth sharing","version":"1.0","author_url":"https:\/\/teded.tumblr.com\/","type":"rich","html":"\u003Cdiv class=\u0022tumblr-post\u0022 data-href=\u0022https:\/\/embed.tumblr.com\/embed\/post\/O6_eRR6K-z9QGTzdU5HrhQ\/184736320764\u0022 data-did=\u0022523d09cda8bc0da2f871ffea606ff71c80405725\u0022  \u003E  \u003C!--comment--\u003E  \u003Ca href=\u0022https:\/\/teded.tumblr.com\/post\/184736320764\/how-do-vaccines-work\u0022\u003Ehttps:\/\/teded.tumblr.com\/post\/184736320764\/how-do-vaccines-work\u003C\/a\u003E\u003C\/div\u003E\u003Cscript async src=\u0022https:\/\/assets.tumblr.com\/post.js\u0022\u003E\u003C\/script\u003E","height":null,"width":540}';
		} else {
			$body = '';
		}

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
			'no_embed'                   => [
				'',
				'',
			],

			'grant-wood-american-gothic' => [
				'https://ifpaintingscouldtext.tumblr.com/post/92003045635/grant-wood-american-gothic-1930',
				'<amp-iframe src="https://embed.tumblr.com/embed/post/2JT2XTaiTxO08wh21dqQrw/92003045635" layout="responsive" width="540" height="480" resizable="" sandbox="allow-scripts allow-popups allow-same-origin"><button overflow="" type="button">See more</button><a href="https://ifpaintingscouldtext.tumblr.com/post/92003045635/grant-wood-american-gothic-1930" placeholder="">https://ifpaintingscouldtext.tumblr.com/post/92003045635/grant-wood-american-gothic-1930</a></amp-iframe>',
			],

			'how-do-vaccines-work'       => [
				'https://teded.tumblr.com/post/184736320764/how-do-vaccines-work',
				'<amp-iframe src="https://embed.tumblr.com/embed/post/O6_eRR6K-z9QGTzdU5HrhQ/184736320764" layout="responsive" width="540" height="480" resizable="" sandbox="allow-scripts allow-popups allow-same-origin"><button overflow="" type="button">See more</button><a href="https://teded.tumblr.com/post/184736320764/how-do-vaccines-work" placeholder="">https://teded.tumblr.com/post/184736320764/how-do-vaccines-work</a></amp-iframe>',
			],
		];
	}

	/**
	 * Test conversion.
	 *
	 * @covers ::sanitize_raw_embeds()
	 * @dataProvider get_conversion_data
	 *
	 * @param string $url      URL.
	 * @param string $expected Expected content.
	 */
	public function test__conversion( $url, $expected ) {
		$embed = new AMP_Tumblr_Embed_Handler();

		// Check with all filters applied (including wpautop).
		$content = apply_filters( 'the_content', $url );
		$dom     = AMP_DOM_Utils::get_dom_from_content( $content );
		$embed->sanitize_raw_embeds( $dom );
		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$this->assertEqualMarkup( $expected, $content );

		if ( $url ) {
			$embed_block = sprintf(
				"<!-- wp:embed {\"url\":\"{$url}\",\"type\":\"rich\",\"providerNameSlug\":\"tumblr\",\"responsive\":true} -->\n<figure class=\"wp-block-embed is-type-rich is-provider-tumblr wp-block-embed-tumblr\"><div class=\"wp-block-embed__wrapper\">\n{$url}\n</div></figure>\n<!-- /wp:embed -->",
				$url
			);
			$content     = apply_filters( 'the_content', $embed_block );
			$dom         = AMP_DOM_Utils::get_dom_from_content( $content );
			$embed->sanitize_raw_embeds( $dom );
			$content = AMP_DOM_Utils::get_content_from_dom( $dom );

			$this->assertEqualMarkup(
				'<figure class="wp-block-embed is-type-rich is-provider-tumblr wp-block-embed-tumblr"><div class="wp-block-embed__wrapper">' . $expected . '</div></figure>',
				$content
			);
		}

		// Check with no filters applied.
		$dom = AMP_DOM_Utils::get_dom_from_content( ( new WP_Embed() )->shortcode( [], $url ) );
		$embed->sanitize_raw_embeds( $dom );
		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$this->assertEqualMarkup( $expected, $content );

		// Check with no filters applied and with the script pre-remoevd.
		$content = ( new WP_Embed() )->shortcode( [], $url );
		$content = preg_replace( '#<script.+?</script>#', '', $content );
		$dom     = AMP_DOM_Utils::get_dom_from_content( $content );
		$embed->sanitize_raw_embeds( $dom );
		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$this->assertEqualMarkup( $expected, $content );
	}

	/**
	 * Data for test_get_scripts().
	 *
	 * @return array Data.
	 */
	public function get_scripts_data() {
		return [
			'not_converted' => [
				'<p>Hello World.</p>',
				[],
			],
			'converted'     => [
				'https://ifpaintingscouldtext.tumblr.com/post/92003045635/grant-wood-american-gothic-1930' . PHP_EOL,
				[ 'amp-iframe' => true ],
			],
		];
	}

	/**
	 * Test AMP_Tag_And_Attribute_Sanitizer::get_scripts().
	 *
	 * @dataProvider get_scripts_data
	 *
	 * @param string $source   Source content.
	 * @param array  $expected Expected scripts.
	 */
	public function test_get_scripts( $source, $expected ) {
		$embed = new AMP_Tumblr_Embed_Handler();

		$filtered_content = apply_filters( 'the_content', $source );
		$dom              = AMP_DOM_Utils::get_dom_from_content( $filtered_content );
		$embed->sanitize_raw_embeds( $dom );

		$whitelist_sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$whitelist_sanitizer->sanitize();

		$scripts = array_merge(
			$embed->get_scripts(),
			$whitelist_sanitizer->get_scripts()
		);

		$this->assertEquals( $expected, $scripts );
	}
}
