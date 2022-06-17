<?php
/**
 * Class AMP_Gallery_Block_Sanitizer_Test.
 *
 * @package AMP
 */

use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;
use AmpProject\AmpWP\Tests\TestCase;
use AmpProject\AmpWP\Tests\Helpers\MarkupComparison;

/**
 * Class AMP_Gallery_Block_Sanitizer_Test
 */
class AMP_Gallery_Block_Sanitizer_Test extends TestCase {

	use PrivateAccess, MarkupComparison;

	/**
	 * Get data.
	 *
	 * @return array
	 */
	public function get_data() {
		return [
			'no_ul'                                   => [
				'<p>Lorem Ipsum Demet Delorit.</p>',
				'<p>Lorem Ipsum Demet Delorit.</p>',
			],

			'no_a_no_amp_img'                         => [
				'<ul class="amp-carousel"><div></div></ul>',
				'<ul class="amp-carousel"><div></div></ul>',
			],

			'no_amp_carousel'                         => [
				'<ul><a><amp-img></amp-img></a></ul>',
				'<ul><a><amp-img></amp-img></a></ul>',
			],

			'no_block_class'                          => [
				'<ul data-amp-carousel="true"><li class="blocks-gallery-item"><figure><a href="http://example.com"><amp-img src="http://example.com/img.png" width="600" height="400"></amp-img></a></figure></li></ul>',
				'<ul data-amp-carousel="true"><li class="blocks-gallery-item"><figure><a href="http://example.com"><amp-img src="http://example.com/img.png" width="600" height="400"></amp-img></a></figure></li></ul>',
			],

			'data_amp_with_carousel_and_link'         => [
				'<ul class="wp-block-gallery" data-amp-carousel="true"><li class="blocks-gallery-item"><figure><a href="http://example.com"><amp-img src="http://example.com/img.png" width="600" height="400"></amp-img></a></figure></li></ul>',
				'<amp-carousel width="600" height="400" type="slides" layout="responsive"><figure class="slide"><a href="http://example.com"><amp-img src="http://example.com/img.png" width="600" height="400" layout="fill" object-fit="cover"></amp-img></a></figure></amp-carousel>',
			],

			'data_amp_with_carousel_and_caption'      => [
				'<ul class="wp-block-gallery" data-amp-carousel="true"><li class="blocks-gallery-item"><figure><amp-img src="http://example.com/img.png" width="600" height="400"></amp-img><figcaption>This is a caption</figcaption></figure></li></ul>',
				'<amp-carousel width="600" height="400" type="slides" layout="responsive"><figure class="slide"><amp-img src="http://example.com/img.png" width="600" height="400" layout="fill" object-fit="cover"></amp-img><figcaption class="amp-wp-gallery-caption">This is a caption</figcaption></figure></amp-carousel>',
			],

			// WordPress 5.3 changed the markup for the Gallery block, wrapping it in a <figure>.
			'data_amp_with_carousel_caption_5_3'      => [
				'<figure class="wp-block-gallery" data-amp-carousel="true"><ul><li class="blocks-gallery-item"><figure><amp-img src="http://example.com/img.png" width="600" height="400"></amp-img><figcaption>This is a caption</figcaption></figure></li></ul></figure>',
				'<figure class="wp-block-gallery" data-amp-carousel="true"><amp-carousel width="600" height="400" type="slides" layout="responsive"><figure class="slide"><amp-img src="http://example.com/img.png" width="600" height="400" layout="fill" object-fit="cover"></amp-img><figcaption class="amp-wp-gallery-caption">This is a caption</figcaption></figure></amp-carousel></figure>',
			],

			'data_amp_with_lightbox'                  => [
				'<ul class="wp-block-gallery" data-amp-lightbox="true"><li class="blocks-gallery-item"><figure><amp-img src="http://example.com/img.png" width="600" height="400"></amp-img></figure></li></ul>',
				'<ul class="wp-block-gallery" data-amp-lightbox="true"><li class="blocks-gallery-item"><figure><amp-img src="http://example.com/img.png" width="600" height="400" lightbox=""></amp-img></figure></li></ul>',
			],

			'data_amp_with_lightbox_and_link'         => [
				'<ul class="wp-block-gallery" data-amp-lightbox="true"><li class="blocks-gallery-item"><figure><a href="http://example.com"><amp-img src="http://example.com/img.png" width="600" height="400"></amp-img></a></figure></li></ul>',
				'<ul class="wp-block-gallery" data-amp-lightbox="true"><li class="blocks-gallery-item"><figure><a href="http://example.com"><amp-img src="http://example.com/img.png" width="600" height="400" lightbox=""></amp-img></a></figure></li></ul>',
			],

			'data_amp_with_lightbox_5_3'              => [
				'<figure class="wp-block-gallery" data-amp-lightbox="true"><ul><li class="blocks-gallery-item"><figure><amp-img src="http://example.com/img.png" width="600" height="400"></amp-img></figure></li></ul></figure>',
				'<figure class="wp-block-gallery" data-amp-lightbox="true"><ul><li class="blocks-gallery-item"><figure><amp-img src="http://example.com/img.png" width="600" height="400" lightbox=""></amp-img></figure></li></ul></figure>',
			],

			'data_amp_with_lightbox_and_link_5_3'     => [
				'<figure class="wp-block-gallery" data-amp-lightbox="true"><ul><li class="blocks-gallery-item"><figure><a href="http://example.com"><amp-img src="http://example.com/img.png" width="600" height="400"></amp-img></a></figure></li></ul></figure>',
				'<figure class="wp-block-gallery" data-amp-lightbox="true"><ul><li class="blocks-gallery-item"><figure><a href="http://example.com"><amp-img src="http://example.com/img.png" width="600" height="400" lightbox=""></amp-img></a></figure></li></ul></figure>',
			],

			'data_amp_with_lightbox_and_carousel'     => [
				'<ul class="wp-block-gallery" data-amp-lightbox="true" data-amp-carousel="true"><li class="blocks-gallery-item"><figure><a href="http://example.com"><amp-img src="http://example.com/img.png" width="1234" height="567"></amp-img></a></figure></li></ul>',
				'<amp-carousel width="1234" height="567" type="slides" layout="responsive" lightbox=""><figure class="slide"><amp-img src="http://example.com/img.png" width="1234" height="567" layout="fill" object-fit="cover"></amp-img></figure></amp-carousel>',
			],

			'data_amp_with_lightbox_carousel_5_3'     => [
				'<figure class="wp-block-gallery" data-amp-lightbox="true" data-amp-carousel="true"><ul><li class="blocks-gallery-item"><figure><a href="http://example.com"><amp-img src="http://example.com/img.png" width="1234" height="567"></amp-img></a></figure></li></ul></figure>',
				'<figure class="wp-block-gallery" data-amp-lightbox="true" data-amp-carousel="true"><amp-carousel width="1234" height="567" type="slides" layout="responsive" lightbox=""><figure class="slide"><amp-img src="http://example.com/img.png" width="1234" height="567" layout="fill" object-fit="cover"></amp-img></figure></amp-carousel></figure>',
			],

			'data_amp_with_lightbox_carousel_gallery_caption_5_3' => [
				'<figure class="wp-block-gallery" data-amp-lightbox="true" data-amp-carousel="true"><ul><li class="blocks-gallery-item"><figure><a href="http://example.com"><amp-img src="http://example.com/img.png" width="1234" height="567"></amp-img></a></figure></li></ul><figcaption>This is the gallery caption</figcaption></figure>',
				'<figure class="wp-block-gallery" data-amp-lightbox="true" data-amp-carousel="true"><amp-carousel width="1234" height="567" type="slides" layout="responsive" lightbox=""><figure class="slide"><amp-img src="http://example.com/img.png" width="1234" height="567" layout="fill" object-fit="cover"></amp-img></figure></amp-carousel><figcaption>This is the gallery caption</figcaption></figure>',
			],

			'carousel_from_wp59_gallery_block_markup' => [
				'
				<figure class="wp-block-gallery has-nested-images columns-default is-cropped" data-amp-carousel="true">
					<figure class="wp-block-image size-large">
						<a href="https://example.com/one-scaled.jpg"><amp-img width="2560" height="1920" data-id="4002" src="https://example.com/one-scaled.jpg" alt="" class="wp-image-4002" srcset="https://example.com/one-scaled.jpg 2560w, https://example.com/one-300x225.jpg 300w, https://example.com/one-1024x768.jpg 1024w, https://example.com/one-768x576.jpg 768w, https://example.com/one-1536x1152.jpg 1536w, https://example.com/one-2048x1536.jpg 2048w, https://example.com/one-1568x1176.jpg 1568w" sizes="(max-width: 2560px) 100vw, 2560px" layout="fill" object-fit="cover"></amp-img></a>
						<figcaption>Sunset</figcaption>
					</figure>
					<figure class="wp-block-image size-large">
						<a href="https://example.com/two.jpg"><amp-img width="640" height="853" data-id="3004" src="https://example.com/two.jpg" alt="" class="wp-image-3004" srcset="https://example.com/two.jpg 640w, https://example.com/two-225x300.jpg 225w, https://example.com/two-150x200.jpg 150w" sizes="(max-width: 640px) 100vw, 640px" layout="fill" object-fit="cover"></amp-img></a>
						<figcaption>Bison</figcaption>
					</figure>
					<figure class="wp-block-image size-large">
						<a href="https://example.com/three-scaled.jpg"><amp-img width="2560" height="1920" data-id="2270" src="https://example.com/three-scaled.jpg" alt="" class="wp-image-2270" srcset="https://example.com/three-scaled.jpg 2560w, https://example.com/three-300x225.jpg 300w, https://example.com/three-1024x768.jpg 1024w, https://example.com/three-768x576.jpg 768w, https://example.com/three-1536x1152.jpg 1536w, https://example.com/three-2048x1536.jpg 2048w, https://example.com/three-1200x900.jpg 1200w" sizes="(max-width: 2560px) 100vw, 2560px" layout="fill" object-fit="cover"></amp-img></a>
						<figcaption>Sunset</figcaption>
					</figure>
					<figcaption class="blocks-gallery-caption">Gallery caption!</figcaption>
				</figure>
				',
				'
				<figure class="wp-block-gallery has-nested-images columns-default is-cropped" data-amp-carousel="true">
					<amp-carousel width="2560" height="1920" type="slides" layout="responsive">
						<figure class="slide"><a href="https://example.com/one-scaled.jpg">
							<amp-img width="2560" height="1920" data-id="4002" src="https://example.com/one-scaled.jpg" alt="" class="wp-image-4002" srcset="https://example.com/one-scaled.jpg 2560w, https://example.com/one-300x225.jpg 300w, https://example.com/one-1024x768.jpg 1024w, https://example.com/one-768x576.jpg 768w, https://example.com/one-1536x1152.jpg 1536w, https://example.com/one-2048x1536.jpg 2048w, https://example.com/one-1568x1176.jpg 1568w" sizes="(max-width: 2560px) 100vw, 2560px" layout="fill" object-fit="cover"></amp-img>
						</a></figure>
						<figure class="slide"><a href="https://example.com/two.jpg">
							<amp-img width="640" height="853" data-id="3004" src="https://example.com/two.jpg" alt="" class="wp-image-3004" srcset="https://example.com/two.jpg 640w, https://example.com/two-225x300.jpg 225w, https://example.com/two-150x200.jpg 150w" sizes="(max-width: 640px) 100vw, 640px" layout="fill" object-fit="cover"></amp-img>
						</a></figure>
						<figure class="slide"><a href="https://example.com/three-scaled.jpg">
							<amp-img width="2560" height="1920" data-id="2270" src="https://example.com/three-scaled.jpg" alt="" class="wp-image-2270" srcset="https://example.com/three-scaled.jpg 2560w, https://example.com/three-300x225.jpg 300w, https://example.com/three-1024x768.jpg 1024w, https://example.com/three-768x576.jpg 768w, https://example.com/three-1536x1152.jpg 1536w, https://example.com/three-2048x1536.jpg 2048w, https://example.com/three-1200x900.jpg 1200w" sizes="(max-width: 2560px) 100vw, 2560px" layout="fill" object-fit="cover"></amp-img>
						</a></figure>
					</amp-carousel>
					<figcaption class="blocks-gallery-caption">Gallery caption!</figcaption>
				</figure>
				',
			],

			'lightbox_from_wp59_gallery_block_markup' => [
				'
				<figure class="wp-block-gallery has-nested-images columns-default is-cropped" data-amp-lightbox="true">
					<figure class="wp-block-image size-large">
						<a href="https://example.com/one-scaled.jpg"><amp-img width="2560" height="1920" data-id="4002" src="https://example.com/one-scaled.jpg" alt="" class="wp-image-4002" srcset="https://example.com/one-scaled.jpg 2560w, https://example.com/one-300x225.jpg 300w, https://example.com/one-1024x768.jpg 1024w, https://example.com/one-768x576.jpg 768w, https://example.com/one-1536x1152.jpg 1536w, https://example.com/one-2048x1536.jpg 2048w, https://example.com/one-1568x1176.jpg 1568w" sizes="(max-width: 2560px) 100vw, 2560px" layout="fill" object-fit="cover"></amp-img></a>
						<figcaption>Sunset</figcaption>
					</figure>
					<figure class="wp-block-image size-large">
						<a href="https://example.com/two.jpg"><amp-img width="640" height="853" data-id="3004" src="https://example.com/two.jpg" alt="" class="wp-image-3004" srcset="https://example.com/two.jpg 640w, https://example.com/two-225x300.jpg 225w, https://example.com/two-150x200.jpg 150w" sizes="(max-width: 640px) 100vw, 640px" layout="fill" object-fit="cover"></amp-img></a>
						<figcaption>Bison</figcaption>
					</figure>
					<figure class="wp-block-image size-large">
						<a href="https://example.com/three-scaled.jpg"><amp-img width="2560" height="1920" data-id="2270" src="https://example.com/three-scaled.jpg" alt="" class="wp-image-2270" srcset="https://example.com/three-scaled.jpg 2560w, https://example.com/three-300x225.jpg 300w, https://example.com/three-1024x768.jpg 1024w, https://example.com/three-768x576.jpg 768w, https://example.com/three-1536x1152.jpg 1536w, https://example.com/three-2048x1536.jpg 2048w, https://example.com/three-1200x900.jpg 1200w" sizes="(max-width: 2560px) 100vw, 2560px" layout="fill" object-fit="cover"></amp-img></a>
						<figcaption>Sunset</figcaption>
					</figure>
					<figcaption class="blocks-gallery-caption">Gallery caption!</figcaption>
				</figure>
				',
				'
				<figure class="wp-block-gallery has-nested-images columns-default is-cropped" data-amp-lightbox="true">
					<figure class="wp-block-image size-large">
						<a href="https://example.com/one-scaled.jpg"><amp-img width="2560" height="1920" data-id="4002" src="https://example.com/one-scaled.jpg" alt="" class="wp-image-4002" srcset="https://example.com/one-scaled.jpg 2560w, https://example.com/one-300x225.jpg 300w, https://example.com/one-1024x768.jpg 1024w, https://example.com/one-768x576.jpg 768w, https://example.com/one-1536x1152.jpg 1536w, https://example.com/one-2048x1536.jpg 2048w, https://example.com/one-1568x1176.jpg 1568w" sizes="(max-width: 2560px) 100vw, 2560px" layout="fill" object-fit="cover" lightbox=""></amp-img></a>
						<figcaption>Sunset</figcaption>
					</figure>
					<figure class="wp-block-image size-large">
						<a href="https://example.com/two.jpg"><amp-img width="640" height="853" data-id="3004" src="https://example.com/two.jpg" alt="" class="wp-image-3004" srcset="https://example.com/two.jpg 640w, https://example.com/two-225x300.jpg 225w, https://example.com/two-150x200.jpg 150w" sizes="(max-width: 640px) 100vw, 640px" layout="fill" object-fit="cover" lightbox=""></amp-img></a>
						<figcaption>Bison</figcaption>
					</figure>
					<figure class="wp-block-image size-large">
						<a href="https://example.com/three-scaled.jpg"><amp-img width="2560" height="1920" data-id="2270" src="https://example.com/three-scaled.jpg" alt="" class="wp-image-2270" srcset="https://example.com/three-scaled.jpg 2560w, https://example.com/three-300x225.jpg 300w, https://example.com/three-1024x768.jpg 1024w, https://example.com/three-768x576.jpg 768w, https://example.com/three-1536x1152.jpg 1536w, https://example.com/three-2048x1536.jpg 2048w, https://example.com/three-1200x900.jpg 1200w" sizes="(max-width: 2560px) 100vw, 2560px" layout="fill" object-fit="cover" lightbox=""></amp-img></a>
						<figcaption>Sunset</figcaption>
					</figure>
					<figcaption class="blocks-gallery-caption">Gallery caption!</figcaption>
				</figure>
				',
			],

			'carousel_with_lightbox_from_wp59_gallery_block_markup' => [
				'
				<figure class="wp-block-gallery has-nested-images columns-default is-cropped" data-amp-carousel="true" data-amp-lightbox="true">
					<figure class="wp-block-image size-large">
						<a href="https://example.com/one-scaled.jpg"><amp-img width="2560" height="1920" data-id="4002" src="https://example.com/one-scaled.jpg" alt="" class="wp-image-4002" srcset="https://example.com/one-scaled.jpg 2560w, https://example.com/one-300x225.jpg 300w, https://example.com/one-1024x768.jpg 1024w, https://example.com/one-768x576.jpg 768w, https://example.com/one-1536x1152.jpg 1536w, https://example.com/one-2048x1536.jpg 2048w, https://example.com/one-1568x1176.jpg 1568w" sizes="(max-width: 2560px) 100vw, 2560px" layout="fill" object-fit="cover"></amp-img></a>
						<figcaption>Sunset</figcaption>
					</figure>
					<figure class="wp-block-image size-large">
						<a href="https://example.com/two.jpg"><amp-img width="640" height="853" data-id="3004" src="https://example.com/two.jpg" alt="" class="wp-image-3004" srcset="https://example.com/two.jpg 640w, https://example.com/two-225x300.jpg 225w, https://example.com/two-150x200.jpg 150w" sizes="(max-width: 640px) 100vw, 640px" layout="fill" object-fit="cover"></amp-img></a>
						<figcaption>Bison</figcaption>
					</figure>
					<figure class="wp-block-image size-large">
						<a href="https://example.com/three-scaled.jpg"><amp-img width="2560" height="1920" data-id="2270" src="https://example.com/three-scaled.jpg" alt="" class="wp-image-2270" srcset="https://example.com/three-scaled.jpg 2560w, https://example.com/three-300x225.jpg 300w, https://example.com/three-1024x768.jpg 1024w, https://example.com/three-768x576.jpg 768w, https://example.com/three-1536x1152.jpg 1536w, https://example.com/three-2048x1536.jpg 2048w, https://example.com/three-1200x900.jpg 1200w" sizes="(max-width: 2560px) 100vw, 2560px" layout="fill" object-fit="cover"></amp-img></a>
						<figcaption>Sunset</figcaption>
					</figure>
					<figcaption class="blocks-gallery-caption">Gallery caption!</figcaption>
				</figure>
				',
				'
				<figure class="wp-block-gallery has-nested-images columns-default is-cropped" data-amp-carousel="true" data-amp-lightbox="true">
					<amp-carousel width="2560" height="1920" type="slides" layout="responsive" lightbox="">
						<figure class="slide">
							<amp-img width="2560" height="1920" data-id="4002" src="https://example.com/one-scaled.jpg" alt="" class="wp-image-4002" srcset="https://example.com/one-scaled.jpg 2560w, https://example.com/one-300x225.jpg 300w, https://example.com/one-1024x768.jpg 1024w, https://example.com/one-768x576.jpg 768w, https://example.com/one-1536x1152.jpg 1536w, https://example.com/one-2048x1536.jpg 2048w, https://example.com/one-1568x1176.jpg 1568w" sizes="(max-width: 2560px) 100vw, 2560px" layout="fill" object-fit="cover"></amp-img>
						</figure>
						<figure class="slide">
							<amp-img width="640" height="853" data-id="3004" src="https://example.com/two.jpg" alt="" class="wp-image-3004" srcset="https://example.com/two.jpg 640w, https://example.com/two-225x300.jpg 225w, https://example.com/two-150x200.jpg 150w" sizes="(max-width: 640px) 100vw, 640px" layout="fill" object-fit="cover"></amp-img>
						</figure>
						<figure class="slide">
							<amp-img width="2560" height="1920" data-id="2270" src="https://example.com/three-scaled.jpg" alt="" class="wp-image-2270" srcset="https://example.com/three-scaled.jpg 2560w, https://example.com/three-300x225.jpg 300w, https://example.com/three-1024x768.jpg 1024w, https://example.com/three-768x576.jpg 768w, https://example.com/three-1536x1152.jpg 1536w, https://example.com/three-2048x1536.jpg 2048w, https://example.com/three-1200x900.jpg 1200w" sizes="(max-width: 2560px) 100vw, 2560px" layout="fill" object-fit="cover"></amp-img>
						</figure>
					</amp-carousel>
					<figcaption class="blocks-gallery-caption">Gallery caption!</figcaption>
				</figure>
				',
			],
		];
	}

