<?php
/**
 * Class AMP_Img_Sanitizer_Test
 *
 * @package AMP
 */

/**
 * Class AMP_Img_Sanitizer_Test
 *
 * @covers AMP_Img_Sanitizer
 */
class AMP_Img_Sanitizer_Test extends WP_UnitTestCase {

	/**
	 * Set up.
	 */
	public function setUp() {
		parent::setUp();
		add_filter(
			'amp_extract_image_dimensions_batch',
			function( $urls ) {
				$dimensions = array();
				foreach ( array_keys( $urls ) as $url ) {
					if ( preg_match( '#/(?P<width>\d+)x(?P<height>\d+)$#', $url, $matches ) ) {
						$dimensions[ $url ] = array_map( 'intval', wp_array_slice_assoc( $matches, array( 'width', 'height' ) ) );
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
		return array(
			'no_images'                                => array(
				'<p>Lorem Ipsum Demet Delorit.</p>',
				'<p>Lorem Ipsum Demet Delorit.</p>',
			),

			'simple_image'                             => array(
				'<p><img src="https://placehold.it/300x300" width="300" height="300" /></p>',
				'<p><amp-img src="https://placehold.it/300x300" width="300" height="300" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/300x300" width="300" height="300"></noscript></amp-img></p>',
				array(
					'add_noscript_fallback' => true,
				),
			),

			'simple_image_without_noscript'            => array(
				'<p><img src="http://placehold.it/300x300" width="300" height="300" /></p>',
				'<p><amp-img src="http://placehold.it/300x300" width="300" height="300" class="amp-wp-enforced-sizes" layout="intrinsic"></amp-img></p>',
				array(
					'add_noscript_fallback' => false,
				),
			),

			'image_without_src'                        => array(
				'<p><img width="300" height="300" /></p>',
				'<p></p>',
				array(),
				array( 'invalid_element' ),
			),

			'image_with_empty_src'                     => array(
				'<p><img src="" width="300" height="300" /></p>',
				'<p></p>',
				array(),
				array( 'invalid_element' ),
			),

			'image_with_layout'                        => array(
				'<img src="https://placehold.it/100x100" data-amp-layout="fill" width="100" height="100" />',
				'<amp-img src="https://placehold.it/100x100" layout="fill" width="100" height="100" class="amp-wp-enforced-sizes"><noscript><img src="https://placehold.it/100x100" width="100" height="100"></noscript></amp-img>',
			),

			'image_with_noloading'                     => array(
				'<img src="https://placehold.it/100x100" data-amp-noloading="" width="100" height="100">',
				'<amp-img src="https://placehold.it/100x100" noloading="" width="100" height="100" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/100x100" width="100" height="100"></noscript></amp-img>',
			),

			'image_with_layout_from_editor'            => array(
				'<figure data-amp-layout="fill"><img src="https://placehold.it/300x300" height="300" width="300" /></figure>',
				'<figure data-amp-layout="fill" style="position:relative; width: 100%; height: 300px;"><amp-img src="https://placehold.it/300x300" layout="fill" class="amp-wp-enforced-sizes"><noscript><img src="https://placehold.it/300x300" height="300" width="300"></noscript></amp-img></figure>',
			),

			'image_with_noloading_from_editor'         => array(
				'<figure data-amp-noloading="true"><img src="https://placehold.it/300x300" height="300" width="300" /></figure>',
				'<figure data-amp-noloading="true"><amp-img src="https://placehold.it/300x300" height="300" width="300" noloading="" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/300x300" height="300" width="300"></noscript></amp-img></figure>',
			),

			'image_with_spaces_only_src'               => array(
				'<p><img src="    " width="300" height="300" /></p>',
				'<p></p>',
				array(),
				array( 'invalid_element' ),
			),

			'image_with_empty_width_and_height'        => array(
				'<p><img src="https://placehold.it/200x300" width="" height="" /></p>',
				'<p><amp-img src="https://placehold.it/200x300" width="200" height="300" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/200x300" width="200" height="300" class=""></noscript></amp-img></p>',
			),

			'image_with_undefined_width_and_height'    => array(
				'<p><img src="https://placehold.it/200x300" /></p>',
				'<p><amp-img src="https://placehold.it/200x300" width="200" height="300" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/200x300" width="200" height="300" class=""></noscript></amp-img></p>',
			),

			'image_with_empty_width'                   => array(
				'<p><img src="https://placehold.it/500x1000" width="" height="300" /></p>',
				'<p><amp-img src="https://placehold.it/500x1000" width="150" height="300" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/500x1000" width="150" height="300" class=""></noscript></amp-img></p>',
			),

			'image_with_empty_height'                  => array(
				'<p><img src="https://placehold.it/500x1000" width="300" height="" /></p>',
				'<p><amp-img src="https://placehold.it/500x1000" width="300" height="600" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/500x1000" width="300" height="600" class=""></noscript></amp-img></p>',
			),

			'image_with_zero_width'                    => array(
				'<p><img src="https://placehold.it/300x300" width="0" height="300" /></p>',
				'<p><amp-img src="https://placehold.it/300x300" width="0" height="300" class="amp-wp-enforced-sizes"><noscript><img src="https://placehold.it/300x300" width="0" height="300"></noscript></amp-img></p>',
			),

			'image_with_zero_width_and_height'         => array(
				'<p><img src="https://placehold.it/300x300" width="0" height="0" /></p>',
				'<p><amp-img src="https://placehold.it/300x300" width="0" height="0" class="amp-wp-enforced-sizes"><noscript><img src="https://placehold.it/300x300" width="0" height="0"></noscript></amp-img></p>',
			),

			'image_with_decimal_width'                 => array(
				'<p><img src="https://placehold.it/300x300" width="299.5" height="300" /></p>',
				'<p><amp-img src="https://placehold.it/300x300" width="299.5" height="300" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/300x300" width="299.5" height="300"></noscript></amp-img></p>',
			),

			'image_with_self_closing_tag'              => array(
				'<img src="https://placehold.it/350x150" width="350" height="150" alt="Placeholder!" />',
				'<amp-img src="https://placehold.it/350x150" width="350" height="150" alt="Placeholder!" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/350x150" width="350" height="150" alt="Placeholder!"></noscript></amp-img>',
			),

			'image_with_no_end_tag'                    => array(
				'<img src="https://placehold.it/350x150" width="350" height="150" alt="Placeholder!">',
				'<amp-img src="https://placehold.it/350x150" width="350" height="150" alt="Placeholder!" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/350x150" width="350" height="150" alt="Placeholder!"></noscript></amp-img>',
			),

			'image_with_end_tag'                       => array(
				'<img src="https://placehold.it/350x150" width="350" height="150" alt="Placeholder!"></img>',
				'<amp-img src="https://placehold.it/350x150" width="350" height="150" alt="Placeholder!" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/350x150" width="350" height="150" alt="Placeholder!"></noscript></amp-img>',
			),

			'image_with_extra_attributes'              => array(
				'<img src="https://placehold.it/350x150" on="tap:my-lightbox" onclick="showLightbox()" media="(min-width: 650px)" role="button" itemscope="image" tabindex="0" width="350" height="150" alt="ALT!" />',
				'<amp-img src="https://placehold.it/350x150" on="tap:my-lightbox" media="(min-width: 650px)" role="button" itemscope="image" tabindex="0" width="350" height="150" alt="ALT!" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/350x150" on="tap:my-lightbox" role="button" itemscope="image" tabindex="0" width="350" height="150" alt="ALT!"></noscript></amp-img>',
				array(),
				array(
					'invalid_attribute', // The onclick attribute.
				),
			),

			'image_with_no_dimensions_is_forced'       => array(
				'<img src="https://placehold.it/350x150" />',
				'<amp-img src="https://placehold.it/350x150" width="350" height="150" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/350x150" width="350" height="150" class=""></noscript></amp-img>',
			),

			'image_with_bad_src_url_get_fallback_dims' => array(
				'<img src="https://example.com/404.png" />',
				'<amp-img src="https://example.com/404.png" width="' . AMP_Img_Sanitizer::FALLBACK_WIDTH . '" height="' . AMP_Img_Sanitizer::FALLBACK_HEIGHT . '" class="amp-wp-unknown-size amp-wp-unknown-width amp-wp-unknown-height amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://example.com/404.png" width="' . AMP_Img_Sanitizer::FALLBACK_WIDTH . '" height="' . AMP_Img_Sanitizer::FALLBACK_HEIGHT . '" class="amp-wp-unknown-size amp-wp-unknown-width amp-wp-unknown-height"></noscript></amp-img>',
			),

			'gif_image_conversion'                     => array(
				'<img src="https://placehold.it/350x150.gif" width="350" height="150" alt="Placeholder!" />',
				'<amp-anim src="https://placehold.it/350x150.gif" width="350" height="150" alt="Placeholder!" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/350x150.gif" width="350" height="150" alt="Placeholder!"></noscript></amp-anim>',
			),

			'gif_image_url_with_querystring'           => array(
				'<img src="https://placehold.it/350x150.gif?foo=bar" width="350" height="150" alt="Placeholder!" />',
				'<amp-anim src="https://placehold.it/350x150.gif?foo=bar" width="350" height="150" alt="Placeholder!" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/350x150.gif?foo=bar" width="350" height="150" alt="Placeholder!"></noscript></amp-anim>',
			),

			'multiple_same_image'                      => array(
				'<img src="https://placehold.it/350x150" width="350" height="150" /><img src="https://placehold.it/350x150" width="350" height="150" /><img src="https://placehold.it/350x150" width="350" height="150" /><img src="https://placehold.it/350x150" width="350" height="150" />',
				'<amp-img src="https://placehold.it/350x150" width="350" height="150" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/350x150" width="350" height="150"></noscript></amp-img><amp-img src="https://placehold.it/350x150" width="350" height="150" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/350x150" width="350" height="150"></noscript></amp-img><amp-img src="https://placehold.it/350x150" width="350" height="150" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/350x150" width="350" height="150"></noscript></amp-img><amp-img src="https://placehold.it/350x150" width="350" height="150" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/350x150" width="350" height="150"></noscript></amp-img>',
			),

			'multiple_different_images'                => array(
				'<img src="https://placehold.it/350x150" width="350" height="150" /><img src="https://placehold.it/360x160" width="360" height="160" /><img src="https://placehold.it/370x170" width="370" height="170" /><img src="https://placehold.it/380x180" width="380" height="180" />',
				'<amp-img src="https://placehold.it/350x150" width="350" height="150" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/350x150" width="350" height="150"></noscript></amp-img><amp-img src="https://placehold.it/360x160" width="360" height="160" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/360x160" width="360" height="160"></noscript></amp-img><amp-img src="https://placehold.it/370x170" width="370" height="170" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/370x170" width="370" height="170"></noscript></amp-img><amp-img src="https://placehold.it/380x180" width="380" height="180" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/380x180" width="380" height="180"></noscript></amp-img>',
			),

			'image_center_aligned'                     => array(
				'<img class="aligncenter" src="https://placehold.it/350x150" width="350" height="150" />',
				'<amp-img class="aligncenter amp-wp-enforced-sizes" src="https://placehold.it/350x150" width="350" height="150" layout="intrinsic"><noscript><img class="aligncenter" src="https://placehold.it/350x150" width="350" height="150"></noscript></amp-img>',
			),

			'image_left_aligned'                       => array(
				'<img class="alignleft" src="https://placehold.it/350x150" width="350" height="150" />',
				'<amp-img class="alignleft amp-wp-enforced-sizes" src="https://placehold.it/350x150" width="350" height="150" layout="intrinsic"><noscript><img class="alignleft" src="https://placehold.it/350x150" width="350" height="150"></noscript></amp-img>',
			),

			'image_with_caption'                       => array(
				'<figure class="wp-caption aligncenter"><img src="https://placehold.it/350x150" alt="" width="350" height="150" class="size-medium wp-image-312"><figcaption class="wp-caption-text">This is an example caption.</figcaption></figure>',
				'<figure class="wp-caption aligncenter"><amp-img src="https://placehold.it/350x150" alt="" width="350" height="150" class="size-medium wp-image-312 amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/350x150" alt="" width="350" height="150" class="size-medium wp-image-312"></noscript></amp-img><figcaption class="wp-caption-text">This is an example caption.</figcaption></figure>',
			),

			'image_with_custom_lightbox_attrs'         => array(
				'<figure data-amp-lightbox="true"><img src="https://placehold.it/100x100" width="100" height="100" data-foo="bar" role="button" tabindex="0" /></figure>',
				'<figure data-amp-lightbox="true"><amp-img src="https://placehold.it/100x100" width="100" height="100" data-foo="bar" role="button" tabindex="0" data-amp-lightbox="" on="tap:amp-image-lightbox" class="amp-wp-enforced-sizes" layout="intrinsic"><noscript><img src="https://placehold.it/100x100" width="100" height="100" role="button" tabindex="0"></noscript></amp-img></figure><amp-image-lightbox id="amp-image-lightbox" layout="nodisplay" data-close-button-aria-label="Close"></amp-image-lightbox>',
			),

			'wide_image'                               => array(
				'<figure class="wp-block-image"><img src="https://placehold.it/580x300" alt="Image Alignment 580x300" class="wp-image-967" /></figure>',
				'<figure class="wp-block-image"><amp-img src="https://placehold.it/580x300" alt="Image Alignment 580x300" class="wp-image-967 amp-wp-enforced-sizes" width="580" height="300" layout="intrinsic"><noscript><img src="https://placehold.it/580x300" alt="Image Alignment 580x300" class="wp-image-967" width="580" height="300"></noscript></amp-img></figure>',
			),

			'wide_image_center_aligned'                => array(
				'<figure class="wp-block-image aligncenter"><img src="https://placehold.it/580x300" alt="Image Alignment 580x300" class="wp-image-967" /></figure>',
				'<figure class="wp-block-image aligncenter"><amp-img src="https://placehold.it/580x300" alt="Image Alignment 580x300" class="wp-image-967 amp-wp-enforced-sizes" width="580" height="300" layout="intrinsic"><noscript><img src="https://placehold.it/580x300" alt="Image Alignment 580x300" class="wp-image-967" width="580" height="300"></noscript></amp-img></figure>',
			),

			'wide_image_left_aligned_custom_style'     => array(
				'<figure class="wp-block-image alignleft" style="border:solid 1px red;"><img src="https://placehold.it/580x300" alt="Image Alignment 580x300" class="wp-image-967" /></figure>',
				'<figure class="wp-block-image alignleft" style="border:solid 1px red;"><amp-img src="https://placehold.it/580x300" alt="Image Alignment 580x300" class="wp-image-967 amp-wp-enforced-sizes" width="580" height="300" layout="intrinsic"><noscript><img src="https://placehold.it/580x300" alt="Image Alignment 580x300" class="wp-image-967" width="580" height="300"></noscript></amp-img></figure>',
			),

			'wide_image_right_aligned'                 => array(
				'<figure class="wp-block-image alignright"><img src="https://placehold.it/580x300" alt="Image Alignment 580x300" class="wp-image-967" /></figure>',
				'<figure class="wp-block-image alignright"><amp-img src="https://placehold.it/580x300" alt="Image Alignment 580x300" class="wp-image-967 amp-wp-enforced-sizes" width="580" height="300" layout="intrinsic"><noscript><img src="https://placehold.it/580x300" alt="Image Alignment 580x300" class="wp-image-967" width="580" height="300"></noscript></amp-img></figure>',
			),

			'wide_image_is_resized'                    => array(
				'<figure class="wp-block-image is-resized"><img src="https://placehold.it/580x300" alt="Image Alignment 580x300" class="wp-image-967" /></figure>',
				'<figure class="wp-block-image is-resized"><amp-img src="https://placehold.it/580x300" alt="Image Alignment 580x300" class="wp-image-967 amp-wp-enforced-sizes" width="580" height="300" layout="intrinsic"><noscript><img src="https://placehold.it/580x300" alt="Image Alignment 580x300" class="wp-image-967" width="580" height="300"></noscript></amp-img></figure>',
			),

			'amp_img_with_noscript_fallback'           => array(
				'<amp-img src="https://placehold.it/100x100" layout="fixed" width="100" height="100"><noscript><img src="https://placehold.it/100x100" width="100" height="100"></noscript></amp-img>',
				null,
			),

			'img_with_sizes_attribute_removed'         => array(
				'<img width="825" height="510" src="https://placehold.it/825x510" class="attachment-post-thumbnail size-post-thumbnail wp-post-image" alt="" sizes="(max-width: 34.9rem) calc(100vw - 2rem), (max-width: 53rem) calc(8 * (100vw / 12)), (min-width: 53rem) calc(6 * (100vw / 12)), 100vw">',
				'<amp-img width="825" height="510" src="https://placehold.it/825x510" class="attachment-post-thumbnail size-post-thumbnail wp-post-image amp-wp-enforced-sizes" alt="" layout="intrinsic"><noscript><img width="825" height="510" src="https://placehold.it/825x510" class="attachment-post-thumbnail size-post-thumbnail wp-post-image" alt="" sizes="(max-width: 34.9rem) calc(100vw - 2rem), (max-width: 53rem) calc(8 * (100vw / 12)), (min-width: 53rem) calc(6 * (100vw / 12)), 100vw"></noscript></amp-img>',
			),

			'amp_img_with_sizes_attribute_retained'    => array(
				'<amp-img width="825" height="510" src="https://placehold.it/825x510" alt="" layout="intrinsic"></amp-img>',
				null,
			),

			'img_with_http_protocol_src'               => array(
				'<img src="http://placehold.it/350x150" width="350" height="150">',
				'<amp-img src="http://placehold.it/350x150" width="350" height="150" class="amp-wp-enforced-sizes" layout="intrinsic"></amp-img>',
			),

			'img_with_http_protocol_srcset'            => array(
				'<img src="https://placehold.it/350x150" srcset="http://placehold.it/1024x768 1024w" width="350" height="150">',
				'<amp-img src="https://placehold.it/350x150" srcset="http://placehold.it/1024x768 1024w" width="350" height="150" class="amp-wp-enforced-sizes" layout="intrinsic"></amp-img>',
			),

			'img_with_fill_layout_inline_style'        => array(
				'<img src="https://placehold.it/20x20" data-amp-layout="fill" style="display: inline">',
				'<amp-img src="https://placehold.it/20x20" layout="fill" style="display:block" class="amp-wp-enforced-sizes"></amp-img>',
				array(
					'add_noscript_fallback' => false,
				),
			),

			'img_with_intrinsic_layout_inline_style'   => array(
				'<img src="https://placehold.it/20x20" width="20" height="20" style="display: inline">',
				'<amp-img src="https://placehold.it/20x20" width="20" height="20" style="display:inline-block" class="amp-wp-enforced-sizes" layout="intrinsic"></amp-img>',
				array(
					'add_noscript_fallback' => false,
				),
			),

			'img_with_responsive_layout_inline_style'  => array(
				'<img src="https://placehold.it/20x20" width="20" height="20" style="display: inline" data-amp-layout="responsive">',
				'<amp-img src="https://placehold.it/20x20" width="20" height="20" style="display:block" layout="responsive" class="amp-wp-enforced-sizes"></amp-img>',
				array(
					'add_noscript_fallback' => false,
				),
			),

			'img_with_fixed_height_inline_style'       => array(
				'<img src="https://placehold.it/20x200" height="20" width="auto" data-amp-layout="fixed-height" style="display: inline-block">',
				'<amp-img src="https://placehold.it/20x200" height="20" width="auto" layout="fixed-height" style="display:block" class="amp-wp-enforced-sizes"></amp-img>',
				array(
					'add_noscript_fallback' => false,
				),
			),

			'img_with_flex_item_inline_style'          => array(
				'<img src="https://placehold.it/20x200" data-amp-layout="flex-item" style="display: inline-block">',
				'<amp-img src="https://placehold.it/20x200" layout="flex-item" style="display:block" class="amp-wp-enforced-sizes"></amp-img>',
				array(
					'add_noscript_fallback' => false,
				),
			),

			'img_with_nodisplay_layout_inline_style'   => array(
				'<img src="https://placehold.it/20x20" data-amp-layout="nodisplay" style="display: inline">',
				'<amp-img src="https://placehold.it/20x20" layout="nodisplay" style="display:none" class="amp-wp-enforced-sizes"></amp-img>',
				array(
					'add_noscript_fallback' => false,
				),
			),

			'img_static_emoji'                         => array(
				'<img src="https://s.w.org/images/core/emoji/12.0.0-1/72x72/1f468-1f3fb-200d-1f4bb.png" alt="👨🏻‍💻" class="wp-smiley" style="height: 1em; max-height: 1em;">',
				'<amp-img src="https://s.w.org/images/core/emoji/12.0.0-1/72x72/1f468-1f3fb-200d-1f4bb.png" alt="👨🏻‍💻" class="wp-smiley amp-wp-enforced-sizes" style="height: 1em; max-height: 1em;" width="72" height="72" noloading="" layout="intrinsic"></amp-img>',
				array(
					'add_noscript_fallback' => false,
				),
			),
		);
	}

	/**
	 * Test converter.
	 *
	 * @param string   $source               Source.
	 * @param string   $expected             Expected.
	 * @param array    $args                 Args.
	 * @param string[] $expected_error_codes Expected error codes.
	 * @dataProvider get_data
	 */
	public function test_converter( $source, $expected = null, $args = array(), $expected_error_codes = array() ) {
		if ( ! $expected ) {
			$expected = $source;
		}

		$error_codes = array();

		$args = array_merge(
			array(
				'use_document_element'      => true,
				'validation_error_callback' => function( $error ) use ( &$error_codes ) {
					$error_codes[] = $error['code'];
				},
			),
			$args
		);

		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$img_count = $dom->getElementsByTagName( 'img' )->length;

		$sanitizer = new AMP_Img_Sanitizer( $dom, $args );
		$sanitizer->sanitize();

		$sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom, $args );
		$sanitizer->sanitize();

		$this->assertEqualSets( $error_codes, $expected_error_codes );

		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$this->assertEquals( $expected, $content );

		$xpath = new DOMXPath( $dom );
		$this->assertEquals( $img_count ? 1 : 0, $xpath->query( '/html/head/meta[ @name = "amp-experiments-opt-in" ][ @content = "amp-img-auto-sizes" ]' )->length );
	}

	/**
	 * Test that amp-anim does not get included for a PNG.
	 */
	public function test_no_gif_no_image_scripts() {
		$source   = '<img src="https://placehold.it/350x150.png" width="350" height="150" alt="Placeholder!" />';
		$expected = array();

		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Img_Sanitizer( $dom );
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
	 * Test that amp-anim does get included for a GIF.
	 */
	public function test_no_gif_image_scripts() {
		$source   = '<img src="https://placehold.it/350x150.gif" width="350" height="150" alt="Placeholder!" />';
		$expected = array( 'amp-anim' => true );

		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Img_Sanitizer( $dom );
		$sanitizer->sanitize();

		$whitelist_sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$whitelist_sanitizer->sanitize();

		$scripts = array_merge(
			$sanitizer->get_scripts(),
			$whitelist_sanitizer->get_scripts()
		);
		$this->assertEquals( $expected, $scripts );
	}
}
