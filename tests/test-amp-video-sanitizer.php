<?php

// phpcs:disable WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned

class AMP_Video_Converter_Test extends WP_UnitTestCase {
	public function get_data() {
		return array(
			'no_videos' => array(
				'<p>Lorem Ipsum Demet Delorit.</p>',
				'<p>Lorem Ipsum Demet Delorit.</p>',
			),

			'simple_video' => array(
				'<video width="300" height="300" src="https://example.com/video.mp4"></video>',
				'<amp-video width="300" height="300" src="https://example.com/video.mp4" layout="responsive"></amp-video>',
			),

			'video_without_dimensions' => array(
				'<video src="https://example.com/file.mp4"></video>',
				'<amp-video src="https://example.com/file.mp4" height="400" layout="fixed-height"></amp-video>',
			),

			'autoplay_attribute' => array(
				'<video width="300" height="300" src="https://example.com/video.mp4" autoplay></video>',
				'<amp-video width="300" height="300" src="https://example.com/video.mp4" autoplay="" layout="responsive"></amp-video>',
			),

			'autoplay_attribute__false' => array(
				'<video width="300" height="300" src="https://example.com/video.mp4" autoplay="false"></video>',
				'<amp-video width="300" height="300" src="https://example.com/video.mp4" layout="responsive"></amp-video>',
			),

			'video_with_whitelisted_attributes__enabled' => array(
				'<video width="300" height="300" src="https://example.com/video.mp4" controls loop="true" muted="muted"></video>',
				'<amp-video width="300" height="300" src="https://example.com/video.mp4" controls="" loop="" muted="" layout="responsive"></amp-video>',
			),

			'video_with_whitelisted_attributes__disabled' => array(
				'<video width="300" height="300" src="https://example.com/video.mp4" controls="false" loop="false" muted="false"></video>',
				'<amp-video width="300" height="300" src="https://example.com/video.mp4" layout="responsive"></amp-video>',
			),

			'video_with_blacklisted_attribute' => array(
				'<video width="300" height="300" src="https://example.com/video.mp4" style="border-color: red;"></video>',
				'<amp-video width="300" height="300" src="https://example.com/video.mp4" layout="responsive"></amp-video>',
			),

			'video_with_sizes_attribute_is_overridden' => array(
				'<video width="300" height="200" src="https://example.com/file.mp4"></video>',
				'<amp-video width="300" height="200" src="https://example.com/file.mp4" layout="responsive"></amp-video>',
			),

			'video_with_children' => array(
				'<video width="480" height="300" poster="https://example.com/video-image.gif">
	<source src="https://example.com/video.mp4" type="video/mp4">
	<source src="https://example.com/video.ogv" type="video/ogg">
</video>',
				'<amp-video width="480" height="300" poster="https://example.com/video-image.gif" layout="responsive"><source src="https://example.com/video.mp4" type="video/mp4"><source src="https://example.com/video.ogv" type="video/ogg"></amp-video>',
			),

			'video_with_layout_from_editor_fill' => array(
				'<figure data-amp-layout="fill"><video src="https://example.com/file.mp4" height="100" width="100"></video></figure>',
				'<figure data-amp-layout="fill" style="position:relative; width: 100%; height: 100px;"><amp-video src="https://example.com/file.mp4" layout="fill"></amp-video></figure>',
			),

			'video_with_layout_from_editor_fixed' => array(
				'<figure data-amp-layout="fixed"><video src="https://example.com/file.mp4" width="100"></video></figure>',
				'<figure data-amp-layout="fixed"><amp-video src="https://example.com/file.mp4" width="100" layout="fixed" height="400"></amp-video></figure>',
			),

			'video_with_noloading_from_editor' => array(
				'<figure data-amp-noloading="true"><video src="https://example.com/file.mp4" height="100" width="100"></video></figure>',
				'<figure data-amp-noloading="true"><amp-video src="https://example.com/file.mp4" height="100" width="100" noloading="" layout="responsive"></amp-video></figure>',
			),

			'multiple_same_video' => array(
				'<video src="https://example.com/video.mp4" width="480" height="300"></video>
<video src="https://example.com/video.mp4" width="480" height="300"></video>
<video src="https://example.com/video.mp4" width="480" height="300"></video>
<video src="https://example.com/video.mp4" width="480" height="300"></video>',
				'<amp-video src="https://example.com/video.mp4" width="480" height="300" layout="responsive"></amp-video><amp-video src="https://example.com/video.mp4" width="480" height="300" layout="responsive"></amp-video><amp-video src="https://example.com/video.mp4" width="480" height="300" layout="responsive"></amp-video><amp-video src="https://example.com/video.mp4" width="480" height="300" layout="responsive"></amp-video>',
			),

			'multiple_different_videos' => array(
				'<video src="https://example.com/video1.mp4" width="480" height="300"></video>
<video src="https://example.com/video2.ogv" width="300" height="480"></video>
<video src="https://example.com/video3.webm" height="100" width="200"></video>',
				'<amp-video src="https://example.com/video1.mp4" width="480" height="300" layout="responsive"></amp-video><amp-video src="https://example.com/video2.ogv" width="300" height="480" layout="responsive"></amp-video><amp-video src="https://example.com/video3.webm" height="100" width="200" layout="responsive"></amp-video>',
			),

			'https_not_required' => array(
				'<video width="300" height="300" src="http://example.com/video.mp4"></video>',
				'<amp-video width="300" height="300" src="http://example.com/video.mp4" layout="responsive"></amp-video>',
			),
		);
	}

	/**
	 * @dataProvider get_data
	 */
	public function test_converter( $source, $expected ) {
		$dom = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Video_Sanitizer( $dom );
		$sanitizer->sanitize();
		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$this->assertEquals( $expected, $content );
	}

	public function test__https_required() {
		$source = '<video width="300" height="300" src="http://example.com/video.mp4"></video>';
		$expected = '';

		$dom = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Video_Sanitizer( $dom, array(
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
		$sanitizer = new AMP_Video_Sanitizer( $dom );
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
		$source = '<video width="300" height="300" src="https://example.com/video.mp4"></video>';
		$expected = array( 'amp-video' => true );

		$dom = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Video_Sanitizer( $dom );
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