	/**
	 * Test sanitizer.
	 *
	 * This only tests when theme support is present.
	 * Like if Standard or Transitional is selected in AMP Settings > Template Mode,
	 * or if this is added with add_theme_support( 'amp' ).
	 * If there is no theme support, the sanitizer will have the argument array( 'carousel_required' => true ).
	 *
	 * @see amp_get_content_sanitizers()
	 * @dataProvider get_data
	 * @param string $source Source.
	 * @param string $expected Expected.
	 */
	public function test_sanitizer( $source, $expected ) {
		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Gallery_Block_Sanitizer(
			$dom,
			[ 'content_max_width' => 600 ]
		);
		$sanitizer->sanitize();
		$content = AMP_DOM_Utils::get_content_from_dom( $dom );

		$this->assertEqualMarkup( $expected, $content, "Expected content:\n$content" );
	}

	/**
	 * Get the Reader mode data.
	 *
	 * @return array
	 */
	public function get_reader_mode_data() {
		return [
			'no_block_class'                      => [
				'<ul data-amp-carousel="true"><li class="blocks-gallery-item"><figure><a href="http://example.com"><amp-img src="http://example.com/img.png" width="600" height="400"></amp-img></a></figure></li></ul>',
				'<ul data-amp-carousel="true"><li class="blocks-gallery-item"><figure><a href="http://example.com"><amp-img src="http://example.com/img.png" width="600" height="400"></amp-img></a></figure></li></ul>',
			],

			'data_amp_with_carousel_and_caption'  => [
				'<ul class="wp-block-gallery" data-amp-carousel="true"><li class="blocks-gallery-item"><figure><a href="http://example.com"><amp-img src="http://example.com/img.png" width="600" height="400"></amp-img></a><figcaption>Here is a caption</figcaption></figure></li></ul>',
				'<amp-carousel width="600" height="400" type="slides" layout="responsive"><figure class="slide"><a href="http://example.com"><amp-img src="http://example.com/img.png" width="600" height="400" layout="fill" object-fit="cover"></amp-img></a><figcaption class="amp-wp-gallery-caption">Here is a caption</figcaption></figure></amp-carousel>',
			],

			'data_amp_with_lightbox'              => [
				'<ul class="wp-block-gallery" data-amp-lightbox="true"><li class="blocks-gallery-item"><figure><amp-img src="http://example.com/img.png" width="600" height="400"></amp-img></figure></li></ul>',
				'<amp-carousel width="600" height="400" type="slides" layout="responsive" lightbox=""><figure class="slide"><amp-img src="http://example.com/img.png" width="600" height="400" layout="fill" object-fit="cover"></amp-img></figure></amp-carousel>',
			],

			'data_amp_with_lightbox_and_link'     => [
				'<ul class="wp-block-gallery" data-amp-lightbox="true"><li class="blocks-gallery-item"><figure><a href="http://example.com"><amp-img src="http://example.com/img.png" width="600" height="400"></amp-img></a></figure></li></ul>',
				'<amp-carousel width="600" height="400" type="slides" layout="responsive" lightbox=""><figure class="slide"><amp-img src="http://example.com/img.png" width="600" height="400" layout="fill" object-fit="cover"></amp-img></figure></amp-carousel>',
			],

			'data_amp_lightbox_carousel_and_link' => [
				'<ul class="wp-block-gallery" data-amp-lightbox="true" data-amp-carousel="true"><li class="blocks-gallery-item"><figure><a href="http://example.com"><amp-img src="http://example.com/img.png" width="600" height="400"></amp-img></a></figure></li></ul>',
				'<amp-carousel width="600" height="400" type="slides" layout="responsive" lightbox=""><figure class="slide"><amp-img src="http://example.com/img.png" width="600" height="400" layout="fill" object-fit="cover"></amp-img></figure></amp-carousel>',
			],
		];
	}

