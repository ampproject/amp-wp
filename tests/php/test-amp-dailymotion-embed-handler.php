<?php

class AMP_DailyMotion_Embed_Handler_Test extends WP_UnitTestCase {

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
		$embed->register_embed();

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
