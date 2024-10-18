<?php
/**
 * Tests for gallery embed.
 *
 * @package AMP
 */

use AmpProject\AmpWP\Admin\ReaderThemes;
use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\Tests\Helpers\LoadsCoreThemes;
use AmpProject\AmpWP\Tests\Helpers\WithoutBlockPreRendering;
use AmpProject\AmpWP\Tests\TestCase;

/**
 * Class AMP_Gallery_Embed_Handler_Test
 */
class AMP_Gallery_Embed_Handler_Test extends TestCase {

	use WithoutBlockPreRendering, LoadsCoreThemes;

	private static $original_amp_options;

	public static function set_up_before_class() {
		parent::set_up_before_class();
		self::$original_amp_options = AMP_Options_Manager::get_options();
	}

	public static function tear_down_after_class() {
		AMP_Options_Manager::update_options( self::$original_amp_options );
		parent::tear_down_after_class();
	}

	public function set_up() {
		parent::set_up();

		$this->register_core_themes();
	}

	/**
	 * Tear down.
	 */
	public function tear_down() {
		if ( did_action( 'add_attachment' ) ) {
			$this->remove_added_uploads();
		}

		$this->restore_theme_directories();

		parent::tear_down();
	}

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
		$amp_carousel_caption = '<figcaption class="amp-wp-gallery-caption"> ' . self::CAPTION_TEXT . ' </figcaption>';

