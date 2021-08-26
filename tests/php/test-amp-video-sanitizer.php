<?php
/**
 * Class AMP_Video_Converter_Test.
 *
 * @package AMP
 */

use AmpProject\AmpWP\Tests\Helpers\MarkupComparison;
use AmpProject\AmpWP\Tests\TestCase;

// phpcs:disable WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned

/**
 * Class AMP_Video_Converter_Test
 *
 * @covers AMP_Video_Sanitizer
 */
class AMP_Video_Converter_Test extends TestCase {

	use MarkupComparison;

	/**
	 * Tear down.
	 */
	public function tearDown() {
		if ( did_action( 'add_attachment' ) ) {
			$this->remove_added_uploads();
		}
		parent::tearDown();
	}

	/**
	 * Get data.
	 *
	 * @return array
	 */
	public function get_data() {
		return [
			'no_videos' => [
				'<p>Lorem Ipsum Demet Delorit.</p>',
				'<p>Lorem Ipsum Demet Delorit.</p>',
			],

			'simple_video' => [
				'<video width="300" height="300" src="https://example.com/video.mp4" playsinline webkit-playsinline></video>',
				'<amp-video width="300" height="300" src="https://example.com/video.mp4" layout="responsive"><a href="https://example.com/video.mp4" fallback="">https://example.com/video.mp4</a><noscript><video width="300" height="300" src="https://example.com/video.mp4" playsinline></video></noscript></amp-video>',
				[
					'add_noscript_fallback' => true,
				],
			],

			'simple_video_without_noscript' => [
				'<video width="300" height="300" src="https://example.com/video.mp4"></video>',
				'<amp-video width="300" height="300" src="https://example.com/video.mp4" layout="responsive"><a href="https://example.com/video.mp4" fallback="">https://example.com/video.mp4</a></amp-video>',
				[
					'add_noscript_fallback' => false,
				],
			],

			'video_without_dimensions' => [
				'<video src="https://example.com/file.mp4"></video>',
				'<amp-video src="https://example.com/file.mp4" height="400" layout="fixed-height" width="auto"><a href="https://example.com/file.mp4" fallback="">https://example.com/file.mp4</a><noscript><video src="https://example.com/file.mp4"></video></noscript></amp-video>',
			],

			'video_with_autoplay' => [
				'<video src="https://example.com/file.mp4" autoplay="true" muted="muted"></video>',
				'<amp-video src="https://example.com/file.mp4" autoplay height="400" layout="fixed-height" width="auto"><a href="https://example.com/file.mp4" fallback="">https://example.com/file.mp4</a><noscript><video src="https://example.com/file.mp4" autoplay="true"></video></noscript></amp-video>',
			],

			'local_video_without_dimensions' => [
				sprintf( '<video src="%s"></video>', '{{video_url}}' ),
				sprintf( '<amp-video src="%1$s" width="560" height="320" layout="responsive"><a href="%1$s" fallback="">%1$s</a><noscript><video src="%1$s"></video></noscript></amp-video>', '{{video_url}}' ),
			],

			'local_video_without_dimensions_and_with_data_layout' => [
				sprintf( '<video src="%s" data-amp-layout="fixed"></video>', '{{video_url}}' ),
				sprintf( '<amp-video src="%1$s" layout="fixed" width="560" height="320"><a href="%1$s" fallback="">%1$s</a><noscript><video src="%1$s"></video></noscript></amp-video>', '{{video_url}}' ),
			],

			'local_video_without_dimensions_and_with_fixed_height_layout' => [
				sprintf( '<video src="%s" layout="fixed-height"></video>', '{{video_url}}' ),
				sprintf( '<amp-video src="%1$s" layout="fixed-height" height="320"><a href="%1$s" fallback="">%1$s</a><noscript><video src="%1$s"></video></noscript></amp-video>', '{{video_url}}' ),
			],

			'layout_fill_video_without_dimensions' => [
				'<video src="https://example.com/file.mp4" layout="fill"></video>',
				'<amp-video src="https://example.com/file.mp4" layout="fill"><a href="https://example.com/file.mp4" fallback="">https://example.com/file.mp4</a><noscript><video src="https://example.com/file.mp4"></video></noscript></amp-video>',
			],

			'data_layout_fill_video_without_dimensions' => [
				'<video src="https://example.com/file.mp4" data-amp-layout="fill"></video>',
				'<amp-video src="https://example.com/file.mp4" layout="fill"><a href="https://example.com/file.mp4" fallback="">https://example.com/file.mp4</a><noscript><video src="https://example.com/file.mp4"></video></noscript></amp-video>',
			],

			'layout_nodisplay_video_without_dimensions' => [
				'<video src="https://example.com/file.mp4" layout="nodisplay"></video>',
				'<amp-video src="https://example.com/file.mp4" layout="nodisplay"><a href="https://example.com/file.mp4" fallback="">https://example.com/file.mp4</a><noscript><video src="https://example.com/file.mp4"></video></noscript></amp-video>',
			],

			'layout_fixed_video_without_dimensions' => [
				'<video src="https://example.com/file.mp4" layout="nodisplay"></video>',
				'<amp-video src="https://example.com/file.mp4" layout="nodisplay"><a href="https://example.com/file.mp4" fallback="">https://example.com/file.mp4</a><noscript><video src="https://example.com/file.mp4"></video></noscript></amp-video>',
			],

			'autoplay_attribute' => [
				'<video width="300" height="300" src="https://example.com/video.mp4" autoplay></video>',
				'<amp-video width="300" height="300" src="https://example.com/video.mp4" autoplay="" layout="responsive"><a href="https://example.com/video.mp4" fallback="">https://example.com/video.mp4</a><noscript><video width="300" height="300" src="https://example.com/video.mp4" autoplay></video></noscript></amp-video>',
			],

			'autoplay_attribute__false' => [
				'<video width="300" height="300" src="https://example.com/video.mp4" autoplay="false"></video>',
				'<amp-video width="300" height="300" src="https://example.com/video.mp4" layout="responsive"><a href="https://example.com/video.mp4" fallback="">https://example.com/video.mp4</a><noscript><video width="300" height="300" src="https://example.com/video.mp4" autoplay="false"></video></noscript></amp-video>',
			],

			'video_with_allowlisted_attributes__enabled' => [
				'<video width="300" height="300" src="https://example.com/video.mp4" controls loop="true" muted="muted"></video>',
				'<amp-video width="300" height="300" src="https://example.com/video.mp4" controls="" loop="" muted="" layout="responsive"><a href="https://example.com/video.mp4" fallback="">https://example.com/video.mp4</a><noscript><video width="300" height="300" src="https://example.com/video.mp4" controls loop="true"></video></noscript></amp-video>',
			],

			'video_with_allowlisted_attributes__disabled' => [
				'<video width="300" height="300" src="https://example.com/video.mp4" controls="false" loop="false" muted="false"></video>',
				'<amp-video width="300" height="300" src="https://example.com/video.mp4" layout="responsive"><a href="https://example.com/video.mp4" fallback="">https://example.com/video.mp4</a><noscript><video width="300" height="300" src="https://example.com/video.mp4" controls="false" loop="false"></video></noscript></amp-video>',
			],

			'video_with_custom_attribute' => [
				'<video width="300" height="300" src="https://example.com/video.mp4" onclick="foo()" data-foo="bar"></video>',
				'<amp-video width="300" height="300" src="https://example.com/video.mp4" data-foo="bar" layout="responsive"><a href="https://example.com/video.mp4" fallback="">https://example.com/video.mp4</a><noscript><video width="300" height="300" src="https://example.com/video.mp4"></video></noscript></amp-video>',
			],

			'video_with_sizes_attribute_is_overridden' => [
				'<video width="300" height="200" src="https://example.com/file.mp4"></video>',
				'<amp-video width="300" height="200" src="https://example.com/file.mp4" layout="responsive"><a href="https://example.com/file.mp4" fallback="">https://example.com/file.mp4</a><noscript><video width="300" height="200" src="https://example.com/file.mp4"></video></noscript></amp-video>',
			],

			'video_with_children' => [
				'
					<video width="480" height="300" poster="https://example.com/video-image.gif">
						<source src="https://example.com/video.mp4" type="video/mp4">
						<source src="https://example.com/video.ogv" type="video/ogg">
					</video>
				',
				'
					<amp-video width="480" height="300" poster="https://example.com/video-image.gif" layout="responsive">
						<source src="https://example.com/video.mp4" type="video/mp4">
						<source src="https://example.com/video.ogv" type="video/ogg">
						<a href="https://example.com/video.mp4" fallback="">https://example.com/video.mp4</a>
						<noscript>
							<video width="480" height="300" poster="https://example.com/video-image.gif">
								<source src="https://example.com/video.mp4" type="video/mp4">
								<source src="https://example.com/video.ogv" type="video/ogg">
							</video>
						</noscript>
					</amp-video>
				',
			],

			'video_with_layout_from_editor_fill' => [
				'<figure data-amp-layout="fill"><video src="https://example.com/file.mp4" height="100" width="100"></video></figure>',
				'<figure data-amp-layout="fill" style="position:relative; width: 100%; height: 100px;"><amp-video src="https://example.com/file.mp4" layout="fill"><a href="https://example.com/file.mp4" fallback="">https://example.com/file.mp4</a><noscript><video src="https://example.com/file.mp4" height="100" width="100"></video></noscript></amp-video></figure>',
			],

			'video_with_layout_from_editor_fixed' => [
				'<figure data-amp-layout="fixed"><video src="https://example.com/file.mp4" width="100"></video></figure>',
				'<figure data-amp-layout="fixed"><amp-video src="https://example.com/file.mp4" width="100" layout="fixed" height="400"><a href="https://example.com/file.mp4" fallback="">https://example.com/file.mp4</a><noscript><video src="https://example.com/file.mp4" width="100"></video></noscript></amp-video></figure>',
			],

			'video_with_noloading_from_editor' => [
				'<figure data-amp-noloading="true"><video src="https://example.com/file.mp4" height="100" width="100"></video></figure>',
				'<figure data-amp-noloading="true"><amp-video src="https://example.com/file.mp4" height="100" width="100" noloading="" layout="responsive"><a href="https://example.com/file.mp4" fallback="">https://example.com/file.mp4</a><noscript><video src="https://example.com/file.mp4" height="100" width="100"></video></noscript></amp-video></figure>',
			],

			'multiple_same_video' => [
				'
					<video src="https://example.com/video.mp4" width="480" height="300"></video>
					<video src="https://example.com/video.mp4" width="480" height="300"></video>
					<video src="https://example.com/video.mp4" width="480" height="300"></video>
					<video src="https://example.com/video.mp4" width="480" height="300"></video>
				',
				'
					<amp-video src="https://example.com/video.mp4" width="480" height="300" layout="responsive"><a href="https://example.com/video.mp4" fallback="">https://example.com/video.mp4</a><noscript><video src="https://example.com/video.mp4" width="480" height="300"></video></noscript></amp-video>
					<amp-video src="https://example.com/video.mp4" width="480" height="300" layout="responsive"><a href="https://example.com/video.mp4" fallback="">https://example.com/video.mp4</a><noscript><video src="https://example.com/video.mp4" width="480" height="300"></video></noscript></amp-video>
					<amp-video src="https://example.com/video.mp4" width="480" height="300" layout="responsive"><a href="https://example.com/video.mp4" fallback="">https://example.com/video.mp4</a><noscript><video src="https://example.com/video.mp4" width="480" height="300"></video></noscript></amp-video>
					<amp-video src="https://example.com/video.mp4" width="480" height="300" layout="responsive"><a href="https://example.com/video.mp4" fallback="">https://example.com/video.mp4</a><noscript><video src="https://example.com/video.mp4" width="480" height="300"></video></noscript></amp-video>
				',
			],

			'multiple_different_videos' => [
				'
					<video src="https://example.com/video1.mp4" width="480" height="300"></video>
					<video src="https://example.com/video2.ogv" width="300" height="480"></video>
					<video src="https://example.com/video3.webm" height="100" width="200"></video>
				',
				'
					<amp-video src="https://example.com/video1.mp4" width="480" height="300" layout="responsive"><a href="https://example.com/video1.mp4" fallback="">https://example.com/video1.mp4</a><noscript><video src="https://example.com/video1.mp4" width="480" height="300"></video></noscript></amp-video>
					<amp-video src="https://example.com/video2.ogv" width="300" height="480" layout="responsive"><a href="https://example.com/video2.ogv" fallback="">https://example.com/video2.ogv</a><noscript><video src="https://example.com/video2.ogv" width="300" height="480"></video></noscript></amp-video>
					<amp-video src="https://example.com/video3.webm" height="100" width="200" layout="responsive"><a href="https://example.com/video3.webm" fallback="">https://example.com/video3.webm</a><noscript><video src="https://example.com/video3.webm" height="100" width="200"></video></noscript></amp-video>
				',
			],

			'https_not_required' => [
				'<video width="300" height="300" src="http://example.com/video.mp4"></video>',
				'<amp-video width="300" height="300" src="https://example.com/video.mp4" layout="responsive"><a href="https://example.com/video.mp4" fallback="">https://example.com/video.mp4</a><noscript><video width="300" height="300" src="https://example.com/video.mp4"></video></noscript></amp-video>',
			],

			'http_video_with_children' => [
				'
					<video width="480" height="300" poster="https://example.com/poster.jpeg">
						<source src="http://example.com/video.mp4" type="video/mp4">
						<source src="http://example.com/video.ogv" type="video/ogg">
						<track srclang="en" label="English" kind="subtitles" src="https://example.com/test-en.vtt"/>
						<a href="http://example.com/video.mp4">http://example.com/video.mp4</a>
					</video>
				',
				'
					<amp-video width="480" height="300" poster="https://example.com/poster.jpeg" layout="responsive">
						<source src="https://example.com/video.mp4" type="video/mp4">
						<source src="https://example.com/video.ogv" type="video/ogg">
						<track srclang="en" label="English" kind="subtitles" src="https://example.com/test-en.vtt">
						<a href="http://example.com/video.mp4" fallback="">http://example.com/video.mp4</a>
						<noscript>
							<video width="480" height="300" poster="https://example.com/poster.jpeg">
								<source src="https://example.com/video.mp4" type="video/mp4">
								<source src="https://example.com/video.ogv" type="video/ogg">
								<track srclang="en" label="English" kind="subtitles" src="https://example.com/test-en.vtt">
					        </video>
						</noscript>
					</amp-video>
				',
			],

			'amp_video_with_fallback' => [
				'<amp-video width="300" height="300" src="https://example.com/video.mp4" layout="responsive"><noscript><video width="300" height="300" src="https://example.com/video.mp4"></video></noscript></amp-video>',
				null,
			],

			'video_with_fallback' => [
				'<div id="player"><noscript><video width="300" height="300" src="https://example.com/video.mp4"></video></noscript></div>',
				'<div id="player"><!--noscript--><amp-video width="300" height="300" src="https://example.com/video.mp4" layout="responsive"><a href="https://example.com/video.mp4" fallback="">https://example.com/video.mp4</a><noscript><video width="300" height="300" src="https://example.com/video.mp4"></video></noscript></amp-video><!--/noscript--></div>',
			],

			'test_with_dev_mode' => [
				'<video data-ampdevmode="" width="300" height="300" src="https://example.com/video.mp4"></video>',
				null, // No change.
				[
					'add_dev_mode' => true,
				],
			],
		];
	}

