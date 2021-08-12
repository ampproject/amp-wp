<?php

use AmpProject\AmpWP\Tests\Helpers\WithoutBlockPreRendering;
use Yoast\WPTestUtils\WPIntegration\TestCase;

class AMP_DailyMotion_Embed_Handler_Test extends TestCase {

	use WithoutBlockPreRendering;

	public function get_conversion_data() {
		return [
			'no_embed'       => [
				'<p>Hello world.</p>',
				'<p>Hello world.</p>' . PHP_EOL,
			],

			'url_simple'     => [
				'https://www.dailymotion.com/video/x5awwth' . PHP_EOL,
				'<p><amp-dailymotion data-videoid="x5awwth" layout="responsive" width="600" height="338"></amp-dailymotion></p>' . PHP_EOL,
			],

			'url_with_title' => [
				'http://www.dailymotion.com/video/x5awwth_snatched-official-trailer-2-hd_shortfilms' . PHP_EOL,
				'<p><amp-dailymotion data-videoid="x5awwth" layout="responsive" width="600" height="338"></amp-dailymotion></p>' . PHP_EOL,
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

		$this->assertEquals( $expected, $filtered_content );
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
