<?php

use AmpProject\AmpWP\Tests\Helpers\WithoutBlockPreRendering;
use AmpProject\AmpWP\Tests\TestCase;

class AMP_Instagram_Embed_Handler_Test extends TestCase {

	use WithoutBlockPreRendering;

	public function get_conversion_data() {
		$overflow_button = '<button overflow type="button">See more</button>';

		return [
			'no_embed'        => [
				'<p>Hello world.</p>',
				'<p>Hello world.</p>' . PHP_EOL,
			],
			'simple_url'      => [
				'https://instagram.com/p/7-l0z_p4A4/' . PHP_EOL,
				'<p><amp-instagram data-shortcode="7-l0z_p4A4" data-captioned layout="responsive" width="600" height="600">' . $overflow_button . '</amp-instagram></p>' . PHP_EOL,
			],
			'simple_tv_url'   => [
				'https://instagram.com/tv/7-l0z_p4A4/' . PHP_EOL,
				'<p><amp-instagram data-shortcode="7-l0z_p4A4" data-captioned layout="responsive" width="600" height="600">' . $overflow_button . '</amp-instagram></p>' . PHP_EOL,
			],
			'simple_reel_url' => [
				'https://instagram.com/reel/COWmlFLB_7P/' . PHP_EOL,
				'<p><amp-instagram data-shortcode="COWmlFLB_7P" data-captioned layout="responsive" width="600" height="600">' . $overflow_button . '</amp-instagram></p>' . PHP_EOL,
			],
			'short_url'       => [
				'https://instagr.am/p/7-l0z_p4A4' . PHP_EOL,
				'<p><amp-instagram data-shortcode="7-l0z_p4A4" data-captioned layout="responsive" width="600" height="600">' . $overflow_button . '</amp-instagram></p>' . PHP_EOL,
			],
			'short_tv_url'    => [
				'https://instagr.am/tv/7-l0z_p4A4' . PHP_EOL,
				'<p><amp-instagram data-shortcode="7-l0z_p4A4" data-captioned layout="responsive" width="600" height="600">' . $overflow_button . '</amp-instagram></p>' . PHP_EOL,
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

		$this->assertEquals( $expected, $filtered_content );
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
		$source = apply_filters( 'the_content', $source );

		$validating_sanitizer = new AMP_Tag_And_Attribute_Sanitizer( AMP_DOM_Utils::get_dom_from_content( $source ) );
		$validating_sanitizer->sanitize();

		$scripts = array_merge(
			$embed->get_scripts(),
			$validating_sanitizer->get_scripts()
		);

		$this->assertEquals( $expected, $scripts );
	}

	/**
	 * Data for test__raw_embed_sanitizer.
	 *
	 * @return array
	 */
	public function get_raw_embed_dataset() {
		$overflow_button = '<button overflow type="button">See more</button>';

		return [
			'no_embed'                               => [
				'<p>Hello world.</p>',
				'<p>Hello world.</p>',
			],
			'embed_blockquote_without_instagram'     => [
				'<blockquote><p>lorem ipsum</p></blockquote>',
				'<blockquote><p>lorem ipsum</p></blockquote>',
			],

			'blockquote_embed'                       => [
				wpautop( '<blockquote class="instagram-media" data-instgrm-permalink="https://www.instagram.com/p/BhsgU3jh6xE/"><div style="padding: 8px;">Lorem ipsum</div></blockquote> <script async defer src="//www.instagram.com/embed.js"></script>' ), // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript, WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine
				'<amp-instagram data-shortcode="BhsgU3jh6xE" layout="responsive" width="600" height="600">' . $overflow_button . '</amp-instagram>' . "\n\n",
			],

			'blockquote_tv_embed'                    => [
				wpautop( '<blockquote class="instagram-media" data-instgrm-permalink="https://www.instagram.com/tv/BhsgU3jh6xE/"><div style="padding: 8px;">Lorem ipsum</div></blockquote> <script async defer src="//www.instagram.com/embed.js"></script>' ), // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript, WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine
				'<amp-instagram data-shortcode="BhsgU3jh6xE" layout="responsive" width="600" height="600">' . $overflow_button . '</amp-instagram>' . "\n\n",
			],

			'blockquote_reel_embed'                  => [
				wpautop( '<blockquote class="instagram-media" data-instgrm-permalink="https://www.instagram.com/reel/COWmlFLB_7P/"><div style="padding: 8px;">Lorem ipsum</div></blockquote> <script async defer src="//www.instagram.com/embed.js"></script>' ), // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript, WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine
				'<amp-instagram data-shortcode="COWmlFLB_7P" layout="responsive" width="600" height="600">' . $overflow_button . '</amp-instagram>' . "\n\n",
			],

			'blockquote_embed_notautop'              => [
				'<blockquote class="instagram-media" data-instgrm-permalink="https://www.instagram.com/p/BhsgU3jh6xE/"><div style="padding: 8px;">Lorem ipsum</div></blockquote> <script async defer src="//www.instagram.com/embed.js"></script>', // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript, WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine
				'<amp-instagram data-shortcode="BhsgU3jh6xE" layout="responsive" width="600" height="600">' . $overflow_button . '</amp-instagram> ',
			],

			'blockquote_embed_with_caption'          => [
				wpautop( '<blockquote class="instagram-media" data-instgrm-permalink="https://www.instagram.com/p/BhsgU3jh6xE/" data-instgrm-captioned><div style="padding: 8px;">Lorem ipsum</div></blockquote> <script async defer src="//www.instagram.com/embed.js"></script>' ), // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript, WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine
				'<amp-instagram data-shortcode="BhsgU3jh6xE" layout="responsive" width="600" height="600" data-captioned="">' . $overflow_button . '</amp-instagram>' . "\n\n",
			],

			'blockquote_embed_with_caption_notautop' => [
				'<blockquote class="instagram-media" data-instgrm-permalink="https://www.instagram.com/p/BhsgU3jh6xE/" data-instgrm-captioned><div style="padding: 8px;">Lorem ipsum</div></blockquote> <script async defer src="//www.instagram.com/embed.js"></script>', // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript, WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine
				'<amp-instagram data-shortcode="BhsgU3jh6xE" layout="responsive" width="600" height="600" data-captioned="">' . $overflow_button . '</amp-instagram> ',
			],

			'blockquote_unsupported_permalink'       => [
				wpautop( '<blockquote class="instagram-media" data-instgrm-permalink="https://www.instagram.com/unsupported/post_id"><div style="padding: 8px;">Lorem ipsum</div></blockquote> <script async defer src="//www.instagram.com/embed.js"></script>' ), // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript, WordPress.Arrays.ArrayDeclarationSpacing.ArrayItemNoNewLine
				'<a href="https://www.instagram.com/unsupported/post_id" class="amp-wp-embed-fallback">View on Instagram</a>' . "\n\n",
			],
		];
	}

	/**
	 * Test raw_embed_sanitizer.
	 *
	 * @param string $source  Content.
	 * @param string $expected Expected content.
	 * @dataProvider get_raw_embed_dataset
	 * @covers AMP_Instagram_Embed_Handler::sanitize_raw_embeds()
	 */
	public function test__raw_embed_sanitizer( $source, $expected ) {
		$dom   = AMP_DOM_Utils::get_dom_from_content( $source );
		$embed = new AMP_Instagram_Embed_Handler();

		$embed->sanitize_raw_embeds( $dom );

		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$content = preg_replace( '/(?<=>)\s+(?=<)/', '', $content );

		$this->assertEquals( $expected, $content );
	}
}
