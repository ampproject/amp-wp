<?php
/**
 * Class AMP_Gallery_Block_Sanitizer_Test.
 *
 * @package AMP
 */

use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;

/**
 * Class AMP_Gallery_Block_Sanitizer_Test
 */
class AMP_Gallery_Block_Sanitizer_Test extends WP_UnitTestCase {

	use PrivateAccess;

	/**
	 * Get data.
	 *
	 * @return array
	 */
	public function get_data() {
		return [
			'no_ul'                               => [
				'<p>Lorem Ipsum Demet Delorit.</p>',
				'<p>Lorem Ipsum Demet Delorit.</p>',
			],

			'no_a_no_amp_img'                     => [
				'<ul class="amp-carousel"><div></div></ul>',
				'<ul class="amp-carousel"><div></div></ul>',
			],

			'no_amp_carousel'                     => [
				'<ul><a><amp-img></amp-img></a></ul>',
				'<ul><a><amp-img></amp-img></a></ul>',
			],

			'no_block_class'                      => [
				'<ul data-amp-carousel="true"><li class="blocks-gallery-item"><figure><a href="http://example.com"><amp-img src="http://example.com/img.png" width="600" height="400"></amp-img></a></figure></li></ul>',
				'<ul data-amp-carousel="true"><li class="blocks-gallery-item"><figure><a href="http://example.com"><amp-img src="http://example.com/img.png" width="600" height="400"></amp-img></a></figure></li></ul>',
			],

			'data_amp_with_carousel_and_link'     => [
				'<ul class="wp-block-gallery" data-amp-carousel="true"><li class="blocks-gallery-item"><figure><a href="http://example.com"><amp-img src="http://example.com/img.png" width="600" height="400"></amp-img></a></figure></li></ul>',
				'<amp-carousel width="600" height="400" type="slides" layout="responsive"><figure class="slide"><a href="http://example.com"><amp-img src="http://example.com/img.png" width="600" height="400" layout="fill" object-fit="cover"></amp-img></a></figure></amp-carousel>',
			],

			'data_amp_with_carousel_and_caption'  => [
				'<ul class="wp-block-gallery" data-amp-carousel="true"><li class="blocks-gallery-item"><figure><amp-img src="http://example.com/img.png" width="600" height="400"></amp-img><figcaption>This is a caption</figcaption></figure></li></ul>',
				'<amp-carousel width="600" height="400" type="slides" layout="responsive"><figure class="slide"><amp-img src="http://example.com/img.png" width="600" height="400" layout="fill" object-fit="cover"></amp-img><figcaption class="amp-wp-gallery-caption">This is a caption</figcaption></figure></amp-carousel>',
			],

			// WordPress 5.3 changed the markup for the Gallery block, wrapping it in a <figure>.
			'data_amp_with_carousel_caption_5_3'  => [
				'<figure class="wp-block-gallery" data-amp-carousel="true"><ul><li class="blocks-gallery-item"><figure><amp-img src="http://example.com/img.png" width="600" height="400"></amp-img><figcaption>This is a caption</figcaption></figure></li></ul></figure>',
				'<figure class="wp-block-gallery" data-amp-carousel="true"><amp-carousel width="600" height="400" type="slides" layout="responsive"><figure class="slide"><amp-img src="http://example.com/img.png" width="600" height="400" layout="fill" object-fit="cover"></amp-img><figcaption class="amp-wp-gallery-caption">This is a caption</figcaption></figure></amp-carousel></figure>',
			],

			'data_amp_with_lightbox'              => [
				'<ul class="wp-block-gallery" data-amp-lightbox="true"><li class="blocks-gallery-item"><figure><amp-img src="http://example.com/img.png" width="600" height="400"></amp-img></figure></li></ul>',
				'<ul class="wp-block-gallery" data-amp-lightbox="true"><li class="blocks-gallery-item"><figure><amp-img src="http://example.com/img.png" width="600" height="400" lightbox=""></amp-img></figure></li></ul>',
			],

			'data_amp_with_lightbox_and_link'     => [
				'<ul class="wp-block-gallery" data-amp-lightbox="true"><li class="blocks-gallery-item"><figure><a href="http://example.com"><amp-img src="http://example.com/img.png" width="600" height="400"></amp-img></a></figure></li></ul>',
				'<ul class="wp-block-gallery" data-amp-lightbox="true"><li class="blocks-gallery-item"><figure><a href="http://example.com"><amp-img src="http://example.com/img.png" width="600" height="400" lightbox=""></amp-img></a></figure></li></ul>',
			],

			'data_amp_with_lightbox_5_3'          => [
				'<figure class="wp-block-gallery" data-amp-lightbox="true"><ul><li class="blocks-gallery-item"><figure><amp-img src="http://example.com/img.png" width="600" height="400"></amp-img></figure></li></ul></figure>',
				'<figure class="wp-block-gallery" data-amp-lightbox="true"><ul><li class="blocks-gallery-item"><figure><amp-img src="http://example.com/img.png" width="600" height="400" lightbox=""></amp-img></figure></li></ul></figure>',
			],

			'data_amp_with_lightbox_and_link_5_3' => [
				'<figure class="wp-block-gallery" data-amp-lightbox="true"><ul><li class="blocks-gallery-item"><figure><a href="http://example.com"><amp-img src="http://example.com/img.png" width="600" height="400"></amp-img></a></figure></li></ul></figure>',
				'<figure class="wp-block-gallery" data-amp-lightbox="true"><ul><li class="blocks-gallery-item"><figure><a href="http://example.com"><amp-img src="http://example.com/img.png" width="600" height="400" lightbox=""></amp-img></a></figure></li></ul></figure>',
			],

			'data_amp_with_lightbox_and_carousel' => [
				'<ul class="wp-block-gallery" data-amp-lightbox="true" data-amp-carousel="true"><li class="blocks-gallery-item"><figure><a href="http://example.com"><amp-img src="http://example.com/img.png" width="1234" height="567"></amp-img></a></figure></li></ul>',
				'<amp-carousel width="1234" height="567" type="slides" layout="responsive" lightbox=""><figure class="slide"><amp-img src="http://example.com/img.png" width="1234" height="567" layout="fill" object-fit="cover"></amp-img></figure></amp-carousel>',
			],

			'data_amp_with_lightbox_carousel_5_3' => [
				'<figure class="wp-block-gallery" data-amp-lightbox="true" data-amp-carousel="true"><ul><li class="blocks-gallery-item"><figure><a href="http://example.com"><amp-img src="http://example.com/img.png" width="1234" height="567"></amp-img></a></figure></li></ul></figure>',
				'<figure class="wp-block-gallery" data-amp-lightbox="true" data-amp-carousel="true"><amp-carousel width="1234" height="567" type="slides" layout="responsive" lightbox=""><figure class="slide"><amp-img src="http://example.com/img.png" width="1234" height="567" layout="fill" object-fit="cover"></amp-img></figure></amp-carousel></figure>',
			],

			'data_amp_with_lightbox_carousel_gallery_caption_5_3' => [
				'<figure class="wp-block-gallery" data-amp-lightbox="true" data-amp-carousel="true"><ul><li class="blocks-gallery-item"><figure><a href="http://example.com"><amp-img src="http://example.com/img.png" width="1234" height="567"></amp-img></a></figure></li></ul><figcaption>This is the gallery caption</figcaption></figure>',
				'<figure class="wp-block-gallery" data-amp-lightbox="true" data-amp-carousel="true"><amp-carousel width="1234" height="567" type="slides" layout="responsive" lightbox=""><figure class="slide"><amp-img src="http://example.com/img.png" width="1234" height="567" layout="fill" object-fit="cover"></amp-img></figure></amp-carousel><figcaption>This is the gallery caption</figcaption></figure>',
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
		$content = preg_replace( '/(?<=>)\s+(?=<)/', '', $content );
		$this->assertEquals( $expected, $content );
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
