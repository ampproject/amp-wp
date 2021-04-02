<?php
/**
 * Class AMP_Img_Sanitizer_Test
 *
 * @package AMP
 */

use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;

/**
 * Class AMP_Img_Sanitizer_Test
 *
 * @covers AMP_Img_Sanitizer
 */
class AMP_Img_Sanitizer_Test extends WP_UnitTestCase {

	use PrivateAccess;

	/**
	 * Set up.
	 */
	public function setUp() {
		parent::setUp();
		add_filter(
			'amp_extract_image_dimensions_batch',
			static function( $urls ) {
				$dimensions = [];
				foreach ( array_keys( $urls ) as $url ) {
					if ( preg_match( '#/(?P<width>\d+)x(?P<height>\d+)$#', $url, $matches ) ) {
						$dimensions[ $url ] = array_map( 'intval', wp_array_slice_assoc( $matches, [ 'width', 'height' ] ) );
					} else {
						$dimensions[ $url ] = false;
					}
				}
				return $dimensions;
			}
		);
	}

	/**
	 * Data for test_converter.
	 *
	 * @return array
	 */
	public function get_data() {
		return [
			'no_images'                                => [
				'<p>Lorem Ipsum Demet Delorit.</p>',
				'<p>Lorem Ipsum Demet Delorit.</p>',
			],

			'simple_image'                             => [
				'<p><img src="https://placehold.it/300x300" width="300" height="300" class="align-center" id="placeholder" style="height:auto" /></p>',
				'<p><amp-img src="https://placehold.it/300x300" width="300" height="300" class="align-center amp-wp-enforced-sizes" id="placeholder" style="height:auto" layout="intrinsic"><noscript><img src="https://placehold.it/300x300" width="300" height="300"></noscript></amp-img></p>',
				[
					'add_noscript_fallback' => true,
				],
			],

			'image_with_new_platform_attributes'       => [
				'<img src="https://placehold.it/150x300" width="150" height="300" importance="low" intrinsicsize="150x300" loading="lazy">',
				'<amp-img src="https://placehold.it/150x300" width="150" height="300" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/150x300" width="150" height="300" importance="low" intrinsicsize="150x300" loading="lazy"></noscript></amp-img>',
				[
					'add_noscript_fallback' => true,
				],
			],

			'image_with_decoding_attribute_of_async'   => [
				'<img src="https://placehold.it/150x300" width="150" height="300" decoding="async">',
				'<amp-img src="https://placehold.it/150x300" width="150" height="300" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/150x300" width="150" height="300" decoding="async"></noscript></amp-img>',
				[
					'add_noscript_fallback' => true,
				],
			],

			'image_with_loading_attribute_of_lazy'     => [
				'<img src="https://placehold.it/150x300" width="150" height="300" loading="lazy">',
				'<amp-img src="https://placehold.it/150x300" width="150" height="300" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/150x300" width="150" height="300" loading="lazy"></noscript></amp-img>',
				[
					'add_noscript_fallback' => true,
				],
			],

			'image_with_wrong_decoding_and_loading'    => [
				'<img src="https://placehold.it/150x300" width="150" height="300" decoding="sync" loading="eager">',
				'<amp-img src="https://placehold.it/150x300" width="150" height="300" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/150x300" width="150" height="300" decoding="sync" loading="eager"></noscript></amp-img>',
				[
					'add_noscript_fallback' => true,
				],
				array_fill( 0, 2, AMP_Tag_And_Attribute_Sanitizer::DISALLOWED_ATTR ),
			],

			'simple_image_without_noscript'            => [
				'<p><img src="http://placehold.it/300x300" width="300" height="300" /></p>',
				'<p><amp-img src="http://placehold.it/300x300" width="300" height="300" class="amp-wp-enforced-sizes" layout="intrinsic"></amp-img></p>',
				[
					'add_noscript_fallback' => false,
				],
			],

			'image_without_src'                        => [
				'<p><img width="300" height="300" /></p>',
				'<p></p>',
				[],
				[ AMP_Tag_And_Attribute_Sanitizer::ATTR_REQUIRED_BUT_MISSING ],
			],

			'image_with_empty_src'                     => [
				'<p><img src="" width="300" height="300" /></p>',
				'<p></p>',
				[],
				[ AMP_Tag_And_Attribute_Sanitizer::ATTR_REQUIRED_BUT_MISSING ],
			],

			'image_with_layout'                        => [
				'<img src="https://placehold.it/100x100" data-amp-layout="fill" width="100" height="100" />',
				'<amp-img src="https://placehold.it/100x100" layout="fill" width="100" height="100" class="amp-wp-enforced-sizes"><noscript><img src="https://placehold.it/100x100" width="100" height="100"></noscript></amp-img>',
			],

			'image_with_noloading'                     => [
				'<img src="https://placehold.it/100x100" data-amp-noloading="" width="100" height="100">',
				'<amp-img src="https://placehold.it/100x100" noloading="" width="100" height="100" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/100x100" width="100" height="100"></noscript></amp-img>',
			],

			'image_with_layout_from_editor'            => [
				'<figure data-amp-layout="fill"><img src="https://placehold.it/300x300" height="300" width="300" /></figure>',
				'<figure data-amp-layout="fill" style="position:relative; width: 100%; height: 300px;"><amp-img src="https://placehold.it/300x300" layout="fill" class="amp-wp-enforced-sizes"><noscript><img src="https://placehold.it/300x300" height="300" width="300"></noscript></amp-img></figure>',
			],

			'image_with_noloading_from_editor'         => [
				'<figure data-amp-noloading="true"><img src="https://placehold.it/300x300" height="300" width="300" /></figure>',
				'<figure data-amp-noloading="true"><amp-img src="https://placehold.it/300x300" height="300" width="300" noloading="" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/300x300" height="300" width="300"></noscript></amp-img></figure>',
			],

			'image_with_spaces_only_src'               => [
				'<p><img src="    " width="300" height="300" /></p>',
				'<p></p>',
				[],
				[ AMP_Tag_And_Attribute_Sanitizer::ATTR_REQUIRED_BUT_MISSING ],
			],

			'image_with_empty_width_and_height'        => [
				'<p><img src="https://placehold.it/200x300" width="" height="" /></p>',
				'<p><amp-img src="https://placehold.it/200x300" width="200" height="300" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/200x300" width="200" height="300"></noscript></amp-img></p>',
			],

			'image_with_undefined_width_and_height'    => [
				'<p><img src="https://placehold.it/200x300" /></p>',
				'<p><amp-img src="https://placehold.it/200x300" width="200" height="300" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/200x300" width="200" height="300"></noscript></amp-img></p>',
			],

			'image_with_empty_width'                   => [
				'<p><img src="https://placehold.it/500x1000" width="" height="300" /></p>',
				'<p><amp-img src="https://placehold.it/500x1000" width="150" height="300" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/500x1000" width="150" height="300"></noscript></amp-img></p>',
			],

			'image_with_empty_height'                  => [
				'<p><img src="https://placehold.it/500x1000" width="300" height="" /></p>',
				'<p><amp-img src="https://placehold.it/500x1000" width="300" height="600" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/500x1000" width="300" height="600"></noscript></amp-img></p>',
			],

			'image_with_zero_width'                    => [
				'<p><img src="https://placehold.it/300x300" width="0" height="300" /></p>',
				'<p><amp-img src="https://placehold.it/300x300" width="0" height="300" class="amp-wp-enforced-sizes"><noscript><img src="https://placehold.it/300x300" width="0" height="300"></noscript></amp-img></p>',
			],

			'image_with_zero_width_and_height'         => [
				'<p><img src="https://placehold.it/300x300" width="0" height="0" /></p>',
				'<p><amp-img src="https://placehold.it/300x300" width="0" height="0" class="amp-wp-enforced-sizes"><noscript><img src="https://placehold.it/300x300" width="0" height="0"></noscript></amp-img></p>',
			],

			'image_with_decimal_width'                 => [
				'<p><img src="https://placehold.it/300x300" width="299.5" height="300" /></p>',
				'<p><amp-img src="https://placehold.it/300x300" width="299.5" height="300" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/300x300" width="299.5" height="300"></noscript></amp-img></p>',
			],

			'image_with_self_closing_tag'              => [
				'<img src="https://placehold.it/350x150" width="350" height="150" alt="Placeholder!" />',
				'<amp-img src="https://placehold.it/350x150" width="350" height="150" alt="Placeholder!" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/350x150" width="350" height="150" alt="Placeholder!"></noscript></amp-img>',
			],

			'image_with_no_end_tag'                    => [
				'<img src="https://placehold.it/350x150" width="350" height="150" alt="Placeholder!">',
				'<amp-img src="https://placehold.it/350x150" width="350" height="150" alt="Placeholder!" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/350x150" width="350" height="150" alt="Placeholder!"></noscript></amp-img>',
			],

			'image_with_end_tag'                       => [
				'<img src="https://placehold.it/350x150" width="350" height="150" alt="Placeholder!"></img>',
				'<amp-img src="https://placehold.it/350x150" width="350" height="150" alt="Placeholder!" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/350x150" width="350" height="150" alt="Placeholder!"></noscript></amp-img>',
			],

			'image_with_extra_attributes'              => [
				'<img src="https://placehold.it/350x150" on="tap:my-lightbox" onclick="showLightbox()" media="(min-width: 650px)" role="button" itemscope="image" tabindex="0" width="350" height="150" alt="ALT!" />',
				'<amp-img src="https://placehold.it/350x150" on="tap:my-lightbox" media="(min-width: 650px)" role="button" itemscope="image" tabindex="0" width="350" height="150" alt="ALT!" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/350x150" on="tap:my-lightbox" role="button" itemscope="image" tabindex="0" width="350" height="150" alt="ALT!"></noscript></amp-img>',
				[],
				[
					AMP_Tag_And_Attribute_Sanitizer::DISALLOWED_ATTR, // The onclick attribute.
				],
			],

			'image_with_no_dimensions_is_forced'       => [
				'<img src="https://placehold.it/350x150" />',
				'<amp-img src="https://placehold.it/350x150" width="350" height="150" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/350x150" width="350" height="150"></noscript></amp-img>',
			],

			'image_with_bad_src_url_get_fallback_dims' => [
				'<img src="https://example.com/404.png" />',
				'<amp-img src="https://example.com/404.png" object-fit="contain" width="' . AMP_Img_Sanitizer::FALLBACK_WIDTH . '" height="' . AMP_Img_Sanitizer::FALLBACK_HEIGHT . '" class="amp-wp-unknown-size amp-wp-unknown-width amp-wp-unknown-height amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://example.com/404.png" width="' . AMP_Img_Sanitizer::FALLBACK_WIDTH . '" height="' . AMP_Img_Sanitizer::FALLBACK_HEIGHT . '"></noscript></amp-img>',
			],

			'gif_image_conversion'                     => [
				'<img src="https://placehold.it/350x150.gif" width="350" height="150" alt="Placeholder!" />',
				'<amp-anim src="https://placehold.it/350x150.gif" width="350" height="150" alt="Placeholder!" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/350x150.gif" width="350" height="150" alt="Placeholder!"></noscript></amp-anim>',
			],

			'gif_image_url_with_querystring'           => [
				'<img src="https://placehold.it/350x150.gif?foo=bar" width="350" height="150" alt="Placeholder!" />',
				'<amp-anim src="https://placehold.it/350x150.gif?foo=bar" width="350" height="150" alt="Placeholder!" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/350x150.gif?foo=bar" width="350" height="150" alt="Placeholder!"></noscript></amp-anim>',
			],

			'multiple_same_image'                      => [
				'<img src="https://placehold.it/350x150" width="350" height="150" /><img src="https://placehold.it/350x150" width="350" height="150" /><img src="https://placehold.it/350x150" width="350" height="150" /><img src="https://placehold.it/350x150" width="350" height="150" />',
				'<amp-img src="https://placehold.it/350x150" width="350" height="150" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/350x150" width="350" height="150"></noscript></amp-img><amp-img src="https://placehold.it/350x150" width="350" height="150" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/350x150" width="350" height="150"></noscript></amp-img><amp-img src="https://placehold.it/350x150" width="350" height="150" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/350x150" width="350" height="150"></noscript></amp-img><amp-img src="https://placehold.it/350x150" width="350" height="150" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/350x150" width="350" height="150"></noscript></amp-img>',
			],

			'multiple_different_images'                => [
				'<img src="https://placehold.it/350x150" width="350" height="150" /><img src="https://placehold.it/360x160" width="360" height="160" /><img src="https://placehold.it/370x170" width="370" height="170" /><img src="https://placehold.it/380x180" width="380" height="180" />',
				'<amp-img src="https://placehold.it/350x150" width="350" height="150" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/350x150" width="350" height="150"></noscript></amp-img><amp-img src="https://placehold.it/360x160" width="360" height="160" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/360x160" width="360" height="160"></noscript></amp-img><amp-img src="https://placehold.it/370x170" width="370" height="170" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/370x170" width="370" height="170"></noscript></amp-img><amp-img src="https://placehold.it/380x180" width="380" height="180" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/380x180" width="380" height="180"></noscript></amp-img>',
			],

			'image_center_aligned'                     => [
				'<img class="aligncenter" src="https://placehold.it/350x150" width="350" height="150" />',
				'<amp-img class="aligncenter amp-wp-enforced-sizes" src="https://placehold.it/350x150" width="350" height="150" layout="intrinsic"><noscript><img src="https://placehold.it/350x150" width="350" height="150"></noscript></amp-img>',
			],

			'image_left_aligned'                       => [
				'<img class="alignleft" src="https://placehold.it/350x150" width="350" height="150" />',
				'<amp-img class="alignleft amp-wp-enforced-sizes" src="https://placehold.it/350x150" width="350" height="150" layout="intrinsic"><noscript><img src="https://placehold.it/350x150" width="350" height="150"></noscript></amp-img>',
			],

			'image_with_caption'                       => [
				'<figure class="wp-caption aligncenter"><img src="https://placehold.it/350x150" alt="" width="350" height="150" class="size-medium wp-image-312"><figcaption class="wp-caption-text">This is an example caption.</figcaption></figure>',
				'<figure class="wp-caption aligncenter"><amp-img src="https://placehold.it/350x150" alt="" width="350" height="150" class="size-medium wp-image-312 amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/350x150" alt="" width="350" height="150"></noscript></amp-img><figcaption class="wp-caption-text">This is an example caption.</figcaption></figure>',
			],

			'image_with_custom_lightbox_attrs'         => [
				'<figure data-amp-lightbox="true"><img src="https://placehold.it/100x100" width="100" height="100" data-foo="bar" role="button" tabindex="0" /></figure>',
				'<figure data-amp-lightbox="true"><amp-img src="https://placehold.it/100x100" width="100" height="100" data-foo="bar" role="button" tabindex="0" data-amp-lightbox="" lightbox="" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/100x100" width="100" height="100" role="button" tabindex="0"></noscript></amp-img></figure>',
			],

			'wide_image'                               => [
				'<figure class="wp-block-image"><img src="https://placehold.it/580x300" alt="Image Alignment 580x300" class="wp-image-967" /></figure>',
				'<figure class="wp-block-image"><amp-img src="https://placehold.it/580x300" alt="Image Alignment 580x300" class="wp-image-967 amp-wp-enforced-sizes" width="580" height="300" layout="intrinsic"><noscript><img src="https://placehold.it/580x300" alt="Image Alignment 580x300" width="580" height="300"></noscript></amp-img></figure>',
			],

			'wide_image_center_aligned'                => [
				'<figure class="wp-block-image aligncenter"><img src="https://placehold.it/580x300" alt="Image Alignment 580x300" class="wp-image-967" /></figure>',
				'<figure class="wp-block-image aligncenter"><amp-img src="https://placehold.it/580x300" alt="Image Alignment 580x300" class="wp-image-967 amp-wp-enforced-sizes" width="580" height="300" layout="intrinsic"><noscript><img src="https://placehold.it/580x300" alt="Image Alignment 580x300" width="580" height="300"></noscript></amp-img></figure>',
			],

			'wide_image_left_aligned_custom_style'     => [
				'<figure class="wp-block-image alignleft" style="border:solid 1px red;"><img src="https://placehold.it/580x300" alt="Image Alignment 580x300" class="wp-image-967" /></figure>',
				'<figure class="wp-block-image alignleft" style="border:solid 1px red;"><amp-img src="https://placehold.it/580x300" alt="Image Alignment 580x300" class="wp-image-967 amp-wp-enforced-sizes" width="580" height="300" layout="intrinsic"><noscript><img src="https://placehold.it/580x300" alt="Image Alignment 580x300" width="580" height="300"></noscript></amp-img></figure>',
			],

			'wide_image_right_aligned'                 => [
				'<figure class="wp-block-image alignright"><img src="https://placehold.it/580x300" alt="Image Alignment 580x300" class="wp-image-967" /></figure>',
				'<figure class="wp-block-image alignright"><amp-img src="https://placehold.it/580x300" alt="Image Alignment 580x300" class="wp-image-967 amp-wp-enforced-sizes" width="580" height="300" layout="intrinsic"><noscript><img src="https://placehold.it/580x300" alt="Image Alignment 580x300" width="580" height="300"></noscript></amp-img></figure>',
			],

			'wide_image_is_resized'                    => [
				'<figure class="wp-block-image is-resized"><img src="https://placehold.it/580x300" alt="Image Alignment 580x300" class="wp-image-967" /></figure>',
				'<figure class="wp-block-image is-resized"><amp-img src="https://placehold.it/580x300" alt="Image Alignment 580x300" class="wp-image-967 amp-wp-enforced-sizes" width="580" height="300" layout="intrinsic"><noscript><img src="https://placehold.it/580x300" alt="Image Alignment 580x300" width="580" height="300"></noscript></amp-img></figure>',
			],

			'amp_img_with_noscript_fallback'           => [
				'<amp-img src="https://placehold.it/100x100" layout="fixed" width="100" height="100"><noscript><img src="https://placehold.it/100x100" width="100" height="100"></noscript></amp-img>',
				null,
			],

			'img_with_sizes_attribute_kept'            => [
				'<img width="825" height="510" src="https://placehold.it/825x510" class="attachment-post-thumbnail size-post-thumbnail wp-post-image" alt="" sizes="(max-width: 34.9rem) calc(100vw - 2rem), (max-width: 53rem) calc(8 * (100vw / 12)), (min-width: 53rem) calc(6 * (100vw / 12)), 100vw">',
				'<amp-img width="825" height="510" src="https://placehold.it/825x510" class="attachment-post-thumbnail size-post-thumbnail wp-post-image amp-wp-enforced-sizes" alt="" sizes="(max-width: 34.9rem) calc(100vw - 2rem), (max-width: 53rem) calc(8 * (100vw / 12)), (min-width: 53rem) calc(6 * (100vw / 12)), 100vw" layout="intrinsic" disable-inline-width=""><noscript><img width="825" height="510" src="https://placehold.it/825x510" alt="" sizes="(max-width: 34.9rem) calc(100vw - 2rem), (max-width: 53rem) calc(8 * (100vw / 12)), (min-width: 53rem) calc(6 * (100vw / 12)), 100vw"></noscript></amp-img>',
			],

			'amp_img_with_sizes_attribute_retained'    => [
				'<amp-img width="825" height="510" src="https://placehold.it/825x510" alt="" layout="intrinsic"></amp-img>',
				null,
			],

			'img_with_http_protocol_src'               => [
				'<img src="http://placehold.it/350x150" width="350" height="150">',
				'<amp-img src="http://placehold.it/350x150" width="350" height="150" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="http://placehold.it/350x150" width="350" height="150"></noscript></amp-img>',
			],

			'img_with_http_protocol_srcset'            => [
				'<img src="http://placehold.it/350x150" srcset="http://placehold.it/1024x768 1024w" width="350" height="150">',
				'<amp-img src="http://placehold.it/350x150" srcset="http://placehold.it/1024x768 1024w" width="350" height="150" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="http://placehold.it/350x150" srcset="http://placehold.it/1024x768 1024w" width="350" height="150"></noscript></amp-img>',
			],

			'img_with_fill_layout_inline_style'        => [
				'<img src="https://placehold.it/20x20" data-amp-layout="fill" style="display: inline">',
				'<amp-img src="https://placehold.it/20x20" layout="fill" style="display:block" class="amp-wp-enforced-sizes"></amp-img>',
				[
					'add_noscript_fallback' => false,
				],
			],

			'img_with_intrinsic_layout_inline_style'   => [
				'<img src="https://placehold.it/20x20" width="20" height="20" style="display: inline">',
				'<amp-img src="https://placehold.it/20x20" width="20" height="20" style="display:inline-block" class="amp-wp-enforced-sizes" layout="intrinsic"></amp-img>',
				[
					'add_noscript_fallback' => false,
				],
			],

			'img_with_responsive_layout_inline_style'  => [
				'<img src="https://placehold.it/20x20" width="20" height="20" style="display: inline" data-amp-layout="responsive">',
				'<amp-img src="https://placehold.it/20x20" width="20" height="20" style="display:block" layout="responsive" class="amp-wp-enforced-sizes"></amp-img>',
				[
					'add_noscript_fallback' => false,
				],
			],

			'img_with_fixed_height_inline_style'       => [
				'<img src="https://placehold.it/20x200" height="20" width="auto" data-amp-layout="fixed-height" style="display: inline-block">',
				'<amp-img src="https://placehold.it/20x200" height="20" width="auto" layout="fixed-height" style="display:block" class="amp-wp-enforced-sizes"></amp-img>',
				[
					'add_noscript_fallback' => false,
				],
			],

			'img_with_flex_item_inline_style'          => [
				'<img src="https://placehold.it/20x200" data-amp-layout="flex-item" style="display: inline-block">',
				'<amp-img src="https://placehold.it/20x200" layout="flex-item" style="display:block" class="amp-wp-enforced-sizes"></amp-img>',
				[
					'add_noscript_fallback' => false,
				],
			],

			'img_with_nodisplay_layout_inline_style'   => [
				'<img src="https://placehold.it/20x20" data-amp-layout="nodisplay" style="display: inline">',
				'<amp-img src="https://placehold.it/20x20" layout="nodisplay" style="display:none" class="amp-wp-enforced-sizes"></amp-img>',
				[
					'add_noscript_fallback' => false,
				],
			],

			'img_static_emoji'                         => [
				'<img src="https://s.w.org/images/core/emoji/12.0.0-1/72x72/1f468-1f3fb-200d-1f4bb.png" alt="👨🏻‍💻" class="wp-smiley" style="height: 1em; max-height: 1em;">',
				'<amp-img src="https://s.w.org/images/core/emoji/12.0.0-1/72x72/1f468-1f3fb-200d-1f4bb.png" alt="👨🏻‍💻" class="wp-smiley amp-wp-enforced-sizes" style="height: 1em; max-height: 1em;" width="72" height="72" noloading="" layout="intrinsic"></amp-img>',
				[
					'add_noscript_fallback' => false,
				],
			],

			'img_with_malformed_attributes'            => [
				"<p>Are you going on a road trip to Pagudpud? Download this Infographic Guide before you go&#8230;<br /><a href=\"https://www.ourawesomeplanet.com/awesome/2012/04/pagudpud-infographic-your-awesome-journey-to-pagudpud.html\"><img alt=\"Infographic: Your Awesome Journey to Pagudpud\" width=\"640\" height=\"126\" src=\"https://i2.wp.com/www.ourawesomeplanet.com/wp-content/uploads/2006/12/pagudpudinfographicbannerad-7.jpg?w=640&#038;ssl=1\" &gt;&lt;/a&gt; &lt;p&gt;&lt;a title=\" data-jpibfi-post-excerpt=\"Patapat Viaduct  is an elevated concrete highway constructed along winding mountainside in the northernmost section of Ilocos Norte.   This viaduct was constructed to solve the problem of landslides in the area which have caused so many vehicular accidents in the past.\n...This will take you through winding mountain roads that have lush vegetation on both sides and occasional views of the sea until you reach the patapat viaduct.\n\" data-jpibfi-post-url=\"https://www.ourawesomeplanet.com/awesome/2006/12/patapat-viaduct.html\" data-jpibfi-post-title=\"Patapat Viaduct\" data-jpibfi-src=\"https://i2.wp.com/www.ourawesomeplanet.com/wp-content/uploads/2006/12/pagudpudinfographicbannerad-7.jpg?w=640&#038;ssl=1\"  data-recalc-dims=\"1\"></a></p>",
				'<p>Are you going on a road trip to Pagudpud? Download this Infographic Guide before you go…<br><a href="https://www.ourawesomeplanet.com/awesome/2012/04/pagudpud-infographic-your-awesome-journey-to-pagudpud.html"><amp-img alt="Infographic: Your Awesome Journey to Pagudpud" width="640" height="126" src="https://i2.wp.com/www.ourawesomeplanet.com/wp-content/uploads/2006/12/pagudpudinfographicbannerad-7.jpg?w=640&amp;ssl=1" title=" data-jpibfi-post-excerpt=" on="" data-jpibfi-post-url="https://www.ourawesomeplanet.com/awesome/2006/12/patapat-viaduct.html" data-jpibfi-post-title="Patapat Viaduct" data-jpibfi-src="https://i2.wp.com/www.ourawesomeplanet.com/wp-content/uploads/2006/12/pagudpudinfographicbannerad-7.jpg?w=640&amp;ssl=1" data-recalc-dims="1" class="amp-wp-enforced-sizes" layout="intrinsic"></amp-img></a></p>',
				[
					'add_noscript_fallback' => false,
				],
				array_fill( 0, 51, AMP_Tag_And_Attribute_Sanitizer::DISALLOWED_ATTR ),
			],

			'image_block_with_lightbox'                => [
				'<figure class="wp-block-image" data-amp-lightbox="true"><img src="https://placehold.it/100x100" width="100" height="100" data-foo="bar" role="button" tabindex="0" /></figure>',
				'<figure class="wp-block-image" data-amp-lightbox="true"><amp-img src="https://placehold.it/100x100" width="100" height="100" data-foo="bar" role="button" tabindex="0" data-amp-lightbox="" lightbox="" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/100x100" width="100" height="100" role="button" tabindex="0"></noscript></amp-img></figure>',
			],

			'image_block_link_attach_page_lightbox'    => [
				'<figure class="wp-block-image" data-amp-lightbox="true"><a href="https://example.com/example-image"><img src="https://placehold.it/100x100" width="100" height="100" data-foo="bar" role="button" tabindex="0" /></a></figure>',
				'<figure class="wp-block-image" data-amp-lightbox="true"><a href="https://example.com/example-image"><amp-img src="https://placehold.it/100x100" width="100" height="100" data-foo="bar" role="button" tabindex="0" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/100x100" width="100" height="100" role="button" tabindex="0"></noscript></amp-img></a></figure>',
			],

			'aligned_image_block_with_lightbox'        => [
				'<div data-amp-lightbox="true" class="wp-block-image"><figure class="alignleft is-resized"><img src="https://placehold.it/100x100" width="100" height="100" data-foo="bar" role="button" tabindex="0" /></figure></div>',
				'<div data-amp-lightbox="true" class="wp-block-image"><figure class="alignleft is-resized"><amp-img src="https://placehold.it/100x100" width="100" height="100" data-foo="bar" role="button" tabindex="0" data-amp-lightbox="" lightbox="" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/100x100" width="100" height="100" role="button" tabindex="0"></noscript></amp-img></figure></div>',
			],

			'test_with_dev_mode'                       => [
				'<img data-ampdevmode src="http://example.com/foo.png">',
				null, // No change.
				[
					'add_dev_mode' => true,
				],
			],

			'amp_story_player_with_poster'             => [
				'
				<amp-story-player layout="fixed" width="360" height="600">
					<a href="https://preview.amp.dev/documentation/examples/introduction/stories_in_amp/">
						<img src="https://amp.dev/static/samples/img/story_dog2_portrait.jpg" width="360" height="600" loading="lazy" data-amp-story-player-poster-img>
						Stories in AMP - Hello World
					</a>
				</amp-story-player>
				',
				null, // No Change.
			],
		];
	}