		return [
			'shortcode_with_invalid_id'               => [
				'[gallery ids=1]',
				'',
			],
			'shortcode_with_valid_ids_in_legacy_mode' => [
				'[gallery ids={{id1}},{{id2}},{{id3}}]',
				'<style type="text/css"> #gallery-1 { margin: auto; } #gallery-1 .gallery-item { float: left; margin-top: 10px; text-align: center; width: 33%; } #gallery-1 img { border: 2px solid #cfcfcf; } #gallery-1 .gallery-caption { margin-left: 0; } /* see gallery_shortcode() in wp-includes/media.php */ </style>
				<amp-carousel width="640" height="480" type="slides" layout="responsive">
					<figure class="slide"><a href="{{file_url1}}"><img width="100" height="100" src="{{file1}}.jpg" class="attachment-large size-large" alt="Alt text" aria-describedby="gallery-1-{{id1}}" layout="fill" object-fit="cover"></a>' . $amp_carousel_caption . '</figure>
					<figure class="slide"><a href="{{file_url2}}"><img width="640" height="480" src="{{file2}}.jpg" class="attachment-large size-large" alt="Alt text" srcset="{{file2}}.jpg 640w, {{file2}}-300x225.jpg 300w" sizes="(max-width: 640px) 100vw, 640px" layout="fill" object-fit="cover"></a></figure>
					<figure class="slide"><a href="{{file_url3}}"><img width="100" height="100" src="{{file3}}.jpg" class="attachment-large size-large" alt="Alt text" layout="fill" object-fit="cover"></a></figure>
				</amp-carousel>',
				true,
			],
			'shortcode_with_valid_ids_in_modern_mode' => [
				'[gallery ids={{id1}},{{id2}},{{id3}}]',
				'<style type="text/css"> #gallery-1 { margin: auto; } #gallery-1 .gallery-item { float: left; margin-top: 10px; text-align: center; width: 33%; } #gallery-1 img { border: 2px solid #cfcfcf; } #gallery-1 .gallery-caption { margin-left: 0; } /* see gallery_shortcode() in wp-includes/media.php */ </style>
				<div id="gallery-1" class="gallery galleryid-0 gallery-columns-3 gallery-size-thumbnail">
					<dl class="gallery-item">
						<dt class="gallery-icon landscape"><a href="{{file_url1}}"><img width="100" height="100" src="{{file1}}.jpg" class="attachment-thumbnail size-thumbnail" alt="Alt text" aria-describedby="gallery-1-{{id1}}"></a></dt>
						<dd class="wp-caption-text gallery-caption" id="gallery-1-{{id1}}"> ' . self::CAPTION_TEXT . ' </dd>
					</dl>
					<dl class="gallery-item">
						<dt class="gallery-icon landscape"><a href="{{file_url2}}"><img width="150" height="150" src="{{file2}}-150x150.jpg" class="attachment-thumbnail size-thumbnail" alt="Alt text"></a></dt>
					</dl>
					<dl class="gallery-item">
						<dt class="gallery-icon landscape"><a href="{{file_url3}}"><img width="100" height="100" src="{{file3}}.jpg" class="attachment-thumbnail size-thumbnail" alt="Alt text"></a></dt>
					</dl>
					<br style="clear: both">
				</div>',
			],
			'shortcode_with_carousel'                 => [
				'[gallery amp-lightbox=false amp-carousel=true ids={{id1}},{{id2}},{{id3}}]',
				'<style type="text/css"> #gallery-1 { margin: auto; } #gallery-1 .gallery-item { float: left; margin-top: 10px; text-align: center; width: 33%; } #gallery-1 img { border: 2px solid #cfcfcf; } #gallery-1 .gallery-caption { margin-left: 0; } /* see gallery_shortcode() in wp-includes/media.php */ </style>
				<amp-carousel width="640" height="480" type="slides" layout="responsive">
					<figure class="slide"><a href="{{file_url1}}"><img width="100" height="100" src="{{file1}}.jpg" class="attachment-large size-large" alt="Alt text" aria-describedby="gallery-1-{{id1}}" layout="fill" object-fit="cover"></a>' . $amp_carousel_caption . '</figure>
					<figure class="slide"><a href="{{file_url2}}"><img width="640" height="480" src="{{file2}}.jpg" class="attachment-large size-large" alt="Alt text" srcset="{{file2}}.jpg 640w, {{file2}}-300x225.jpg 300w" sizes="(max-width: 640px) 100vw, 640px" layout="fill" object-fit="cover"></a></figure>
					<figure class="slide"><a href="{{file_url3}}"><img width="100" height="100" src="{{file3}}.jpg" class="attachment-large size-large" alt="Alt text" layout="fill" object-fit="cover"></a></figure>
				</amp-carousel>',
			],
			'shortcode_with_carousel_linking_to_file' => [
				'[gallery amp-lightbox=false amp-carousel=true link="file" ids={{id1}},{{id2}},{{id3}}]',
				'<style type="text/css"> #gallery-1 { margin: auto; } #gallery-1 .gallery-item { float: left; margin-top: 10px; text-align: center; width: 33%; } #gallery-1 img { border: 2px solid #cfcfcf; } #gallery-1 .gallery-caption { margin-left: 0; } /* see gallery_shortcode() in wp-includes/media.php */ </style>
				<amp-carousel width="640" height="480" type="slides" layout="responsive">
					<figure class="slide"><a href="{{file1}}.jpg"><img width="100" height="100" src="{{file1}}.jpg" class="attachment-large size-large" alt="Alt text" aria-describedby="gallery-1-{{id1}}" layout="fill" object-fit="cover"></a>' . $amp_carousel_caption . '</figure>
					<figure class="slide"><a href="{{file2}}.jpg"><img width="640" height="480" src="{{file2}}.jpg" class="attachment-large size-large" alt="Alt text" srcset="{{file2}}.jpg 640w, {{file2}}-300x225.jpg 300w" sizes="(max-width: 640px) 100vw, 640px" layout="fill" object-fit="cover"></a></figure>
					<figure class="slide"><a href="{{file3}}.jpg"><img width="100" height="100" src="{{file3}}.jpg" class="attachment-large size-large" alt="Alt text" layout="fill" object-fit="cover"></a></figure>
				</amp-carousel>',
			],
			'shortcode_with_lightbox'                 => [
				'[gallery amp-lightbox=true amp-carousel=false ids={{id1}},{{id2}},{{id3}}]',
				'<style type="text/css"> #gallery-1 { margin: auto; } #gallery-1 .gallery-item { float: left; margin-top: 10px; text-align: center; width: 33%; } #gallery-1 img { border: 2px solid #cfcfcf; } #gallery-1 .gallery-caption { margin-left: 0; } /* see gallery_shortcode() in wp-includes/media.php */ </style>
				<div data-amp-lightbox="true" id="gallery-1" class="gallery galleryid-0 gallery-columns-3 gallery-size-thumbnail">
					<dl class="gallery-item"><dt class="gallery-icon landscape">
						<img width="100" height="100" src="{{file1}}.jpg" class="attachment-thumbnail size-thumbnail" alt="Alt text" aria-describedby="gallery-1-{{id1}}" lightbox="">
					</dt><dd class="wp-caption-text gallery-caption" id="gallery-1-{{id1}}"> ' . self::CAPTION_TEXT . ' </dd></dl>
					<dl class="gallery-item"><dt class="gallery-icon landscape">
						<img width="150" height="150" src="{{file2}}-150x150.jpg" class="attachment-thumbnail size-thumbnail" alt="Alt text" lightbox="">
					</dt></dl>
					<dl class="gallery-item"><dt class="gallery-icon landscape">
						<img width="100" height="100" src="{{file3}}.jpg" class="attachment-thumbnail size-thumbnail" alt="Alt text" lightbox="">
					</dt></dl>
					<br style="clear: both">
				</div>',
			],
			'shortcode_with_lightbox_linking_to_file' => [
				'[gallery amp-lightbox=true amp-carousel=false link="file" ids={{id1}},{{id2}},{{id3}}]',
				'<style type="text/css"> #gallery-1 { margin: auto; } #gallery-1 .gallery-item { float: left; margin-top: 10px; text-align: center; width: 33%; } #gallery-1 img { border: 2px solid #cfcfcf; } #gallery-1 .gallery-caption { margin-left: 0; } /* see gallery_shortcode() in wp-includes/media.php */ </style>
				<div data-amp-lightbox="true" id="gallery-1" class="gallery galleryid-0 gallery-columns-3 gallery-size-thumbnail">
					<dl class="gallery-item"><dt class="gallery-icon landscape">
						<img width="100" height="100" src="{{file1}}.jpg" class="attachment-thumbnail size-thumbnail" alt="Alt text" aria-describedby="gallery-1-{{id1}}" lightbox="">
					</dt><dd class="wp-caption-text gallery-caption" id="gallery-1-{{id1}}"> ' . self::CAPTION_TEXT . ' </dd></dl>
					<dl class="gallery-item"><dt class="gallery-icon landscape">
						<img width="150" height="150" src="{{file2}}-150x150.jpg" class="attachment-thumbnail size-thumbnail" alt="Alt text" lightbox="">
					</dt></dl>
					<dl class="gallery-item"><dt class="gallery-icon landscape">
						<img width="100" height="100" src="{{file3}}.jpg" class="attachment-thumbnail size-thumbnail" alt="Alt text" lightbox="">
					</dt></dl>
					<br style="clear: both">
				</div>',
			],
			'shortcode_with_lightbox_and_carousel'    => [
				'[gallery amp-lightbox=true amp-carousel=true ids={{id1}},{{id2}},{{id3}}]',
				'<style type="text/css"> #gallery-1 { margin: auto; } #gallery-1 .gallery-item { float: left; margin-top: 10px; text-align: center; width: 33%; } #gallery-1 img { border: 2px solid #cfcfcf; } #gallery-1 .gallery-caption { margin-left: 0; } /* see gallery_shortcode() in wp-includes/media.php */ </style>
				<amp-carousel width="640" height="480" type="slides" layout="responsive" lightbox="">' .
					'<figure class="slide"><img width="100" height="100" src="{{file1}}.jpg" class="attachment-large size-large" alt="Alt text" aria-describedby="gallery-1-{{id1}}" layout="fill" object-fit="cover">' . $amp_carousel_caption . '</figure>' .
					'<figure class="slide"><img width="640" height="480" src="{{file2}}.jpg" class="attachment-large size-large" alt="Alt text" srcset="{{file2}}.jpg 640w, {{file2}}-300x225.jpg 300w" sizes="(max-width: 640px) 100vw, 640px" layout="fill" object-fit="cover"></figure>' .
					'<figure class="slide"><img width="100" height="100" src="{{file3}}.jpg" class="attachment-large size-large" alt="Alt text" layout="fill" object-fit="cover"></figure>' .
				'</amp-carousel>',
			],
			'shortcode_with_lightbox_and_carousel_linking_to_file' => [
				'[gallery amp-lightbox=true amp-carousel=true link="file" ids={{id1}},{{id2}},{{id3}}]',
				'<style type="text/css"> #gallery-1 { margin: auto; } #gallery-1 .gallery-item { float: left; margin-top: 10px; text-align: center; width: 33%; } #gallery-1 img { border: 2px solid #cfcfcf; } #gallery-1 .gallery-caption { margin-left: 0; } /* see gallery_shortcode() in wp-includes/media.php */ </style>
				<amp-carousel width="640" height="480" type="slides" layout="responsive" lightbox="">' .
					'<figure class="slide"><img width="100" height="100" src="{{file1}}.jpg" class="attachment-large size-large" alt="Alt text" aria-describedby="gallery-1-{{id1}}" layout="fill" object-fit="cover">' . $amp_carousel_caption . '</figure>' .
					'<figure class="slide"><img width="640" height="480" src="{{file2}}.jpg" class="attachment-large size-large" alt="Alt text" srcset="{{file2}}.jpg 640w, {{file2}}-300x225.jpg 300w" sizes="(max-width: 640px) 100vw, 640px" layout="fill" object-fit="cover"></figure>' .
					'<figure class="slide"><img width="100" height="100" src="{{file3}}.jpg" class="attachment-large size-large" alt="Alt text" layout="fill" object-fit="cover"></figure>' .
				'</amp-carousel>',
			],
			'shortcode_with_no_attributes'            => [
				'[gallery]',
				// Note the same three attachments will be used here because when the gallery shortcode lacks an ids attribute, it uses unattached photos.
				'<style type="text/css"> #gallery-1 { margin: auto; } #gallery-1 .gallery-item { float: left; margin-top: 10px; text-align: center; width: 33%; } #gallery-1 img { border: 2px solid #cfcfcf; } #gallery-1 .gallery-caption { margin-left: 0; } /* see gallery_shortcode() in wp-includes/media.php */ </style>
				<div id="gallery-1" class="gallery galleryid-0 gallery-columns-3 gallery-size-thumbnail">
					<dl class="gallery-item">
						<dt class="gallery-icon landscape"><a href="{{file_url1}}"><img width="100" height="100" src="{{file1}}.jpg" class="attachment-thumbnail size-thumbnail" alt="Alt text" aria-describedby="gallery-1-{{id1}}"></a></dt>
						<dd class="wp-caption-text gallery-caption" id="gallery-1-{{id1}}"> ' . self::CAPTION_TEXT . ' </dd>
					</dl>
					<dl class="gallery-item">
						<dt class="gallery-icon landscape"><a href="{{file_url2}}"><img width="150" height="150" src="{{file2}}-150x150.jpg" class="attachment-thumbnail size-thumbnail" alt="Alt text"></a></dt>
					</dl>
					<dl class="gallery-item">
						<dt class="gallery-icon landscape"><a href="{{file_url3}}"><img width="100" height="100" src="{{file3}}.jpg" class="attachment-thumbnail size-thumbnail" alt="Alt text"></a></dt>
					</dl>
					<br style="clear: both">
				</div>',
			],
		];
	}

	/**
	 * Test conversion.
	 *
	 * @dataProvider get_conversion_data
	 * @param string $source   Source.
	 * @param string $expected Expected.
	 * @param bool   $use_legacy_mode Whether to use legacy Reader mode.
	 */
	public function test__conversion( $source, $expected, $use_legacy_mode = false ) {
		$source_files = [
			AMP__DIR__ . '/tests/e2e/assets/small-image-100-100.jpg',
			DIR_TESTDATA . '/images/canola.jpg',
			DIR_TESTDATA . '/images/gradient-square.jpg',
		];

		if ( $use_legacy_mode ) {
			AMP_Options_Manager::update_option( Option::READER_THEME, ReaderThemes::DEFAULT_READER_THEME );
			AMP_Options_Manager::update_option( Option::THEME_SUPPORT, AMP_Theme_Support::READER_MODE_SLUG );
		} else {
			if ( ! wp_get_theme( 'twentyseventeen' )->exists() ) {
				$this->markTestSkipped( 'Test depends on Twenty Seventeen being installed.' );
			}
			AMP_Options_Manager::update_option( Option::READER_THEME, 'twentyseventeen' );
		}

		// When we generate new attachments, we neither control the IDs being
		// generated, which we need for the gallery shortcode, nor the actual
		// filenames, which will have an index appended because of filename
		// collisions. So we write the tests in a way that they don't rely on
		// this, and str-replace the dynamic portions into the expected and
		// actual output after the fact.
		$ids       = [];
		$files     = [];
		$file_urls = [];

		foreach ( $source_files as $index => $source_file ) {
			$ids[ $index ] = self::factory()->attachment
				->create_upload_object( $source_file, 0 );

			$files[ $index ] = rtrim(
				wp_get_attachment_url( $ids[ $index ] ),
				'.jpg'
			);

			$file_urls[ $index ] = get_attachment_link( $ids[ $index ] );

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

		$this->initialize_replacements( $ids, $files, $file_urls );

		$embed = new AMP_Gallery_Embed_Handler();
		$embed->register_embed();

		$filtered_content = apply_filters(
			'the_content',
			$this->normalize( $source )
		);

		$dom = AMP_DOM_Utils::get_dom_from_content( $filtered_content );
		$embed->sanitize_raw_embeds( $dom );

		$content = AMP_DOM_Utils::get_content_from_dom( $dom );

		// Normalize auto-incrementing ID.
		$content = preg_replace( '/\bgallery-\d+/', 'gallery-1', $content );

		// @TODO: Handle this more appropriately when webp is supported in WP core.
		// replace .webp with .jpg.
		$content = str_replace( '-jpg.webp', '.jpg', $content );
		$content = str_replace( 'jpg.webp', '.jpg', $content );

		// Remove lazy loading attribute.
		$content = preg_replace( '/\s+loading="lazy"/', '', $content );

		// Remove fetchpriority attribute.
		$content = preg_replace( '/\s+fetchpriority="high"/', '', $content );

		// Remove decoding attribute.
		$content = preg_replace( '/\s+decoding="async"/', '', $content );

		// Auto sizes was added in WordPress 6.7
		if ( version_compare( strtok( get_bloginfo( 'version' ), '-' ), '6.7', '>=' ) ) {
			$expected = str_replace( ' sizes="', ' sizes="auto, ', $expected );
		}

		$this->assertEquals(
			$this->normalize( $expected ),
			$this->normalize( $content )
		);
	}

	/**
	 * Initialize the associative array of replacements to perform.
	 *
	 * @param array<int>    $ids       Array of attachment post IDs.
	 * @param array<string> $files     Array of file URLs.
	 * @param array<string> $file_urls Array of file permalink URLs.
	 */
	private function initialize_replacements( $ids, $files, $file_urls ) {
		$this->replacements = [
			'{{id1}}'       => $ids[0],
			'{{id2}}'       => $ids[1],
			'{{id3}}'       => $ids[2],
			'{{file1}}'     => $files[0],
			'{{file2}}'     => $files[1],
			'{{file3}}'     => $files[2],
			'{{file_url1}}' => $file_urls[0],
			'{{file_url2}}' => $file_urls[1],
			'{{file_url3}}' => $file_urls[2],
			"\n"            => '', // Make tests ignore new lines.
			'> <'           => '><', // Remove left-over space between elements.
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
		$content = str_replace( '<style type="text/css">', '<style type="text/css">', $content );

		// Then we go through all previously defined replacements.
		return str_replace(
			array_keys( $this->replacements ),
			$this->replacements,
			$content
		);
	}
}
