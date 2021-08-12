<?php
/**
 * Test AMP AMP_O2_Player_Sanitizer.
 *
 * @package AMP
 */

use Yoast\WPTestUtils\WPIntegration\TestCase;

/**
 * Test AMP_O2_Player_Sanitizer
 *
 * @covers AMP_O2_Player_Sanitizer
 */
class AMP_O2_Player_Sanitizer_Test extends TestCase {

	/**
	 * Data for converter test.
	 *
	 * @return array Data.
	 */
	public function get_data() {
		return [
			'no_o2_player_item'                      => [
				'<h1>Im Not A O2 Player</h1>',
				'<h1>Im Not A O2 Player</h1>',
			],
			'o2_player_item_without_script'          => [
				'<div class="vdb_player"></div>',
				'<div class="vdb_player"></div>',
			],

			'o2_player_item'                         => [
				'<div class="vdb_player"><script type="text/javascript" src="//delivery.vidible.tv/jsonp/pid=59521379f3bdc970c5c9d75e/vid=5b11a50d0239e257abfdf16a/59521191e9399f3a7d7de88f.js?m.embeded=cms_video_plugin_chromeExtension"></script></div>',// phpcs:ignore
				'<amp-o2-player data-pid="59521379f3bdc970c5c9d75e" data-vid="5b11a50d0239e257abfdf16a" data-bcid="59521191e9399f3a7d7de88f" data-macros="m.playback=click" layout="responsive" width="480" height="270"></amp-o2-player>',
			],

			'o2_player_item_without_required_fields' => [
				'<div class="vdb_player"><script type="text/javascript" src="//delivery.vidible.tv/jsonp/vid=5b11a50d0239e257abfdf16a/59521191e9399f3a7d7de88f.js?m.embeded=cms_video_plugin_chromeExtension"></script></div>',// phpcs:ignore
				'<div class="vdb_player"><script type="text/javascript" src="//delivery.vidible.tv/jsonp/vid=5b11a50d0239e257abfdf16a/59521191e9399f3a7d7de88f.js?m.embeded=cms_video_plugin_chromeExtension"></script></div>',// phpcs:ignore
			],

		];
	}

	/**
	 * Dataset to test amp-o2-player sanitizer.
	 *
	 * @param string $source  Content.
	 * @param string $expected Expected content.
	 *
	 * @dataProvider get_data
	 * @covers AMP_O2_Player_Sanitizer::sanitize()
	 * @covers AMP_O2_Player_Sanitizer::create_amp_o2_player()
	 * @covers AMP_O2_Player_Sanitizer::get_o2_player_attributes()
	 */
	public function test_converter( $source, $expected ) {
		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_O2_Player_Sanitizer( $dom );

		$sanitizer->sanitize();

		$content = AMP_DOM_Utils::get_content_from_dom( $dom );

		$this->assertEquals( $expected, $content );
	}

	/**
	 * Tests get script when required attributes missing.
	 */
	public function test_get_script__pid__vid__required() {
		$source   = '<div class="vdb_player"><script type="text/javascript" src="//delivery.vidible.tv/jsonp/pid=59521379f3bdc970c5c9d75e?m.embeded=cms_video_plugin_chromeExtension"></script></div>';// phpcs:ignore
		$expected = [];

		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_O2_Player_Sanitizer( $dom );
		$sanitizer->sanitize();

		$validating_sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$validating_sanitizer->sanitize();

		$scripts = array_merge(
			$sanitizer->get_scripts(),
			$validating_sanitizer->get_scripts()
		);
		$this->assertEquals( $expected, $scripts );
	}

	/**
	 * Test that get_scripts() did convert.
	 */
	public function test_get_scripts__did_convert() {
		$source   = '<div class="vdb_player"><script type="text/javascript" src="//delivery.vidible.tv/jsonp/pid=59521379f3bdc970c5c9d75e/vid=5b11a50d0239e257abfdf16a/59521191e9399f3a7d7de88f.js?m.embeded=cms_video_plugin_chromeExtension"></script></div>';// phpcs:ignore
		$expected = [ 'amp-o2-player' => true ];

		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_O2_Player_Sanitizer( $dom );
		$sanitizer->sanitize();
		$validating_sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$validating_sanitizer->sanitize();

		$scripts = array_merge(
			$sanitizer->get_scripts(),
			$validating_sanitizer->get_scripts()
		);
		$this->assertEquals( $expected, $scripts );
	}
}