	/**
	 * Test converter.
	 *
	 * @covers AMP_Img_Sanitizer::sanitize()
	 * @covers AMP_Noscript_Fallback::initialize_noscript_allowed_attributes()
	 * @covers AMP_Noscript_Fallback::is_inside_amp_noscript()
	 * @covers AMP_Noscript_Fallback::append_old_node_noscript()
	 *
	 * @param string   $source               Source.
	 * @param string   $expected             Expected.
	 * @param array    $args                 Args.
	 * @param string[] $expected_error_codes Expected error codes.
	 * @dataProvider get_data
	 */
	public function test_converter( $source, $expected = null, $args = [], $expected_error_codes = [] ) {
		if ( ! $expected ) {
			$expected = $source;
		}

		$error_codes = [];

		$args = array_merge(
			[
				'use_document_element'      => true,
				'validation_error_callback' => static function( $error ) use ( &$error_codes ) {
					$error_codes[] = $error['code'];
				},
			],
			$args
		);

		$dom = AMP_DOM_Utils::get_dom_from_content( $source );
		if ( ! empty( $args['add_dev_mode'] ) ) {
			$dom->documentElement->setAttribute( AMP_Rule_Spec::DEV_MODE_ATTRIBUTE, '' );
		}

		$sanitizer = new AMP_Img_Sanitizer( $dom, $args );
		$sanitizer->sanitize();

		$sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom, $args );
		$sanitizer->sanitize();

