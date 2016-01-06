<?php

class AMP_Audio_Converter_Test extends WP_UnitTestCase {
	public function get_data() {
		return array(
			'no_audios' => array(
				'<p>Lorem Ipsum Demet Delorit.</p>',
				'<p>Lorem Ipsum Demet Delorit.</p>',
			),

			'simple_audio' => array(
				'<audio width="400" height="300" src="https://developer.mozilla.org/@api/deki/files/2926/=AudioTest_(1).ogg"></audio>',
				'<amp-audio width="400" height="300" src="https://developer.mozilla.org/@api/deki/files/2926/=AudioTest_(1).ogg"></amp-audio>',
			),

			'autoplay_attribute' => array(
				'<audio width="400" height="300" src="https://developer.mozilla.org/@api/deki/files/2926/=AudioTest_(1).ogg" autoplay></audio>',
				'<amp-audio width="400" height="300" src="https://developer.mozilla.org/@api/deki/files/2926/=AudioTest_(1).ogg" autoplay="desktop tablet mobile"></amp-audio>',
			),

			'audio_with_whitelisted_attributes' => array(
				'<audio width="400" height="300" src="https://developer.mozilla.org/@api/deki/files/2926/=AudioTest_(1).ogg" class="test" loop muted></audio>',
				'<amp-audio width="400" height="300" src="https://developer.mozilla.org/@api/deki/files/2926/=AudioTest_(1).ogg" class="test" loop muted></amp-audio>',
			),

			'audio_with_blacklisted_attribute' => array(
				'<audio width="400" height="300" src="https://developer.mozilla.org/@api/deki/files/2926/=AudioTest_(1).ogg" style="border-color: red;"></audio>',
				'<amp-audio width="400" height="300" src="https://developer.mozilla.org/@api/deki/files/2926/=AudioTest_(1).ogg"></amp-audio>',
			),

			'audio_with_children' => array(
				'
<audio width="400" height="300">
	<source src="https://example.com/foo.wav" type="audio/wav">
</audio>',
				'
<amp-audio width="400" height="300">
	<source src="https://example.com/foo.wav" type="audio/wav">
</amp-audio>'
			),


			'multiple_same_audio' => array(
				'
<audio width="400" height="300">
	<source src="https://example.com/foo.wav" type="audio/wav">
</audio>
<audio width="400" height="300">
	<source src="https://example.com/foo.wav" type="audio/wav">
</audio>
<audio width="400" height="300">
	<source src="https://example.com/foo.wav" type="audio/wav">
</audio>
<audio width="400" height="300">
	<source src="https://example.com/foo.wav" type="audio/wav">
</audio>
				',
				'
<amp-audio width="400" height="300">
	<source src="https://example.com/foo.wav" type="audio/wav">
</amp-audio>
<amp-audio width="400" height="300">
	<source src="https://example.com/foo.wav" type="audio/wav">
</amp-audio>
<amp-audio width="400" height="300">
	<source src="https://example.com/foo.wav" type="audio/wav">
</amp-audio>
<amp-audio width="400" height="300">
	<source src="https://example.com/foo.wav" type="audio/wav">
</amp-audio>
				',
			),

			'multiple_different_audio' => array(
				'
<audio width="400" height="300">
	<source src="https://example.com/foo.wav" type="audio/wav">
</audio>
<audio width="400" height="300" src="https://developer.mozilla.org/@api/deki/files/2926/=AudioTest_(1).ogg"></audio>
<audio height="500" width="300">
	<source src="https://example.com/foo2.wav" type="audio/wav">
</audio>
				',
				'
<amp-audio width="400" height="300">
	<source src="https://example.com/foo.wav" type="audio/wav">
</amp-audio>
<amp-audio width="400" height="300" src="https://developer.mozilla.org/@api/deki/files/2926/=AudioTest_(1).ogg"></amp-audio>
<amp-audio height="500" width="300">
	<source src="https://example.com/foo2.wav" type="audio/wav">
</amp-audio>
				'
			),
		);
	}

	/**
	 * @dataProvider get_data
	 */
	public function test_converter( $source, $expected ) {
		$converter = new AMP_Audio_Converter( $source );
		$converted = $converter->convert();
		$this->assertEquals( $expected, $converted );
	}
}
