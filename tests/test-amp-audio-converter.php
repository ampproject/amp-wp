<?php

class AMP_Audio_Converter_Test extends WP_UnitTestCase {
	function test_no_audios() {
		$content = '<p>Lorem Ipsum Demet Delorit.</p>';
		$converter = new AMP_Audio_Converter( $content );
		$converted = $converter->convert();
		$this->assertEquals( $content, $converted );
	}

	function test_simple_audio() {
		$content = '<audio width="400" height="300" src="https://developer.mozilla.org/@api/deki/files/2926/=AudioTest_(1).ogg"></audio>';
		$expected = '<amp-audio width="400" height="300" src="https://developer.mozilla.org/@api/deki/files/2926/=AudioTest_(1).ogg"></amp-audio>';

		$converter = new AMP_Audio_Converter( $content );
		$converted = $converter->convert();
		$this->assertEquals( $expected, $converted );
	}

	function test_autoplay_attribute() {
		$content = '<audio width="400" height="300" src="https://developer.mozilla.org/@api/deki/files/2926/=AudioTest_(1).ogg" autoplay></audio>';
		$expected = '<amp-audio width="400" height="300" src="https://developer.mozilla.org/@api/deki/files/2926/=AudioTest_(1).ogg" autoplay="desktop tablet mobile"></amp-audio>';

		$converter = new AMP_Audio_Converter( $content );
		$converted = $converter->convert();
		$this->assertEquals( $expected, $converted );
	}

	function test_audio_with_whitelisted_attributes() {
		$content = '<audio width="400" height="300" src="https://developer.mozilla.org/@api/deki/files/2926/=AudioTest_(1).ogg" class="test" loop muted></audio>';
		$expected = '<amp-audio width="400" height="300" src="https://developer.mozilla.org/@api/deki/files/2926/=AudioTest_(1).ogg" class="test" loop muted></amp-audio>';

		$converter = new AMP_Audio_Converter( $content );
		$converted = $converter->convert();
		$this->assertEquals( $expected, $converted );
	}

	function test_audio_with_blacklisted_attribute() {
		$content = '<audio width="400" height="300" src="https://developer.mozilla.org/@api/deki/files/2926/=AudioTest_(1).ogg" style="border-color: red;"></audio>';
		$expected = '<amp-audio width="400" height="300" src="https://developer.mozilla.org/@api/deki/files/2926/=AudioTest_(1).ogg"></amp-audio>';

		$converter = new AMP_Audio_Converter( $content );
		$converted = $converter->convert();
		$this->assertEquals( $expected, $converted );
	}

	function test_audio_with_children() {
		$content = '
<audio width="400" height="300">
  <source src="https://example.com/foo.wav" type="audio/wav">
</audio>';
		$expected = '
<amp-audio width="400" height="300">
  <source src="https://example.com/foo.wav" type="audio/wav">
</amp-audio>';

		$converter = new AMP_Audio_Converter( $content );
		$converted = $converter->convert();
		$this->assertEquals( $expected, $converted );
	}


	function test_multiple_same_audio() {
		$content = '
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
		';
		$expected = '
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
		';

		$converter = new AMP_Audio_Converter( $content );
		$converted = $converter->convert();
		$this->assertEquals( $expected, $converted );
	}

	function test_multiple_different_audio() {
		$content = '
<audio width="400" height="300">
  <source src="https://example.com/foo.wav" type="audio/wav">
</audio>
<audio width="400" height="300" src="https://developer.mozilla.org/@api/deki/files/2926/=AudioTest_(1).ogg"></audio>
<audio height="500" width="300">
  <source src="https://example.com/foo2.wav" type="audio/wav">
</audio>
		';
		$expected = '
<amp-audio width="400" height="300">
  <source src="https://example.com/foo.wav" type="audio/wav">
</amp-audio>
<amp-audio width="400" height="300" src="https://developer.mozilla.org/@api/deki/files/2926/=AudioTest_(1).ogg"></amp-audio>
<amp-audio height="500" width="300">
  <source src="https://example.com/foo2.wav" type="audio/wav">
</amp-audio>
		';

		$converter = new AMP_Audio_Converter( $content );
		$converted = $converter->convert();
		$this->assertEquals( $expected, $converted );
	}
}
