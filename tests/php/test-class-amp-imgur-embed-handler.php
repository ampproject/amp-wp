<?php
/**
 * Test Imgur embed.
 *
 * @package AMP.
 */

use AmpProject\AmpWP\Tests\Helpers\WithoutBlockPreRendering;

/**
 * Class AMP_Imgur_Embed_Handler_Test
 */
class AMP_Imgur_Embed_Handler_Test extends WP_UnitTestCase {

	use WithoutBlockPreRendering {
		setUp as public prevent_block_pre_render;
	}

	/**
	 * Set up.
	 */
	public function setUp() {
		$this->prevent_block_pre_render();

		// Mock the HTTP request.
		add_filter(
			'pre_http_request',
			static function( $pre, $r, $url ) {
				if ( in_array( 'external-http', $_SERVER['argv'], true ) ) {
					return $pre;
				}

				if ( false !== strpos( $url, 'fmHGADZ' ) ) {
					$body = '{"version":"1.0","type":"rich","provider_name":"Imgur","provider_url":"https:\/\/imgur.com","width":500,"height":750,"html":"<blockquote class=\"imgur-embed-pub\" lang=\"en\" data-id=\"fmHGADZ\"><a href=\"https:\/\/imgur.com\/fmHGADZ\">View post on imgur.com<\/a><\/blockquote><script async src=\"\/\/s.imgur.com\/min\/embed.js\" charset=\"utf-8\"><\/script>"}'; // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
				} elseif ( false !== strpos( $url, '1ApvcWB' ) ) {
					$body = '{"version":"1.0","type":"rich","provider_name":"Imgur","provider_url":"https:\/\/imgur.com","width":500,"height":750,"html":"<blockquote class=\"imgur-embed-pub\" lang=\"en\" data-id=\"a\/1ApvcWB\"><a href=\"https:\/\/imgur.com\/a\/1ApvcWB\">Oops, all baby yoda<\/a><\/blockquote><script async src=\"\/\/s.imgur.com\/min\/embed.js\" charset=\"utf-8\"><\/script>"}'; // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
				} else {
					return $pre;
				}

				return [
					'body'     => $body,
					'response' => [
						'code'    => 200,
						'message' => 'OK',
					],
				];
			},
			10,
			3
		);
	}

	/**
	 * Get conversion data.
	 *
	 * @return array
	 */
	public function get_conversion_data() {
		$width  = 500;
		$height = 750;

		return [
			'no_embed'        => [
				'<p>Hello world.</p>',
				'<p>Hello world.</p>' . PHP_EOL,
			],

			'url_simple'      => [
				'https://imgur.com/fmHGADZ' . PHP_EOL,
				'<p><amp-imgur width="' . $width . '" height="' . $height . '" data-imgur-id="fmHGADZ"></amp-imgur></p>' . PHP_EOL,
			],

			'url_with_detail' => [
				'https://imgur.com/gallery/1ApvcWB' . PHP_EOL,
				'<p><amp-imgur width="' . $width . '" height="' . $height . '" data-imgur-id="1ApvcWB"></amp-imgur></p>' . PHP_EOL,
			],

			'url_with_params' => [
				'https://imgur.com/gallery/1ApvcWB?foo=bar' . PHP_EOL,
				'<p><amp-imgur width="' . $width . '" height="' . $height . '" data-imgur-id="1ApvcWB"></amp-imgur></p>' . PHP_EOL,
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
	public function test__conversion( $source, $expected ) {
		$embed = new AMP_Imgur_Embed_Handler();
		$embed->register_embed();
		$filtered_content = apply_filters( 'the_content', $source );

		$this->assertEquals( $expected, $filtered_content );
	}

	/**
	 * Get scripts data.
	 *
	 * @return array
	 */
	public function get_scripts_data() {
		return [
			'not_converted' => [
				'<p>Hello World.</p>',
				[],
			],
			'converted'     => [
				'https://www.imgur.com/gallery/1ApvcWB' . PHP_EOL,
				[ 'amp-imgur' => true ],
			],
		];
	}

	/**
	 * Test scripts.
	 *
	 * @param string $source Source.
	 * @param string $expected Expected.
	 * @dataProvider get_scripts_data
	 */
	public function test__get_scripts( $source, $expected ) {
		$embed = new AMP_Imgur_Embed_Handler();
		$embed->register_embed();
		$source = apply_filters( 'the_content', $source );

		$validating_sanitizer = new AMP_Tag_And_Attribute_Sanitizer( AMP_DOM_Utils::get_dom_from_content( $source ) );
		$validating_sanitizer->sanitize();

		$scripts = array_merge(
			$embed->get_scripts(),
			$validating_sanitizer->get_scripts()
		);

		$this->assertEquals( $expected, $scripts );
	}
}
