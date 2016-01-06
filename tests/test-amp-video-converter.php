<?php

class AMP_Video_Converter_Test extends WP_UnitTestCase {
	public function get_data() {
		return array(
			'no_videos' => array(
				'<p>Lorem Ipsum Demet Delorit.</p>',
				'<p>Lorem Ipsum Demet Delorit.</p>'
			),
			'simple_video' => array(
				'<video width="300" height="300" src="https://archive.org/download/WebmVp8Vorbis/webmvp8_512kb.mp4"></video>',
				'<amp-video width="300" height="300" src="https://archive.org/download/WebmVp8Vorbis/webmvp8_512kb.mp4"></amp-video>',
			),

			'autoplay_attribute' => array(
				'<video width="300" height="300" src="https://archive.org/download/WebmVp8Vorbis/webmvp8_512kb.mp4" autoplay></video>',
				'<amp-video width="300" height="300" src="https://archive.org/download/WebmVp8Vorbis/webmvp8_512kb.mp4" autoplay="desktop tablet mobile"></amp-video>',
			),

			'audio_with_whitelisted_attributes' => array(
				'<video width="300" height="300" src="https://archive.org/download/WebmVp8Vorbis/webmvp8_512kb.mp4" controls loop muted></video>',
				'<amp-video width="300" height="300" src="https://archive.org/download/WebmVp8Vorbis/webmvp8_512kb.mp4" controls loop muted></amp-video>',
			),

			'video_with_blacklisted_attribute' => array(
				'<video width="300" height="300" src="https://archive.org/download/WebmVp8Vorbis/webmvp8_512kb.mp4" style="border-color: red;"></video>',
				'<amp-video width="300" height="300" src="https://archive.org/download/WebmVp8Vorbis/webmvp8_512kb.mp4"></amp-video>',
			),

			'video_with_children' => array(
				'
<video width="480" height="300" poster="https://archive.org/download/WebmVp8Vorbis/webmvp8.gif">
	<source src="https://archive.org/download/WebmVp8Vorbis/webmvp8_512kb.mp4" type="video/mp4">
	<source src="https://archive.org/download/WebmVp8Vorbis/webmvp8.ogv" type="video/ogg">
</video>',
				'
<amp-video width="480" height="300" poster="https://archive.org/download/WebmVp8Vorbis/webmvp8.gif">
	<source src="https://archive.org/download/WebmVp8Vorbis/webmvp8_512kb.mp4" type="video/mp4">
	<source src="https://archive.org/download/WebmVp8Vorbis/webmvp8.ogv" type="video/ogg">
</amp-video>'
			),

			'multiple_same_video' => array(
				'
<video src="https://archive.org/download/WebmVp8Vorbis/webmvp8_512kb.mp4" width="480" height="300"></video>
<video src="https://archive.org/download/WebmVp8Vorbis/webmvp8_512kb.mp4" width="480" height="300"></video>
<video src="https://archive.org/download/WebmVp8Vorbis/webmvp8_512kb.mp4" width="480" height="300"></video>
<video src="https://archive.org/download/WebmVp8Vorbis/webmvp8_512kb.mp4" width="480" height="300"></video>
				',
				'
<amp-video src="https://archive.org/download/WebmVp8Vorbis/webmvp8_512kb.mp4" width="480" height="300"></amp-video>
<amp-video src="https://archive.org/download/WebmVp8Vorbis/webmvp8_512kb.mp4" width="480" height="300"></amp-video>
<amp-video src="https://archive.org/download/WebmVp8Vorbis/webmvp8_512kb.mp4" width="480" height="300"></amp-video>
<amp-video src="https://archive.org/download/WebmVp8Vorbis/webmvp8_512kb.mp4" width="480" height="300"></amp-video>
				'
			),

			'multiple_different_videos' => array(
				'
<video src="https://archive.org/download/WebmVp8Vorbis/webmvp8_512kb.mp4" width="480" height="300"></video>
<video src="https://archive.org/download/WebmVp8Vorbis/webmvp8.ogv" width="300" height="480"></video>
<video src="https://archive.org/download/WebmVp8Vorbis/webmvp8.webm" height="100" width="200"></video>
				',
				'
<amp-video src="https://archive.org/download/WebmVp8Vorbis/webmvp8_512kb.mp4" width="480" height="300"></amp-video>
<amp-video src="https://archive.org/download/WebmVp8Vorbis/webmvp8.ogv" width="300" height="480"></amp-video>
<amp-video src="https://archive.org/download/WebmVp8Vorbis/webmvp8.webm" height="100" width="200"></amp-video>
				'
			)
		);
	}

	/**
	 * @dataProvider get_data
	 */
	public function test_converter( $source, $expected ) {
		$converter = new AMP_Video_Converter( $source );
		$converted = $converter->convert();
		$this->assertEquals( $expected, $converted );
	}
}