	/**
	 * Test an native img tag has not layout or object-fit attributes.
	 *
	 * `layout` and `object-fit` will be replaced with a style attribute.
	 *
	 * @covers \AMP_Gallery_Block_Sanitizer::sanitize()
	 */
	public function test_native_img_tag_has_not_layout_or_object_fit_attrs() {
		$source   = '<amp-carousel width="600" height="400" type="slides" layout="responsive" lightbox=""><figure class="slide"><img src="http://example.com/img.png" width="600" height="400" layout="fill" object-fit="cover"></figure></amp-carousel>';
		$expected = '<amp-carousel width="600" height="400" type="slides" layout="responsive" lightbox=""><figure class="slide"><img src="http://example.com/img.png" width="600" height="400" style="position:absolute; left:0; right:0; top:0; bottom: 0; width:100%; height:100%; object-fit:cover;"></figure></amp-carousel>';

		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Gallery_Block_Sanitizer(
			$dom,
			[
				'native_img_used'   => true,
				'carousel_required' => true,
			]
		);
		$sanitizer->sanitize();

		$sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$sanitizer->sanitize();
		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$this->assertEquals( $expected, $content );
	}

	/**
	 * Test the sanitizer in Reader mode (without theme support).
	 *
	 * The tested sanitizer will have an argument of array( 'carousel_required' => true ),
	 * which sometimes causes different output.
	 *
	 * @see amp_get_content_sanitizers()
	 * @dataProvider get_reader_mode_data
	 * @param string $source Source.
	 * @param string $expected Expected.
	 */
	public function test_sanitizer_reader_mode( $source, $expected ) {
		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Gallery_Block_Sanitizer(
			$dom,
			[ 'carousel_required' => true ]
		);
		$sanitizer->sanitize();
		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$content = preg_replace( '/(?<=>)\s+(?=<)/', '', $content );
		$this->assertEquals( $expected, $content );
	}

