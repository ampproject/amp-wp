<?php

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
				'<amp-audio width="400" height="300" src="https://example.com/audio/file.ogg" autoplay="desktop tablet mobile"></amp-audio>',
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
				'<amp-audio width="400" height="300"><source src="https://example.com/foo.wav" type="audio/wav"></source></amp-audio>'
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
				'<amp-audio width="400" height="300"><source src="https://example.com/foo.wav" type="audio/wav"></source></amp-audio><amp-audio width="400" height="300"><source src="https://example.com/foo.wav" type="audio/wav"></source></amp-audio><amp-audio width="400" height="300"><source src="https://example.com/foo.wav" type="audio/wav"></source></amp-audio>',
			),

			'multiple_different_audio' => array(
				'<audio width="400" height="300">
	<source src="https://example.com/foo.wav" type="audio/wav">
</audio>
<audio width="400" height="300" src="https://example.com/audio/file.ogg"></audio>
<audio height="500" width="300">
	<source src="https://example.com/foo2.wav" type="audio/wav">
</audio>',
				'<amp-audio width="400" height="300"><source src="https://example.com/foo.wav" type="audio/wav"></source></amp-audio><amp-audio width="400" height="300" src="https://example.com/audio/file.ogg"></amp-audio><amp-audio height="500" width="300"><source src="https://example.com/foo2.wav" type="audio/wav"></source></amp-audio>'
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
		$this->assertEquals( $expected, $content );
	}

	public function test_get_scripts__didnt_convert() {
		$source = '<p>Hello World</p>';
		$expected = array();

		$dom = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Audio_Sanitizer( $dom );
		$sanitizer->sanitize();

		$scripts = $sanitizer->get_scripts();
		$this->assertEquals( $expected, $scripts );
	}

	public function test_get_scripts__did_convert() {
		$source = '<audio width="400" height="300" src="https://example.com/audio/file.ogg"></audio>';
		$expected = array( 'amp-audio' => 'https://cdn.ampproject.org/v0/amp-audio-0.1.js' );

		$dom = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Audio_Sanitizer( $dom );
		$sanitizer->sanitize();
		$scripts = $sanitizer->get_scripts();

		$this->assertEquals( $expected, $scripts );
	}
}
