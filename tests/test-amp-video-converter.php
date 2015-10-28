<?php

class AMP_Video_Converter_Test extends WP_UnitTestCase {
	function test_no_videos() {
		$content = '<p>Lorem Ipsum Demet Delorit.</p>';
		$converter = new AMP_Video_Converter( $content );
		$converted = $converter->convert();
		$this->assertEquals( $content, $converted );
	}

	function test_simple_video() {
		$content = '<video width="480" height="300" controls poster="https://archive.org/download/WebmVp8Vorbis/webmvp8.gif">
  <source src="https://archive.org/download/WebmVp8Vorbis/webmvp8_512kb.mp4" type="video/mp4">
  <source src="https://archive.org/download/WebmVp8Vorbis/webmvp8.ogv" type="video/ogg">
</video>';
		$expected = '<amp-video width="480" height="300" controls poster="https://archive.org/download/WebmVp8Vorbis/webmvp8.gif">
  <source src="https://archive.org/download/WebmVp8Vorbis/webmvp8_512kb.mp4" type="video/mp4">
  <source src="https://archive.org/download/WebmVp8Vorbis/webmvp8.ogv" type="video/ogg">
</amp-video>';

		$converter = new AMP_Video_Converter( $content );
		$converted = $converter->convert();
		$this->assertEquals( $expected, $converted );
	}

	function test_video_with_blacklisted_attribute() {
		$content = '<video src="https://archive.org/download/WebmVp8Vorbis/webmvp8_512kb.mp4" style="border-color: red;"></video>';
		$expected = '<amp-video src="https://archive.org/download/WebmVp8Vorbis/webmvp8_512kb.mp4"></amp-video>';

		$converter = new AMP_Video_Converter( $content );
		$converted = $converter->convert();
		$this->assertEquals( $expected, $converted );
	}

	function test_multiple_same_video() {
		$content = '
<video src="https://archive.org/download/WebmVp8Vorbis/webmvp8_512kb.mp4" width="480" height="300"></video>
<video src="https://archive.org/download/WebmVp8Vorbis/webmvp8_512kb.mp4" width="480" height="300"></video>
<video src="https://archive.org/download/WebmVp8Vorbis/webmvp8_512kb.mp4" width="480" height="300"></video>
<video src="https://archive.org/download/WebmVp8Vorbis/webmvp8_512kb.mp4" width="480" height="300"></video>
		';
		$expected = '
<amp-video src="https://archive.org/download/WebmVp8Vorbis/webmvp8_512kb.mp4" width="480" height="300"></amp-video>
<amp-video src="https://archive.org/download/WebmVp8Vorbis/webmvp8_512kb.mp4" width="480" height="300"></amp-video>
<amp-video src="https://archive.org/download/WebmVp8Vorbis/webmvp8_512kb.mp4" width="480" height="300"></amp-video>
<amp-video src="https://archive.org/download/WebmVp8Vorbis/webmvp8_512kb.mp4" width="480" height="300"></amp-video>
		';

		$converter = new AMP_Video_Converter( $content );
		$converted = $converter->convert();
		$this->assertEquals( $expected, $converted );
	}

	function test_multiple_different_videos() {
		$content = '
<video src="https://archive.org/download/WebmVp8Vorbis/webmvp8_512kb.mp4" width="480" height="300"></video>
<video src="https://archive.org/download/WebmVp8Vorbis/webmvp8.ogv" width="300" height="480"></video>
<video src="https://archive.org/download/WebmVp8Vorbis/webmvp8.webm" height="100" width="200"></video>
		';
		$expected = '
<amp-video src="https://archive.org/download/WebmVp8Vorbis/webmvp8_512kb.mp4" width="480" height="300"></amp-video>
<amp-video src="https://archive.org/download/WebmVp8Vorbis/webmvp8.ogv" width="300" height="480"></amp-video>
<amp-video src="https://archive.org/download/WebmVp8Vorbis/webmvp8.webm" height="100" width="200"></amp-video>
		';

		$converter = new AMP_Video_Converter( $content );
		$converted = $converter->convert();
		$this->assertEquals( $expected, $converted );
	}
}