	/**
	 * Gets the data for test_possibly_get_caption_text().
	 *
	 * @return array[] The source to use, the expected return value, and the tag type to pass as an argument.
	 */
	public function get_caption_text_data() {
		return [
			'no_amp_img_or_anchor'       => [
				'<div><img src="https://example.com/image.jpg"></div>',
				null,
				'img',
			],
			'amp_img_with_empty_caption' => [
				'<amp-img src="https://example.com/image.jpg"></amp-img><figcaption></figcaption>',
				null,
				'amp-img',
			],
			'amp_img_with_caption'       => [
				'<amp-img src="https://example.com/image.jpg"></amp-img><figcaption>This is a caption</figcaption>',
				'<figcaption>This is a caption</figcaption>',
				'amp-img',
			],
			'amp_img_with_html_caption'  => [
				'<amp-img src="https://example.com/image.jpg"></amp-img><figcaption><span class="foobar">This is a caption</span></figcaption>',
				'<figcaption><span class="foobar">This is a caption</span></figcaption>',
				'amp-img',
			],
			'amp_img_wrapped_in_anchor_with_caption_in_div' => [
				'<a href="https://example.com"><amp-img src="https://example.com/image.jpg"></amp-img></a><div>This is a caption</div>',
				null,
				'a',
			],
			'amp_img_wrapped_in_anchor_with_caption_in_figcaption' => [
				'<a href="https://example.com"><amp-img src="https://example.com/image.jpg"></amp-img></a><figcaption>This is a caption</figcaption>',
				'<figcaption>This is a caption</figcaption>',
				'a',
			],
		];
	}

	/**
	 * Test possibly_get_caption_text.
	 *
	 * @covers \AMP_Gallery_Block_Sanitizer::get_caption_element()
	 *
	 * @dataProvider get_caption_text_data
	 * @param string      $source The markup source to test.
	 * @param string|null $expected The expected return value of the tested method.
	 * @param string      $element_name The name of the element to pass to the tested method.
	 */
	public function test_possibly_get_caption_text( $source, $expected, $element_name ) {
		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$element   = $dom->getElementsByTagName( $element_name )->item( 0 );
		$sanitizer = new AMP_Gallery_Block_Sanitizer(
			$dom,
			[ 'content_max_width' => 600 ]
		);

		$actual = $this->call_private_method( $sanitizer, 'get_caption_element', [ $element ] );

		if ( $actual instanceof DOMElement ) {
			$actual = $dom->saveHTML( $actual );
		}

		$this->assertEquals( $expected, $actual );
	}
}
