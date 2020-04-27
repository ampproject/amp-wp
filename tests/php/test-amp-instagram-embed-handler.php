<?php

class AMP_Instagram_Embed_Handler_Test extends WP_UnitTestCase {

	public function get_conversion_data() {
		return [
			'no_embed'                           => [
				'<p>Hello world.</p>',
				'<p>Hello world.</p>',
			],
			'simple_url'                         => [
				'https://instagram.com/p/7-l0z_p4A4/',
				'<amp-instagram data-shortcode="7-l0z_p4A4" layout="responsive" width="600" height="600" data-captioned=""></amp-instagram>',
			],
			'simple_tv_url'                      => [
				'https://instagram.com/tv/7-l0z_p4A4/' . PHP_EOL,
				'<amp-instagram data-shortcode="7-l0z_p4A4" layout="responsive" width="600" height="600" data-captioned=""></amp-instagram>',
			],
			'short_url'                          => [
				'https://instagr.am/p/7-l0z_p4A4' . PHP_EOL,
				'<amp-instagram data-shortcode="7-l0z_p4A4" layout="responsive" width="600" height="600" data-captioned=""></amp-instagram>',
			],
			'short_tv_url'                       => [
				'https://instagr.am/tv/7-l0z_p4A4' . PHP_EOL,
				'<amp-instagram data-shortcode="7-l0z_p4A4" layout="responsive" width="600" height="600" data-captioned=""></amp-instagram>',
			],

			'embed_blockquote_without_instagram' => [
				'<blockquote><p>lorem ipsum</p></blockquote>',
				'<blockquote><p>lorem ipsum</p></blockquote>',
			],

			'blockquote_embed'                   => [
				wpautop( '<blockquote class="instagram-media" data-instgrm-permalink="https://www.instagram.com/p/BhsgU3jh6xE/"><div style="padding: 8px;">Lorem ipsum</div></blockquote> <script async defer src="//www.instagram.com/embed.js"></script>' ), // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript, WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine
				'<amp-instagram data-shortcode="BhsgU3jh6xE" layout="responsive" width="600" height="600"></amp-instagram>',
			],

			'blockquote_tv_embed'                => [
				wpautop( '<blockquote class="instagram-media" data-instgrm-permalink="https://www.instagram.com/tv/BhsgU3jh6xE/"><div style="padding: 8px;">Lorem ipsum</div></blockquote> <script async defer src="//www.instagram.com/embed.js"></script>' ), // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript, WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine
				'<amp-instagram data-shortcode="BhsgU3jh6xE" layout="responsive" width="600" height="600"></amp-instagram>',
			],

			'blockquote_embed_with_caption'      => [
				wpautop( '<blockquote class="instagram-media" data-instgrm-permalink="https://www.instagram.com/p/BhsgU3jh6xE/" data-instgrm-captioned><div style="padding: 8px;">Lorem ipsum</div></blockquote> <script async defer src="//www.instagram.com/embed.js"></script>' ), // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript, WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine
				'<amp-instagram data-shortcode="BhsgU3jh6xE" layout="responsive" width="600" height="600" data-captioned=""></amp-instagram>',
			],
		];
	}

	/**
	 * Test conversion.
	 *
	 * @dataProvider get_conversion_data
	 * @param string $source   Source.
	 * @param string $expected Expected.
	 */
	public function test__conversion( $source, $expected ) {
		$embed = new AMP_Instagram_Embed_Handler();
		$embed->register_embed();

		$filtered_content = apply_filters( 'the_content', $source );
		$dom              = AMP_DOM_Utils::get_dom_from_content( $filtered_content );
		$embed->sanitize_raw_embeds( $dom );

		$content = AMP_DOM_Utils::get_content_from_dom( $dom );

		$this->assertEquals( $expected, $content );
	}

	/**
	 * Get scripts data.
	 *
	 * @return array
	 */
	public function get_scripts_data() {
		return [
			'not_converted' => [
				'<p>Hello World.</p>',
				[],
			],
			'converted'     => [
				'https://instagram.com/p/7-l0z_p4A4/' . PHP_EOL,
				[ 'amp-instagram' => true ],
			],
		];
	}

	/**
	 * Test get_scripts().
	 *
	 * @dataProvider get_scripts_data
	 * @param string $source   Source.
	 * @param array  $expected Expected.
	 */
	public function test__get_scripts( $source, $expected ) {
		$embed = new AMP_Instagram_Embed_Handler();
		$embed->register_embed();

		$filtered_content = apply_filters( 'the_content', $source );
		$dom              = AMP_DOM_Utils::get_dom_from_content( $filtered_content );
		$embed->sanitize_raw_embeds( $dom );

		$whitelist_sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$whitelist_sanitizer->sanitize();

		$scripts = array_merge(
			$embed->get_scripts(),
			$whitelist_sanitizer->get_scripts()
		);

		$this->assertEquals( $expected . PHP_EOL . PHP_EOL, $scripts );
	}
}
