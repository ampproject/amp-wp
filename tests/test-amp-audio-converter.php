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
 * @covers AMP_Audio_Sanitizer
 */
class AMP_Audio_Converter_Test extends WP_UnitTestCase {

	/**
	 * Get data.
	 *
	 * @return array
	 */
	public function get_data() {
		return array(
			'no_audios' => array(
				'<p>Lorem Ipsum Demet Delorit.</p>',
				'<p>Lorem Ipsum Demet Delorit.</p>',
			),

			'simple_audio' => array(
				'<audio src="https://example.com/audio/file.ogg" data-foo="bar"></audio>',
				'<amp-audio src="https://example.com/audio/file.ogg" data-foo="bar" width="auto"><a href="https://example.com/audio/file.ogg" fallback="">https://example.com/audio/file.ogg</a><noscript><audio src="https://example.com/audio/file.ogg"></audio></noscript></amp-audio>',
				array(
					'add_noscript_fallback' => true,
				),
			),

			'simple_audio_without_noscript' => array(
				'<audio src="https://example.com/audio/file.ogg" data-foo="bar"></audio>',
				'<amp-audio src="https://example.com/audio/file.ogg" data-foo="bar" width="auto"><a href="https://example.com/audio/file.ogg" fallback="">https://example.com/audio/file.ogg</a></amp-audio>',
				array(
					'add_noscript_fallback' => false,
				),
			),

			'autoplay_attribute' => array(
				'<audio src="https://example.com/audio/file.ogg" autoplay></audio>',
				'<amp-audio src="https://example.com/audio/file.ogg" autoplay="" width="auto"><a href="https://example.com/audio/file.ogg" fallback="">https://example.com/audio/file.ogg</a><noscript><audio src="https://example.com/audio/file.ogg" autoplay></audio></noscript></amp-audio>',
			),

			'autoplay_attribute__false' => array(
				'<audio src="https://example.com/audio/file.ogg" autoplay="false"></audio>',
				'<amp-audio src="https://example.com/audio/file.ogg" width="auto"><a href="https://example.com/audio/file.ogg" fallback="">https://example.com/audio/file.ogg</a><noscript><audio src="https://example.com/audio/file.ogg" autoplay="false"></audio></noscript></amp-audio>',
			),

			'audio_with_whitelisted_attributes__enabled' => array(
				'<audio src="https://example.com/audio/file.ogg" class="test" loop="loop" muted></audio>',
				'<amp-audio src="https://example.com/audio/file.ogg" class="test" loop="" muted="" width="auto"><a href="https://example.com/audio/file.ogg" fallback="">https://example.com/audio/file.ogg</a><noscript><audio src="https://example.com/audio/file.ogg" class="test" loop="loop" muted></audio></noscript></amp-audio>',
			),

			'audio_with_whitelisted_attributes__disabled' => array(
				'<audio src="https://example.com/audio/file.ogg" class="test" loop="false" muted="false"></audio>',
				'<amp-audio src="https://example.com/audio/file.ogg" class="test" width="auto"><a href="https://example.com/audio/file.ogg" fallback="">https://example.com/audio/file.ogg</a><noscript><audio src="https://example.com/audio/file.ogg" class="test" loop="false" muted="false"></audio></noscript></amp-audio>',
			),

			'audio_with_children' => array(
				'<audio><source src="https://example.com/foo.wav" type="audio/wav"></audio>',
				'<amp-audio width="auto"><source src="https://example.com/foo.wav" type="audio/wav"><a href="https://example.com/foo.wav" fallback="">https://example.com/foo.wav</a><noscript><audio><source src="https://example.com/foo.wav" type="audio/wav"></audio></noscript></amp-audio>',
			),

			'audio_with_http_children' => array(
				'<audio><source src="http://example.com/foo.wav" type="audio/wav"></audio>',
				'<amp-audio width="auto"><source src="https://example.com/foo.wav" type="audio/wav"><a href="https://example.com/foo.wav" fallback="">https://example.com/foo.wav</a><noscript><audio><source src="https://example.com/foo.wav" type="audio/wav"></audio></noscript></amp-audio>',
			),

			'audio_with_layout_from_editor_fixed_height' => array(
				'<figure><audio src="https://example.com/audio/file.ogg" width="100" height="100"></audio><figcaption>Caption</figcaption></figure>',
				'<figure><amp-audio src="https://example.com/audio/file.ogg" width="auto" height="100"><a href="https://example.com/audio/file.ogg" fallback="">https://example.com/audio/file.ogg</a><noscript><audio src="https://example.com/audio/file.ogg"></audio></noscript></amp-audio><figcaption>Caption</figcaption></figure>',
			),

			'multiple_same_audio' => array(
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
			),

			'multiple_different_audio' => array(
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
			),

			'audio_with_track_no_source_element' => array(
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
			),

			'audio_with_track_and_source_element' => array(
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
			),

			'audio_block_and_shortcode_output' => array(
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

					<!--[if lt IE 9]><script>document.createElement(\'audio\');</script><![endif]-->
					<amp-audio class="wp-audio-shortcode amp-wp-199b6f0" id="audio-87-1" preload="none" controls="controls" width="auto">
						<source type="audio/mpeg" src="https://wordpressdev.lndo.site/content/uploads/2019/02/do-you-know-I-am-batman.mp3?_=1">
						<a href="https://wordpressdev.lndo.site/content/uploads/2019/02/do-you-know-I-am-batman.mp3" fallback="">https://wordpressdev.lndo.site/content/uploads/2019/02/do-you-know-I-am-batman.mp3</a>
						<noscript>
							<audio class="wp-audio-shortcode amp-wp-199b6f0" id="audio-87-1" preload="none" controls="controls">
								<source type="audio/mpeg" src="https://wordpressdev.lndo.site/content/uploads/2019/02/do-you-know-I-am-batman.mp3?_=1">
							</audio>
						</noscript>
					</amp-audio>
				',
			),

			'amp_audio_with_existing_noscript_fallback' => array(
				'<amp-audio src="https://example.com/audio/file.ogg"><noscript><audio src="https://example.com/audio/file.ogg"></audio></noscript></amp-audio>',
				null,
			),

			'audio_with_existing_noscript_fallback'     => array(
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
			),

			'audio_with_extra_attributes' => array(
				'<audio src="https://example.com/audio/file.ogg" onclick="foo()" data-foo="bar"></audio>',
				'<amp-audio src="https://example.com/audio/file.ogg" data-foo="bar" width="auto"><a href="https://example.com/audio/file.ogg" fallback="">https://example.com/audio/file.ogg</a><noscript><audio src="https://example.com/audio/file.ogg"></audio></noscript></amp-audio>',
				array(
					'add_noscript_fallback' => true,
				),
			),
		);
	}

	/**
	 * Test converter.
	 *
	 * @dataProvider get_data
	 *
	 * @param string $source   Source.
	 * @param string $expected Expected.
	 * @param array  $args     Args for sanitizer.
	 */
	public function test_converter( $source, $expected = null, $args = array() ) {
		if ( null === $expected ) {
			$expected = $source;
		}
		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Audio_Sanitizer( $dom, $args );
		$sanitizer->sanitize();

		$sanitizer = new AMP_Script_Sanitizer( $dom );
		$sanitizer->sanitize();

		$style_sanitizer = new AMP_Style_Sanitizer( $dom );
		$style_sanitizer->sanitize();

		$whitelist_sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$whitelist_sanitizer->sanitize();

		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$this->assertEqualMarkup( $expected, $content );
	}

	/**
	 * Test that HTTPS is enforced.
	 */
	public function test__https_required() {
		$source   = '<audio src="http://example.com/audio/file.ogg"></audio>';
		$expected = '';

		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Audio_Sanitizer(
			$dom,
			array(
				'require_https_src' => true,
			)
		);
		$sanitizer->sanitize();

		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$this->assertEquals( $expected, $content );
	}

	/**
	 * Test that scripts don't get picked up.
	 */
	public function test_get_scripts__didnt_convert() {
		$source   = '<p>Hello World</p>';
		$expected = array();

		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
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

	/**
	 * Test that scripts get picked up.
	 */
	public function test_get_scripts__did_convert() {
		$source   = '<audio src="https://example.com/audio/file.ogg"></audio>';
		$expected = array( 'amp-audio' => true );

		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
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

	/**
	 * Assert markup is equal.
	 *
	 * @param string $expected Expected markup.
	 * @param string $actual   Actual markup.
	 */
	public function assertEqualMarkup( $expected, $actual ) {
		$actual   = preg_replace( '/(?<=>)\s+(?=<)/', '', trim( $actual ) );
		$expected = preg_replace( '/(?<=>)\s+(?=<)/', '', trim( $expected ) );

		$this->assertEquals(
			array_filter( preg_split( '#(<[^>]+>|[^<>]+)#', $expected, -1, PREG_SPLIT_DELIM_CAPTURE ) ),
			array_filter( preg_split( '#(<[^>]+>|[^<>]+)#', $actual, -1, PREG_SPLIT_DELIM_CAPTURE ) )
		);
	}
}
