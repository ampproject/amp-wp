<?php
/**
 * Class AMP_Audio_Converter_Test.
 *
 * @package AMP
 */

// phpcs:disable WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned

/**
 * Class AMP_Audio_Converter_Test
 *
 * This is here because PhpStorm cannot find them because of phpunit6-compat.php
 *
 * @method void assertEquals( mixed $expected, mixed $actual, string $errorMessage=null )
 */
class AMP_Audio_Converter_Test extends WP_UnitTestCase {
	public function get_data() {
		return array(
			'no_audios' => array(
				'<p>Lorem Ipsum Demet Delorit.</p>',
				'<p>Lorem Ipsum Demet Delorit.</p>',
			),

			'simple_audio' => array(
				'<audio width="400" height="300" src="https://example.com/audio/file.ogg"></audio>',
				'<amp-audio width="400" height="300" src="https://example.com/audio/file.ogg"></amp-audio>',
			),

			'autoplay_attribute' => array(
				'<audio width="400" height="300" src="https://example.com/audio/file.ogg" autoplay></audio>',
				'<amp-audio width="400" height="300" src="https://example.com/audio/file.ogg" autoplay=""></amp-audio>',
			),

			'autoplay_attribute__false' => array(
				'<audio width="400" height="300" src="https://example.com/audio/file.ogg" autoplay="false"></audio>',
				'<amp-audio width="400" height="300" src="https://example.com/audio/file.ogg"></amp-audio>',
			),

			'audio_with_whitelisted_attributes__enabled' => array(
				'<audio width="400" height="300" src="https://example.com/audio/file.ogg" class="test" loop="loop" muted></audio>',
				'<amp-audio width="400" height="300" src="https://example.com/audio/file.ogg" class="test" loop="" muted=""></amp-audio>',
			),

			'audio_with_whitelisted_attributes__disabled' => array(
				'<audio width="400" height="300" src="https://example.com/audio/file.ogg" class="test" loop="false" muted="false"></audio>',
				'<amp-audio width="400" height="300" src="https://example.com/audio/file.ogg" class="test"></amp-audio>',
			),

			'audio_with_blacklisted_attribute' => array(
				'<audio width="400" height="300" src="https://example.com/audio/file.ogg" style="border-color: red;"></audio>',
				'<amp-audio width="400" height="300" src="https://example.com/audio/file.ogg"></amp-audio>',
			),

			'audio_with_children' => array(
				'<audio width="400" height="300">
	<source src="https://example.com/foo.wav" type="audio/wav">
</audio>',
				'<amp-audio width="400" height="300"><source src="https://example.com/foo.wav" type="audio/wav"></amp-audio>',
			),

			'audio_with_layout_from_editor_fixed_height' => array(
				'<figure data-amp-layout="fixed-height"><audio src="https://example.com/audio/file.ogg" width="100" height="100"></audio></figure>',
				'<figure data-amp-layout="fixed-height"><amp-audio src="https://example.com/audio/file.ogg" width="auto" height="100" layout="fixed-height"></amp-audio></figure>',
			),

			'multiple_same_audio' => array(
				'<audio width="400" height="300">
	<source src="https://example.com/foo.wav" type="audio/wav">
</audio>
<audio width="400" height="300">
	<source src="https://example.com/foo.wav" type="audio/wav">
</audio>
<audio width="400" height="300">
	<source src="https://example.com/foo.wav" type="audio/wav">
</audio>',
				'<amp-audio width="400" height="300"><source src="https://example.com/foo.wav" type="audio/wav"></amp-audio><amp-audio width="400" height="300"><source src="https://example.com/foo.wav" type="audio/wav"></amp-audio><amp-audio width="400" height="300"><source src="https://example.com/foo.wav" type="audio/wav"></amp-audio>',
			),

			'multiple_different_audio' => array(
				'<audio width="400" height="300">
	<source src="https://example.com/foo.wav" type="audio/wav">
</audio>
<audio width="400" height="300" src="https://example.com/audio/file.ogg"></audio>
<audio height="500" width="300">
	<source src="https://example.com/foo2.wav" type="audio/wav">
</audio>',
				'<amp-audio width="400" height="300"><source src="https://example.com/foo.wav" type="audio/wav"></amp-audio><amp-audio width="400" height="300" src="https://example.com/audio/file.ogg"></amp-audio><amp-audio height="500" width="300"><source src="https://example.com/foo2.wav" type="audio/wav"></amp-audio>',
			),

			'https_not_required' => array(
				'<audio width="400" height="300" src="http://example.com/audio/file.ogg"></audio>',
				'<amp-audio width="400" height="300" src="http://example.com/audio/file.ogg"></amp-audio>',
			),
		);
	}

	/**
	 * @dataProvider get_data
	 */
	public function test_converter( $source, $expected ) {
		$dom = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Audio_Sanitizer( $dom );
		$sanitizer->sanitize();
		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$content = preg_replace( '/(?<=>)\s+(?=<)/', '', $content );
		$this->assertEquals( $expected, $content );
	}

	public function test__https_required() {
		$source = '<audio width="400" height="300" src="http://example.com/audio/file.ogg"></audio>';
		$expected = '';

		$dom = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Audio_Sanitizer( $dom, array(
			'require_https_src' => true,
		) );
		$sanitizer->sanitize();

		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$this->assertEquals( $expected, $content );
	}

	public function test_get_scripts__didnt_convert() {
		$source = '<p>Hello World</p>';
		$expected = array();

		$dom = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Audio_Sanitizer( $dom );
		$sanitizer->sanitize();

		$whitelist_sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$whitelist_sanitizer->sanitize();

		$scripts = array_merge(
			$sanitizer->get_scripts(),
			$whitelist_sanitizer->get_scripts()
		);
		$this->assertEquals( $expected, $scripts );
	}

	public function test_get_scripts__did_convert() {
		$source = '<audio width="400" height="300" src="https://example.com/audio/file.ogg"></audio>';
		$expected = array( 'amp-audio' => true );

		$dom = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Audio_Sanitizer( $dom );
		$sanitizer->sanitize();

		$whitelist_sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$whitelist_sanitizer->sanitize();

		$scripts = array_merge(
			$sanitizer->get_scripts(),
			$whitelist_sanitizer->get_scripts()
		);
		$this->assertEquals( $expected, $scripts );
	}
}