	/**
	 * Get video attachment ID.
	 *
	 * @return int|WP_Error ID or error.
	 */
	protected function get_video_attachment_id() {
		$temp_file = trailingslashit( get_temp_dir() ) . 'video-converter-test-' . wp_generate_uuid4() . '.mp4';
		copy( DIR_TESTDATA . '/uploads/small-video.mp4', $temp_file );
		$attachment_id = self::factory()->attachment->create_upload_object( $temp_file );

		// Remove the file extension from the post_title media_handle_upload().
		$attachment               = get_post( $attachment_id, ARRAY_A );
		$attachment['post_title'] = str_replace( '.mp4', '', $attachment['post_title'] );
		$attachment['post_name']  = str_replace( '-mp4', '', $attachment['post_name'] );
		wp_update_post( wp_slash( $attachment ) );

		return $attachment_id;
	}

	/**
	 * Test converter.
	 *
	 * @dataProvider get_data
	 *
	 * @param string $source   Source.
	 * @param string $expected Expected.
	 * @param array  $args     Sanitizer args.
	 */
	public function test_converter( $source, $expected = null, $args = [] ) {
		if ( null === $expected ) {
			$expected = $source;
		}

		if ( false !== strpos( $source, '{{video_url}}' ) ) {
			$video_url = set_url_scheme( wp_get_attachment_url( $this->get_video_attachment_id() ), 'https' );
			$source    = str_replace( '{{video_url}}', esc_url( $video_url ), $source );
			$expected  = str_replace( '{{video_url}}', esc_url( $video_url ), $expected );
		}

		$dom = AMP_DOM_Utils::get_dom_from_content( $source );
		if ( ! empty( $args['add_dev_mode'] ) ) {
			$dom->documentElement->setAttribute( AMP_Rule_Spec::DEV_MODE_ATTRIBUTE, '' );
		}

		$sanitizer = new AMP_Video_Sanitizer( $dom, $args );
		$sanitizer->sanitize();

		$sanitizer = new AMP_Script_Sanitizer( $dom );
		$sanitizer->sanitize();

		$validating_sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$validating_sanitizer->sanitize();

		$content = AMP_DOM_Utils::get_content_from_dom( $dom );

		$this->assertEqualMarkup( $expected, $content );
	}

	/**
	 * Test that HTTPS is enforced.
	 */
	public function test__https_required() {
		$source   = '<video width="300" height="300" src="http://example.com/video.mp4"></video>';
		$expected = '';

		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Video_Sanitizer(
			$dom,
			[
				'require_https_src' => true,
			]
		);
		$sanitizer->sanitize();

		$validating_sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$validating_sanitizer->sanitize();

		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$this->assertEqualMarkup( $expected, $content );
	}

	/**
	 * Test that scripts don't picked up as expected.
	 */
	public function test_get_scripts__didnt_convert() {
		$source   = '<p>Hello World</p>';
		$expected = [];

		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Video_Sanitizer( $dom );
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
	 * Test that scripts get picked up.
	 */
	public function test_get_scripts__did_convert() {
		$source   = '<video width="300" height="300" src="https://example.com/video.mp4"></video>';
		$expected = [ 'amp-video' => true ];

		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Video_Sanitizer( $dom );
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
