<?php
/**
 * Tests for gallery embed.
 *
 * @package AMP
 */

/**
 * Class AMP_Gallery_Embed_Test
 */
class AMP_Gallery_Embed_Test extends WP_UnitTestCase {

	/**
	 * Mock caption text.
	 *
	 * @var string
	 */
	const CAPTION_TEXT = 'Example caption';

	/**
	 * Replacements.
	 *
	 * @var array Associative array of string replacements to process.
	 */
	private $replacements = [];

	/**
	 * Get conversion data.
	 *
	 * @return array[]
	 */
	public function get_conversion_data() {
		$amp_carousel_caption = '<span class="amp-wp-gallery-caption"><span>' . self::CAPTION_TEXT . '</span></span>';

		return [
			'shortcode_with_invalid_id'               => [
				'[gallery ids=1]',
				'',
			],
			'shortcode_with_valid_id'                 => [
				'[gallery ids={{id1}}]',
				'<amp-carousel width="50" height="50" type="slides" layout="responsive"><span class="slide"><img src="{{file1}}.jpg" width="50" height="50" layout="fill" alt="Alt text" object-fit="cover">' . $amp_carousel_caption . '</span></amp-carousel>',
			],
			'shortcode_with_multiple_ids'             => [
				'[gallery ids={{id1}},{{id2}},{{id3}}]',
				'<amp-carousel width="600" height="450" type="slides" layout="responsive">' .
					'<span class="slide"><img src="{{file1}}.jpg" width="50" height="50" layout="fill" alt="Alt text" object-fit="cover">' . $amp_carousel_caption . '</span>' .
					'<span class="slide"><img src="{{file2}}.jpg" width="600" height="450" layout="fill" alt="Alt text" srcset="{{file2}}.jpg 640w, {{file2}}-300x225.jpg 300w" object-fit="cover"></span>' .
					'<span class="slide"><img src="{{file3}}.jpg" width="100" height="100" layout="fill" alt="Alt text" object-fit="cover"></span>' .
				'</amp-carousel>',
			],
			'shortcode_linking_to_file'               => [
				'[gallery link="file" ids={{id1}},{{id2}},{{id3}}]',
				'<amp-carousel width="600" height="450" type="slides" layout="responsive">' .
					'<span class="slide"><a href="{{file1}}.jpg"><img src="{{file1}}.jpg" width="50" height="50" layout="fill" alt="Alt text" object-fit="cover"></a>' . $amp_carousel_caption . '</span>' .
					'<span class="slide"><a href="{{file2}}.jpg"><img src="{{file2}}.jpg" width="600" height="450" layout="fill" alt="Alt text" srcset="{{file2}}.jpg 640w, {{file2}}-300x225.jpg 300w" object-fit="cover"></a></span>' .
					'<span class="slide"><a href="{{file3}}.jpg"><img src="{{file3}}.jpg" width="100" height="100" layout="fill" alt="Alt text" object-fit="cover"></a></span>' .
				'</amp-carousel>',
			],
			'shortcode_with_carousel'                 => [
				'[gallery amp-lightbox=false amp-carousel=true ids={{id1}},{{id2}},{{id3}}]',
				'<amp-carousel width="600" height="450" type="slides" layout="responsive">' .
					'<span class="slide"><img src="{{file1}}.jpg" width="50" height="50" layout="fill" alt="Alt text" object-fit="cover">' . $amp_carousel_caption . '</span>' .
					'<span class="slide"><img src="{{file2}}.jpg" width="600" height="450" layout="fill" alt="Alt text" srcset="{{file2}}.jpg 640w, {{file2}}-300x225.jpg 300w" object-fit="cover"></span>' .
					'<span class="slide"><img src="{{file3}}.jpg" width="100" height="100" layout="fill" alt="Alt text" object-fit="cover"></span>' .
				'</amp-carousel>',
			],
			'shortcode_with_carousel_linking_to_file' => [
				'[gallery amp-lightbox=false amp-carousel=true link="file" ids={{id1}},{{id2}},{{id3}}]',
				'<amp-carousel width="600" height="450" type="slides" layout="responsive">' .
					'<span class="slide"><a href="{{file1}}.jpg"><img src="{{file1}}.jpg" width="50" height="50" layout="fill" alt="Alt text" object-fit="cover"></a>' . $amp_carousel_caption . '</span>' .
					'<span class="slide"><a href="{{file2}}.jpg"><img src="{{file2}}.jpg" width="600" height="450" layout="fill" alt="Alt text" srcset="{{file2}}.jpg 640w, {{file2}}-300x225.jpg 300w" object-fit="cover"></a></span>' .
					'<span class="slide"><a href="{{file3}}.jpg"><img src="{{file3}}.jpg" width="100" height="100" layout="fill" alt="Alt text" object-fit="cover"></a></span>' .
				'</amp-carousel>',
			],
			'shortcode_with_lightbox'                 => [
				'[gallery amp-lightbox=true amp-carousel=false ids={{id1}},{{id2}},{{id3}}]',
				'<style type=\'text/css\'> #gallery-8 { margin: auto; } #gallery-8 .gallery-item { float: left; margin-top: 10px; text-align: center; width: 33%; } #gallery-8 img { border: 2px solid #cfcfcf; } #gallery-8 .gallery-caption { margin-left: 0; } /* see gallery_shortcode() in wp-includes/media.php */ </style>
				<div id=\'gallery-8\' class=\'gallery galleryid-0 gallery-columns-3 gallery-size-thumbnail\'>
					<dl class=\'gallery-item\'><dt class=\'gallery-icon landscape\'>
						<img width="50" height="50" src="{{file1}}.jpg" class="attachment-thumbnail size-thumbnail" alt="Alt text" aria-describedby="gallery-8-{{id1}}" lightbox="" />
					</dt><dd class=\'wp-caption-text gallery-caption\' id=\'gallery-8-{{id1}}\'> ' . self::CAPTION_TEXT . ' </dd></dl>
					<dl class=\'gallery-item\'><dt class=\'gallery-icon landscape\'>
						<img width="150" height="150" src="{{file2}}-150x150.jpg" class="attachment-thumbnail size-thumbnail" alt="Alt text" lightbox="" />
					</dt></dl>
					<dl class=\'gallery-item\'><dt class=\'gallery-icon landscape\'>
						<img width="100" height="100" src="{{file3}}.jpg" class="attachment-thumbnail size-thumbnail" alt="Alt text" lightbox="" />
					</dt></dl>
					<br style="clear: both" />
				</div>',
			],
			'shortcode_with_lightbox_linking_to_file' => [
				'[gallery amp-lightbox=true amp-carousel=false link="file" ids={{id1}},{{id2}},{{id3}}]',
				'<style type=\'text/css\'> #gallery-10 { margin: auto; } #gallery-10 .gallery-item { float: left; margin-top: 10px; text-align: center; width: 33%; } #gallery-10 img { border: 2px solid #cfcfcf; } #gallery-10 .gallery-caption { margin-left: 0; } /* see gallery_shortcode() in wp-includes/media.php */ </style>
				<div id=\'gallery-10\' class=\'gallery galleryid-0 gallery-columns-3 gallery-size-thumbnail\'>
					<dl class=\'gallery-item\'><dt class=\'gallery-icon landscape\'>
						<img width="50" height="50" src="{{file1}}.jpg" class="attachment-thumbnail size-thumbnail" alt="Alt text" aria-describedby="gallery-10-{{id1}}" lightbox="" />
					</dt><dd class=\'wp-caption-text gallery-caption\' id=\'gallery-10-{{id1}}\'> ' . self::CAPTION_TEXT . ' </dd></dl>
					<dl class=\'gallery-item\'><dt class=\'gallery-icon landscape\'>
						<img width="150" height="150" src="{{file2}}-150x150.jpg" class="attachment-thumbnail size-thumbnail" alt="Alt text" lightbox="" />
					</dt></dl>
					<dl class=\'gallery-item\'><dt class=\'gallery-icon landscape\'>
						<img width="100" height="100" src="{{file3}}.jpg" class="attachment-thumbnail size-thumbnail" alt="Alt text" lightbox="" />
					</dt></dl>
					<br style="clear: both" />
				</div>',
			],
			'shortcode_with_lightbox_and_carousel'    => [
				'[gallery amp-lightbox=true amp-carousel=true ids={{id1}},{{id2}},{{id3}}]',
				'<amp-carousel width="600" height="450" type="slides" layout="responsive">' .
					'<span class="slide"><img src="{{file1}}.jpg" width="50" height="50" layout="fill" alt="Alt text" lightbox="" object-fit="cover">' . $amp_carousel_caption . '</span>' .
					'<span class="slide"><img src="{{file2}}.jpg" width="600" height="450" layout="fill" alt="Alt text" srcset="{{file2}}.jpg 640w, {{file2}}-300x225.jpg 300w" lightbox="" object-fit="cover"></span>' .
					'<span class="slide"><img src="{{file3}}.jpg" width="100" height="100" layout="fill" alt="Alt text" lightbox="" object-fit="cover"></span>' .
				'</amp-carousel>',
			],
			'shortcode_with_lightbox_and_carousel_linking_to_file' => [
				'[gallery amp-lightbox=true amp-carousel=true link="file" ids={{id1}},{{id2}},{{id3}}]',
				'<amp-carousel width="600" height="450" type="slides" layout="responsive">' .
					'<span class="slide"><img src="{{file1}}.jpg" width="50" height="50" layout="fill" alt="Alt text" lightbox="" object-fit="cover">' . $amp_carousel_caption . '</span>' .
					'<span class="slide"><img src="{{file2}}.jpg" width="600" height="450" layout="fill" alt="Alt text" srcset="{{file2}}.jpg 640w, {{file2}}-300x225.jpg 300w" lightbox="" object-fit="cover"></span>' .
					'<span class="slide"><img src="{{file3}}.jpg" width="100" height="100" layout="fill" alt="Alt text" lightbox="" object-fit="cover"></span>' .
				'</amp-carousel>',
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
		$source_files = [
			DIR_TESTDATA . '/images/test-image.jpg',
			DIR_TESTDATA . '/images/canola.jpg',
			DIR_TESTDATA . '/images/gradient-square.jpg',
		];

		// When we generate new attachments, we neither control the IDs being
		// generated, which we need for the gallery shortcode, nor the actual
		// filenames, which will have an index appended because of filename
		// collisions. So we write the tests in a way that they don't rely on
		// this, and str-replace the dynamic portions into the expected and
		// actual output after the fact.
		$ids   = [];
		$files = [];

		foreach ( $source_files as $index => $source_file ) {
			$ids[ $index ] = self::factory()->attachment
				->create_upload_object( $source_file, 0 );

			$files[ $index ] = rtrim(
				wp_get_attachment_url( $ids[ $index ] ),
				'.jpg'
			);

			update_post_meta(
				$ids[ $index ],
				'_wp_attachment_image_alt',
				'Alt text'
			);

			// Add a caption to the first image.
			if ( 0 === $index ) {
				wp_update_post(
					[
						'ID'           => $ids[ $index ],
						'post_excerpt' => self::CAPTION_TEXT,
					]
				);
			}
		}

		$this->initialize_replacements( $ids, $files );

		$embed = new AMP_Gallery_Embed_Handler();
		$embed->register_embed();

		$filtered_content = apply_filters(
			'the_content',
			$this->normalize( $source )
		);

		$this->assertEquals(
			$this->normalize( $expected ),
			$this->normalize( $filtered_content )
		);
	}

	/**
	 * Initialize the associative array of replacements to perform.
	 *
	 * @param array<int>    $ids   Array of attachment post IDs.
	 * @param array<string> $files Array of file URLs.
	 */
	private function initialize_replacements( $ids, $files ) {
		$this->replacements = [
			'{{id1}}'   => $ids[0],
			'{{id2}}'   => $ids[1],
			'{{id3}}'   => $ids[2],
			'{{file1}}' => $files[0],
			'{{file2}}' => $files[1],
			'{{file3}}' => $files[2],
			"\n"        => '', // Make tests ignore new lines.
			'> <'       => '><', // Remove left-over space between elements.
		];
	}

	/**
	 * Normalize a piece of content by replacing placeholders with the related
	 * dynamic parts.
	 *
	 * @param string|array $content Content to normalize.
	 * @return string|array Normalized content.
	 */
	private function normalize( $content ) {
		// We start by turning multiple whitespaces into one space, as the default WP gallery code
		// creates a mess with lots of spaces.
		$content = trim( preg_replace( '/\s+/', ' ', $content ) );

		// Normalize attribute quote style for 5.3-alpha.
		$content = str_replace( '<style type=\'text/css\'>', '<style type="text/css">', $content );

		// Then we go through all previously defined replacements.
		return str_replace(
			array_keys( $this->replacements ),
			$this->replacements,
			$content
		);
	}
}
