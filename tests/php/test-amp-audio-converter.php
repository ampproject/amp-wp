<?php
/**
 * Class AMP_Audio_Converter_Test.
 *
 * @package AMP
 */

use AmpProject\AmpWP\Tests\Helpers\MarkupComparison;
use AmpProject\AmpWP\Tests\TestCase;
use AmpProject\AmpWP\ValidationExemption;
use AmpProject\Dom\Document;

// phpcs:disable WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned

/**
 * Class AMP_Audio_Converter_Test
 *
 * @coversDefaultClass \AMP_Audio_Sanitizer
 */
class AMP_Audio_Converter_Test extends TestCase {

	use MarkupComparison;

	/** @covers ::get_selector_conversion_mapping() */
	public function test_get_selector_conversion_mapping() {
		$dom = Document::fromHtmlFragment( '<p>Hello world</p>' );

		$with_defaults = new AMP_Audio_Sanitizer( $dom );
		$this->assertEquals(
			[ 'audio' => [ 'amp-audio' ] ],
			$with_defaults->get_selector_conversion_mapping()
		);

		$with_false_native_used = new AMP_Audio_Sanitizer( $dom, [ 'native_audio_used' => false ] );
		$this->assertEquals(
			[ 'audio' => [ 'amp-audio' ] ],
			$with_false_native_used->get_selector_conversion_mapping()
		);

		$with_true_native_used = new AMP_Audio_Sanitizer( $dom, [ 'native_audio_used' => true ] );
		$this->assertEquals(
			[],
			$with_true_native_used->get_selector_conversion_mapping()
		);
	}