		$this->assertEqualSets( $error_codes, $expected_error_codes );

		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$this->assertEquals( $expected, $content );
	}

	/**
	 * Test that amp-anim does not get included for a PNG.
	 *
	 * @covers AMP_Img_Sanitizer::sanitize()
	 */
	public function test_no_gif_no_image_scripts() {
		$source   = '<img src="https://placehold.it/350x150.png" width="350" height="150" alt="Placeholder!" />';
		$expected = [];

		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Img_Sanitizer( $dom );
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
	 * Test that amp-anim does get included for a GIF.
	 *
	 * @covers AMP_Img_Sanitizer::sanitize()
	 */
	public function test_no_gif_image_scripts() {
		$source   = '<img src="https://placehold.it/350x150.gif" width="350" height="150" alt="Placeholder!" />';
		$expected = [ 'amp-anim' => true ];

		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Img_Sanitizer( $dom );
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
	 * Test an Image block wrapped in an <a>, that links to the media file, with 'lightbox' selected.
	 *
	 * This should have the <a> stripped, as it interferes with the lightbox.
	 *
	 * @covers AMP_Img_Sanitizer::sanitize()
	 */
	public function test_image_block_link_to_media_file_with_lightbox() {
		$source   = sprintf( '<figure class="wp-block-image" data-amp-lightbox="true"><a href="%s"><img src="https://placehold.it/100x100" width="100" height="100" data-foo="bar" role="button" tabindex="0" /></a></figure>', wp_get_attachment_image_url( $this->get_new_attachment_id() ) );
		$expected = '<figure class="wp-block-image" data-amp-lightbox="true"><amp-img src="https://placehold.it/100x100" width="100" height="100" data-foo="bar" role="button" tabindex="0" data-amp-lightbox="" lightbox="" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/100x100" width="100" height="100" role="button" tabindex="0"></noscript></amp-img></figure>';

		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Img_Sanitizer( $dom );
		$sanitizer->sanitize();

		$sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$sanitizer->sanitize();
		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$this->assertEquals( $expected, $content );
	}

	/**
	 * Test an Image block wrapped in an <a>, that has right-alignment, links to the media file, and has 'lightbox' selected.
	 *
	 * This should have the <a> stripped, as it interferes with the lightbox.
	 *
	 * @covers AMP_Img_Sanitizer::sanitize()
	 */
	public function test_image_block_link_to_media_file_and_alignment_with_lightbox() {
		$source   = sprintf( '<div data-amp-lightbox="true" class="wp-block-image"><figure class="alignright size-large"><a href="%s"><img src="https://placehold.it/100x100" width="100" height="100" data-foo="bar" role="button" tabindex="0" /></a></figure></div>', wp_get_attachment_image_url( $this->get_new_attachment_id() ) );
		$expected = '<div data-amp-lightbox="true" class="wp-block-image"><figure class="alignright size-large"><amp-img src="https://placehold.it/100x100" width="100" height="100" data-foo="bar" role="button" tabindex="0" data-amp-lightbox="" lightbox="" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/100x100" width="100" height="100" role="button" tabindex="0"></noscript></amp-img></figure></div>';

		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Img_Sanitizer( $dom );
		$sanitizer->sanitize();

		$sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$sanitizer->sanitize();
		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$this->assertEquals( $expected, $content );
	}

	/**
	 * Gets test data for test_does_node_have_block_class(), using a <figure> element.
	 *
	 * @see AMP_Img_Sanitizer_Test::test_does_node_have_block_class()
	 * @return array Test data for function.
	 */
	public function get_data_for_node_block_class_test() {
		return [
			'has_no_class'           => [
				'<figure></figure>',
				false,
			],
			'has_wrong_class'        => [
				'<figure class="completely-wrong-class"></figure>',
				false,
			],
			'only_has_part_of_class' => [
				'<figure class="wp-block"></figure>',
				false,
			],
			'has_correct_class'      => [
				'<figure class="wp-block-image"></figure>',
				true,
			],
		];
	}

	/**
	 * Test does_node_have_block_class.
	 *
	 * @dataProvider get_data_for_node_block_class_test
	 * @covers \AMP_Img_Sanitizer::does_node_have_block_class()
	 *
	 * @param string $source The source markup to test.
	 * @param string $expected The expected return of the tested function, using the source markup.
	 */
	public function test_does_node_have_block_class( $source, $expected ) {
		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Img_Sanitizer( $dom );
		$figures   = $dom->getElementsByTagName( 'figure' );

		$this->assertEquals( $expected, $this->call_private_method( $sanitizer, 'does_node_have_block_class', [ $figures->item( 0 ) ] ) );
	}

	/**
	 * Creates a new image attachment, and gets the ID.
	 *
	 * @return int|WP_Error The new attachment ID, or WP_Error.
	 */
	public function get_new_attachment_id() {
		return self::factory()->attachment->create_object(
			'example-image.jpeg',
			self::factory()->post->create(),
			[
				'post_mime_type' => 'image/jpeg',
				'post_type'      => 'attachment',
			]
		);
	}
}
