<?php

class AMP_DailyMotion_Embed_Handler_Test extends WP_UnitTestCase {

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
	public function mock_http_request( $pre, $r, $url ) {
		if ( in_array( 'external-http', $_SERVER['argv'], true ) ) {
			return $pre;
		}

		if ( false === strpos( $url, 'dailymotion.com' ) ) {
			return $pre;
		}

		$body = '{"type":"video","version":"1.0","provider_name":"Dailymotion","provider_url":"https:\/\/www.dailymotion.com","title":"Snatched - Official Trailer 2 (HD)","description":"M\u00e1s info http:\/\/trailersyestrenos.es\/snatched-jonathan-levine\/ - TWITTER: https:\/\/twitter.com\/TrailersyEstren - FACEBOOK: https:\/\/www.facebook.com\/trailersyestrenos - GOOGLE+: https:\/\/www.google.com\/+TrailersyEstrenos  Sinopsis: Una madre y una hija se enfrentar\u00e1n a distintos problemas que surgen mientras est\u00e1n de vacaciones  Director: Jonathan Levine Reparto: Amy Schumer, Ike Barinholtz, Goldie Hawn, Christopher Meloni, Randall Park, Wanda Sykes, \u00d3scar Jaenada, Colin Quinn, Tom Bateman, Kevin Kane, Sharon M. Bell  (El trailer pertenece a la productora y distribuidora de la pel\u00edcula y ha sido subido sin \u00e1nimo de lucro)","author_name":"Trailers y Estrenos","author_url":"https:\/\/www.dailymotion.com\/TrailersyEstrenos","width":480,"height":204,"html":"<iframe frameborder=\"0\" width=\"480\" height=\"204\" src=\"https:\/\/www.dailymotion.com\/embed\/video\/x5awwth\" allowfullscreen allow=\"autoplay\"><\/iframe>","thumbnail_url":"https:\/\/s2.dmcdn.net\/v\/J7Emb1Ungw3_0rEtU\/x120","thumbnail_width":214,"thumbnail_height":120}';

		return [
			'body'     => $body,
			'response' => [
				'code'    => 200,
				'message' => 'OK',
			],
		];
	}

	public function get_conversion_data() {
		return [
			'no_embed'       => [
				'<p>Hello world.</p>',
				'<p>Hello world.</p>' . PHP_EOL,
			],

			'url_simple'     => [
				'https://www.dailymotion.com/video/x5awwth' . PHP_EOL,
				'<amp-dailymotion data-videoid="x5awwth" layout="responsive" width="500" height="212"></amp-dailymotion>' . PHP_EOL,
			],

			'url_with_title' => [
				'http://www.dailymotion.com/video/x5awwth_snatched-official-trailer-2-hd_shortfilms' . PHP_EOL,
				'<amp-dailymotion data-videoid="x5awwth" layout="responsive" width="500" height="212"></amp-dailymotion>' . PHP_EOL,
			],
		];
	}

	/**
	 * @dataProvider get_conversion_data
	 */
	public function test__conversion( $source, $expected ) {
		$embed = new AMP_DailyMotion_Embed_Handler();

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
				'https://www.dailymotion.com/video/x5awwth' . PHP_EOL,
				[ 'amp-dailymotion' => true ],
			],
		];
	}

	/**
	 * @dataProvider get_scripts_data
	 */
	public function test__get_scripts( $source, $expected ) {
		$embed = new AMP_DailyMotion_Embed_Handler();

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