	/**
	 * Get data.
	 *
	 * @return array
	 */
	public function get_data() {
		return [
			'no_audios' => [
				'<p>Lorem Ipsum Demet Delorit.</p>',
				'<p>Lorem Ipsum Demet Delorit.</p>',
			],

			'simple_audio' => [
				'<audio src="https://example.com/audio/file.ogg" data-foo="bar"></audio>',
				'<amp-audio src="https://example.com/audio/file.ogg" data-foo="bar" width="auto"><a href="https://example.com/audio/file.ogg" fallback="">https://example.com/audio/file.ogg</a><noscript><audio src="https://example.com/audio/file.ogg"></audio></noscript></amp-audio>',
				[
					'add_noscript_fallback' => true,
				],
			],

			'simple_audio_without_noscript' => [
				'<audio src="https://example.com/audio/file.ogg" data-foo="bar"></audio>',
				'<amp-audio src="https://example.com/audio/file.ogg" data-foo="bar" width="auto"><a href="https://example.com/audio/file.ogg" fallback="">https://example.com/audio/file.ogg</a></amp-audio>',
				[
					'add_noscript_fallback' => false,
				],
			],

			'simple_native_audio' => [
				'<audio src="https://example.com/audio/file.ogg" data-foo="bar"></audio>',
				sprintf( '<audio src="https://example.com/audio/file.ogg" data-foo="bar" %s></audio>', ValidationExemption::PX_VERIFIED_TAG_ATTRIBUTE ),
				[
					'native_audio_used' => true,
				],
			],

			'autoplay_attribute' => [
				'<audio src="https://example.com/audio/file.ogg" autoplay></audio>',
				'<amp-audio src="https://example.com/audio/file.ogg" autoplay="" width="auto"><a href="https://example.com/audio/file.ogg" fallback="">https://example.com/audio/file.ogg</a><noscript><audio src="https://example.com/audio/file.ogg" autoplay></audio></noscript></amp-audio>',
			],

			'autoplay_attribute__false' => [
				'<audio src="https://example.com/audio/file.ogg" autoplay="false"></audio>',
				'<amp-audio src="https://example.com/audio/file.ogg" width="auto"><a href="https://example.com/audio/file.ogg" fallback="">https://example.com/audio/file.ogg</a><noscript><audio src="https://example.com/audio/file.ogg" autoplay="false"></audio></noscript></amp-audio>',
			],

			'audio_with_allowlisted_attributes__enabled' => [
				'<audio src="https://example.com/audio/file.ogg" class="test" loop="loop" muted></audio>',
				'<amp-audio src="https://example.com/audio/file.ogg" class="test" loop="" muted="" width="auto"><a href="https://example.com/audio/file.ogg" fallback="">https://example.com/audio/file.ogg</a><noscript><audio src="https://example.com/audio/file.ogg" loop="loop" muted></audio></noscript></amp-audio>',
			],

			'audio_with_allowlisted_attributes__disabled' => [
				'<audio src="https://example.com/audio/file.ogg" class="test" loop="false" muted="false"></audio>',
				'<amp-audio src="https://example.com/audio/file.ogg" class="test" width="auto"><a href="https://example.com/audio/file.ogg" fallback="">https://example.com/audio/file.ogg</a><noscript><audio src="https://example.com/audio/file.ogg" loop="false" muted="false"></audio></noscript></amp-audio>',
			],

			'audio_with_children' => [
				'<audio><source src="https://example.com/foo.wav" type="audio/wav"></audio>',
				'<amp-audio width="auto"><source src="https://example.com/foo.wav" type="audio/wav"><a href="https://example.com/foo.wav" fallback="">https://example.com/foo.wav</a><noscript><audio><source src="https://example.com/foo.wav" type="audio/wav"></audio></noscript></amp-audio>',
			],

			'audio_with_http_children' => [
				'<audio><source src="http://example.com/foo.wav" type="audio/wav"></audio>',
				'<amp-audio width="auto"><source src="https://example.com/foo.wav" type="audio/wav"><a href="https://example.com/foo.wav" fallback="">https://example.com/foo.wav</a><noscript><audio><source src="https://example.com/foo.wav" type="audio/wav"></audio></noscript></amp-audio>',
			],

			'audio_with_layout_from_editor_fixed_height' => [
				'<figure><audio src="https://example.com/audio/file.ogg" width="100" height="100"></audio><figcaption>Caption</figcaption></figure>',
				'<figure><amp-audio src="https://example.com/audio/file.ogg" width="auto" height="100"><a href="https://example.com/audio/file.ogg" fallback="">https://example.com/audio/file.ogg</a><noscript><audio src="https://example.com/audio/file.ogg"></audio></noscript></amp-audio><figcaption>Caption</figcaption></figure>',
			],

			'multiple_same_audio' => [
				'
					<audio><source src="https://example.com/foo.wav" type="audio/wav"></audio>
					<audio><source src="https://example.com/foo.wav" type="audio/wav"></audio>
					<audio><source src="https://example.com/foo.wav" type="audio/wav"></audio>
				',
				'
					<amp-audio width="auto"><source src="https://example.com/foo.wav" type="audio/wav"><a href="https://example.com/foo.wav" fallback="">https://example.com/foo.wav</a><noscript><audio><source src="https://example.com/foo.wav" type="audio/wav"></audio></noscript></amp-audio>
					<amp-audio width="auto"><source src="https://example.com/foo.wav" type="audio/wav"><a href="https://example.com/foo.wav" fallback="">https://example.com/foo.wav</a><noscript><audio><source src="https://example.com/foo.wav" type="audio/wav"></audio></noscript></amp-audio>
					<amp-audio width="auto"><source src="https://example.com/foo.wav" type="audio/wav"><a href="https://example.com/foo.wav" fallback="">https://example.com/foo.wav</a><noscript><audio><source src="https://example.com/foo.wav" type="audio/wav"></audio></noscript></amp-audio>
				',
			],

			'multiple_different_audio' => [
				'
					<audio><source src="https://example.com/foo.wav" type="audio/wav"></audio>
					<audio src="https://example.com/audio/file.ogg"></audio>
					<audio><source src="https://example.com/foo2.wav" type="audio/wav"></audio>
				',
				'
					<amp-audio width="auto"><source src="https://example.com/foo.wav" type="audio/wav"><a href="https://example.com/foo.wav" fallback="">https://example.com/foo.wav</a><noscript><audio><source src="https://example.com/foo.wav" type="audio/wav"></audio></noscript></amp-audio>
					<amp-audio src="https://example.com/audio/file.ogg" width="auto"><a href="https://example.com/audio/file.ogg" fallback="">https://example.com/audio/file.ogg</a><noscript><audio src="https://example.com/audio/file.ogg"></audio></noscript></amp-audio>
					<amp-audio width="auto"><source src="https://example.com/foo2.wav" type="audio/wav"><a href="https://example.com/foo2.wav" fallback="">https://example.com/foo2.wav</a><noscript><audio><source src="https://example.com/foo2.wav" type="audio/wav"></audio></noscript></amp-audio>
				',
			],

			'audio_with_track_no_source_element' => [
				'
					<audio src="https://example.com/audio/file.ogg">
						<track kind="chapters" srclang="en" src="https://example.com/media/examples/friday.vtt">
					</audio>
				',
				'
					<amp-audio src="https://example.com/audio/file.ogg" width="auto">
						<track kind="chapters" srclang="en" src="https://example.com/media/examples/friday.vtt">
						<a href="https://example.com/audio/file.ogg" fallback="">https://example.com/audio/file.ogg</a>
						<noscript>
							<audio src="https://example.com/audio/file.ogg">
								<track kind="chapters" srclang="en" src="https://example.com/media/examples/friday.vtt">
							</audio>
						</noscript>
					</amp-audio>
				',
			],

			'audio_with_track_and_source_element' => [
				'
					<audio>
						<source src="https://example.com/audio/file.ogg" type="audio/mp3">
						<track kind="chapters" srclang="en" src="https://example.com/media/examples/friday.vtt">
					</audio>
				',
				'
					<amp-audio width="auto">
						<source src="https://example.com/audio/file.ogg" type="audio/mp3">
						<track kind="chapters" srclang="en" src="https://example.com/media/examples/friday.vtt">
						<a href="https://example.com/audio/file.ogg" fallback="">https://example.com/audio/file.ogg</a>
						<noscript>
							<audio>
								<source src="https://example.com/audio/file.ogg" type="audio/mp3">
								<track kind="chapters" srclang="en" src="https://example.com/media/examples/friday.vtt">
							</audio>
						</noscript>
					</amp-audio>
				',
			],

			'audio_block_and_shortcode_output' => [
				// Note: the IE conditional comment is stripped, as AMP doesn't support those browsers anyway.
				'
					<figure class="wp-block-audio">
						<audio controls src="https://wordpressdev.lndo.site/content/uploads/2019/02/do-you-know-I-am-batman.mp3"></audio>
						<figcaption>Caption</figcaption>
					</figure>

					<!--[if lt IE 9]><script>document.createElement(\'audio\');</script><![endif]-->
					<audio class="wp-audio-shortcode" id="audio-87-1" preload="none" style="width: 100%;" controls="controls">
						<source type="audio/mpeg" src="https://wordpressdev.lndo.site/content/uploads/2019/02/do-you-know-I-am-batman.mp3?_=1"/>
						<a href="https://wordpressdev.lndo.site/content/uploads/2019/02/do-you-know-I-am-batman.mp3">https://wordpressdev.lndo.site/content/uploads/2019/02/do-you-know-I-am-batman.mp3</a>
					</audio>
				',
				'
					<figure class="wp-block-audio">
						<amp-audio controls="" src="https://wordpressdev.lndo.site/content/uploads/2019/02/do-you-know-I-am-batman.mp3" width="auto">
							<a href="https://wordpressdev.lndo.site/content/uploads/2019/02/do-you-know-I-am-batman.mp3" fallback="">https://wordpressdev.lndo.site/content/uploads/2019/02/do-you-know-I-am-batman.mp3</a>
							<noscript>
								<audio controls src="https://wordpressdev.lndo.site/content/uploads/2019/02/do-you-know-I-am-batman.mp3"></audio>
							</noscript>
						</amp-audio>
						<figcaption>Caption</figcaption>
					</figure>

					<amp-audio class="wp-audio-shortcode amp-wp-199b6f0" id="audio-87-1" preload="none" controls="controls" width="auto" data-amp-original-style="width: 100%;">
						<source type="audio/mpeg" src="https://wordpressdev.lndo.site/content/uploads/2019/02/do-you-know-I-am-batman.mp3?_=1">
						<a href="https://wordpressdev.lndo.site/content/uploads/2019/02/do-you-know-I-am-batman.mp3" fallback="">https://wordpressdev.lndo.site/content/uploads/2019/02/do-you-know-I-am-batman.mp3</a>
						<noscript>
							<audio preload="none" controls="controls">
								<source type="audio/mpeg" src="https://wordpressdev.lndo.site/content/uploads/2019/02/do-you-know-I-am-batman.mp3?_=1">
							</audio>
						</noscript>
					</amp-audio>
				',
			],

			'amp_audio_with_existing_noscript_fallback' => [
				'<amp-audio src="https://example.com/audio/file.ogg"><noscript><audio src="https://example.com/audio/file.ogg"></audio></noscript></amp-audio>',
				null,
			],

			'audio_with_existing_noscript_fallback'     => [
				'<div id="player"><noscript><audio src="https://example.com/audio/file.mp3"></audio></noscript></div>',
				'
					<div id="player">
						<!--noscript-->
						<amp-audio src="https://example.com/audio/file.mp3" width="auto">
							<a href="https://example.com/audio/file.mp3" fallback="">https://example.com/audio/file.mp3</a>
							<noscript>
								<audio src="https://example.com/audio/file.mp3"></audio>
							</noscript>
						</amp-audio>
						<!--/noscript-->
					</div>
				',
			],

			'audio_with_extra_attributes' => [
				'<audio src="https://example.com/audio/file.ogg" onclick="foo()" data-foo="bar"></audio>',
				'<amp-audio src="https://example.com/audio/file.ogg" data-foo="bar" width="auto"><a href="https://example.com/audio/file.ogg" fallback="">https://example.com/audio/file.ogg</a><noscript><audio src="https://example.com/audio/file.ogg"></audio></noscript></amp-audio>',
				[
					'add_noscript_fallback' => true,
				],
			],

			'test_with_dev_mode' => [
				'<audio data-ampdevmode="" src="https://example.com/audio/file.ogg" onclick="foo()" data-foo="bar"></audio>',
				null, // No change.
				[
					'add_dev_mode' => true,
				],
			],
		];
	}

	/**
	 * Test converter.
	 *
	 * @dataProvider get_data
	 *
	 * @param string $source   Source.
	 * @param string $expected Expected.
	 * @param array  $args     Args for sanitizer.
	 *
	 * @covers ::sanitize()
	 */
	public function test_converter( $source, $expected = null, $args = [] ) {
		if ( null === $expected ) {
			$expected = $source;
		}
		$dom = AMP_DOM_Utils::get_dom_from_content( $source );
		if ( ! empty( $args['add_dev_mode'] ) ) {
			$dom->documentElement->setAttribute( AMP_Rule_Spec::DEV_MODE_ATTRIBUTE, '' );
		}

		$sanitizer = new AMP_Audio_Sanitizer( $dom, $args );
		$sanitizer->sanitize();

		$sanitizer = new AMP_Script_Sanitizer( $dom );
		$sanitizer->sanitize();

		$style_sanitizer = new AMP_Style_Sanitizer( $dom );
		$style_sanitizer->sanitize();

		$validating_sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$validating_sanitizer->sanitize();

		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$this->assertEqualMarkup( $expected, $content );
	}

	/**
	 * Test that HTTPS is enforced.
	 *
	 * @covers ::sanitize()
	 */
	public function test__https_required() {
		$source   = '<audio src="http://example.com/audio/file.ogg"></audio>';
		$expected = '';

		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Audio_Sanitizer(
			$dom,
			[
				'require_https_src' => true,
			]
		);
		$sanitizer->sanitize();

		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$this->assertEquals( $expected, $content );
	}

	/**
	 * Test that scripts don't get picked up.
	 *
	 * @covers ::sanitize()
	 * @covers ::get_scripts()
	 */
	public function test_get_scripts__didnt_convert() {
		$source   = '<p>Hello World</p>';
		$expected = [];

		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Audio_Sanitizer( $dom );
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
	 *
	 * @covers ::sanitize()
	 * @covers ::get_scripts()
	 */
	public function test_get_scripts__did_convert() {
		$source   = '<audio src="https://example.com/audio/file.ogg"></audio>';
		$expected = [ 'amp-audio' => true ];

		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Audio_Sanitizer( $dom );
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
