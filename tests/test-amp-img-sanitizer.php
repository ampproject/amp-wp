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
		add_filter( 'amp_extract_image_dimensions_batch', function( $urls ) {
			$dimensions = array();
			foreach ( array_keys( $urls ) as $url ) {
				if ( preg_match( '#/(?P<width>\d+)x(?P<height>\d+)$#', $url, $matches ) ) {
					$dimensions[ $url ] = array_map( 'intval', wp_array_slice_assoc( $matches, array( 'width', 'height' ) ) );
				} else {
					$dimensions[ $url ] = false;
				}
			}
			return $dimensions;
		} );
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

			'image_without_src'                        => array(
				'<p><img width="300" height="300" /></p>',
				'<p></p>',
			),

			'image_with_empty_src'                     => array(
				'<p><img src="" width="300" height="300" /></p>',
				'<p></p>',
			),

			'image_with_layout'                        => array(
				'<img src="http://placehold.it/100x100" data-amp-layout="fill" width="100" height="100" />',
				'<amp-img src="http://placehold.it/100x100" layout="fill" width="100" height="100" class="amp-wp-enforced-sizes"></amp-img>',
			),

			'image_with_layout_from_editor'            => array(
				'<figure data-amp-layout="fill"><img src="http://placehold.it/300x300" height="300" width="300" /></figure>',
				'<figure data-amp-layout="fill" style="position:relative; width: 100%; height: 300px;"><amp-img src="http://placehold.it/300x300" layout="fill" class="amp-wp-enforced-sizes"></amp-img></figure>',
			),

			'image_with_noloading_from_editor'         => array(
				'<figure data-amp-noloading="true"><img src="http://placehold.it/300x300" height="300" width="300" /></figure>',
				'<figure data-amp-noloading="true"><amp-img src="http://placehold.it/300x300" height="300" width="300" noloading="" class="amp-wp-enforced-sizes" layout="intrinsic"></amp-img></figure>',
			),

			'image_with_spaces_only_src'               => array(
				'<p><img src="    " width="300" height="300" /></p>',
				'<p></p>',
			),

			'image_with_empty_width_and_height'        => array(
				'<p><img src="http://placehold.it/200x300" width="" height="" /></p>',
				'<p><amp-img src="http://placehold.it/200x300" width="200" height="300" class="amp-wp-enforced-sizes" layout="intrinsic"></amp-img></p>',
			),

			'image_with_undefined_width_and_height'    => array(
				'<p><img src="http://placehold.it/200x300" /></p>',
				'<p><amp-img src="http://placehold.it/200x300" width="200" height="300" class="amp-wp-enforced-sizes" layout="intrinsic"></amp-img></p>',
			),

			'image_with_empty_width'                   => array(
				'<p><img src="http://placehold.it/500x1000" width="" height="300" /></p>',
				'<p><amp-img src="http://placehold.it/500x1000" width="150" height="300" class="amp-wp-enforced-sizes" layout="intrinsic"></amp-img></p>',
			),

			'image_with_empty_height'                  => array(
				'<p><img src="http://placehold.it/500x1000" width="300" height="" /></p>',
				'<p><amp-img src="http://placehold.it/500x1000" width="300" height="600" class="amp-wp-enforced-sizes" layout="intrinsic"></amp-img></p>',
			),

			'image_with_zero_width'                    => array(
				'<p><img src="http://placehold.it/300x300" width="0" height="300" /></p>',
				'<p><amp-img src="http://placehold.it/300x300" width="0" height="300" class="amp-wp-enforced-sizes"></amp-img></p>',
			),

			'image_with_zero_width_and_height'         => array(
				'<p><img src="http://placehold.it/300x300" width="0" height="0" /></p>',
				'<p><amp-img src="http://placehold.it/300x300" width="0" height="0" class="amp-wp-enforced-sizes"></amp-img></p>',
			),

			'image_with_decimal_width'                 => array(
				'<p><img src="http://placehold.it/300x300" width="299.5" height="300" /></p>',
				'<p><amp-img src="http://placehold.it/300x300" width="299.5" height="300" class="amp-wp-enforced-sizes" layout="intrinsic"></amp-img></p>',
			),

			'image_with_self_closing_tag'              => array(
				'<img src="http://placehold.it/350x150" width="350" height="150" alt="Placeholder!" />',
				'<amp-img src="http://placehold.it/350x150" width="350" height="150" alt="Placeholder!" class="amp-wp-enforced-sizes" layout="intrinsic"></amp-img>',
			),

			'image_with_no_end_tag'                    => array(
				'<img src="http://placehold.it/350x150" width="350" height="150" alt="Placeholder!">',
				'<amp-img src="http://placehold.it/350x150" width="350" height="150" alt="Placeholder!" class="amp-wp-enforced-sizes" layout="intrinsic"></amp-img>',
			),

			'image_with_end_tag'                       => array(
				'<img src="http://placehold.it/350x150" width="350" height="150" alt="Placeholder!"></img>',
				'<amp-img src="http://placehold.it/350x150" width="350" height="150" alt="Placeholder!" class="amp-wp-enforced-sizes" layout="intrinsic"></amp-img>',
			),

			'image_with_on_attribute'                  => array(
				'<img src="http://placehold.it/350x150" on="tap:my-lightbox" width="350" height="150" />',
				'<amp-img src="http://placehold.it/350x150" on="tap:my-lightbox" width="350" height="150" class="amp-wp-enforced-sizes" layout="intrinsic"></amp-img>',
			),

			'image_with_blacklisted_attribute'         => array(
				'<img src="http://placehold.it/350x150" width="350" height="150" style="border: 1px solid red;" />',
				'<amp-img src="http://placehold.it/350x150" width="350" height="150" class="amp-wp-enforced-sizes" layout="intrinsic"></amp-img>',
			),

			'image_with_no_dimensions_is_forced'       => array(
				'<img src="http://placehold.it/350x150" />',
				'<amp-img src="http://placehold.it/350x150" width="350" height="150" class="amp-wp-enforced-sizes" layout="intrinsic"></amp-img>',
			),

			'image_with_bad_src_url_get_fallback_dims' => array(
				'<img src="http://example.com/404.png" />',
				'<amp-img src="http://example.com/404.png" width="' . AMP_Img_Sanitizer::FALLBACK_WIDTH . '" height="' . AMP_Img_Sanitizer::FALLBACK_HEIGHT . '" class="amp-wp-unknown-size amp-wp-unknown-width amp-wp-unknown-height amp-wp-enforced-sizes" layout="intrinsic"></amp-img>',
			),

			'gif_image_conversion'                     => array(
				'<img src="http://placehold.it/350x150.gif" width="350" height="150" alt="Placeholder!" />',
				'<amp-anim src="http://placehold.it/350x150.gif" width="350" height="150" alt="Placeholder!" class="amp-wp-enforced-sizes" layout="intrinsic"></amp-anim>',
			),

			'gif_image_url_with_querystring'           => array(
				'<img src="http://placehold.it/350x150.gif?foo=bar" width="350" height="150" alt="Placeholder!" />',
				'<amp-anim src="http://placehold.it/350x150.gif?foo=bar" width="350" height="150" alt="Placeholder!" class="amp-wp-enforced-sizes" layout="intrinsic"></amp-anim>',
			),

			'multiple_same_image'                      => array(
				'<img src="http://placehold.it/350x150" width="350" height="150" />
<img src="http://placehold.it/350x150" width="350" height="150" />
<img src="http://placehold.it/350x150" width="350" height="150" />
<img src="http://placehold.it/350x150" width="350" height="150" />
				',
				'<amp-img src="http://placehold.it/350x150" width="350" height="150" class="amp-wp-enforced-sizes" layout="intrinsic"></amp-img><amp-img src="http://placehold.it/350x150" width="350" height="150" class="amp-wp-enforced-sizes" layout="intrinsic"></amp-img><amp-img src="http://placehold.it/350x150" width="350" height="150" class="amp-wp-enforced-sizes" layout="intrinsic"></amp-img><amp-img src="http://placehold.it/350x150" width="350" height="150" class="amp-wp-enforced-sizes" layout="intrinsic"></amp-img>',
			),

			'multiple_different_images'                => array(
				'<img src="http://placehold.it/350x150" width="350" height="150" />
<img src="http://placehold.it/360x160" width="360" height="160" />
<img src="http://placehold.it/370x170" width="370" height="170" />
<img src="http://placehold.it/380x180" width="380" height="180" />',
				'<amp-img src="http://placehold.it/350x150" width="350" height="150" class="amp-wp-enforced-sizes" layout="intrinsic"></amp-img><amp-img src="http://placehold.it/360x160" width="360" height="160" class="amp-wp-enforced-sizes" layout="intrinsic"></amp-img><amp-img src="http://placehold.it/370x170" width="370" height="170" class="amp-wp-enforced-sizes" layout="intrinsic"></amp-img><amp-img src="http://placehold.it/380x180" width="380" height="180" class="amp-wp-enforced-sizes" layout="intrinsic"></amp-img>',
			),

			'image_center_aligned'                     => array(
				'<img class="aligncenter" src="http://placehold.it/350x150" width="350" height="150" />',
				'<figure class="aligncenter" style="max-width: 350px;"><amp-img class="amp-wp-enforced-sizes" src="http://placehold.it/350x150" width="350" height="150" layout="intrinsic"></amp-img></figure>',
			),

			'image_left_aligned'                       => array(
				'<img class="alignleft" src="http://placehold.it/350x150" width="350" height="150" />',
				'<amp-img class="alignleft amp-wp-enforced-sizes" src="http://placehold.it/350x150" width="350" height="150" layout="intrinsic"></amp-img>',
			),

			'image_with_caption'                       => array(
				'<figure class="wp-caption aligncenter"><img src="http://placehold.it/350x150" alt="" width="350" height="150" class="size-medium wp-image-312"><figcaption class="wp-caption-text">This is an example caption.</figcaption></figure>',
				'<figure class="wp-caption aligncenter"><amp-img src="http://placehold.it/350x150" alt="" width="350" height="150" class="size-medium wp-image-312 amp-wp-enforced-sizes" layout="intrinsic"></amp-img><figcaption class="wp-caption-text">This is an example caption.</figcaption></figure>',
			),
		);
	}

	/**
	 * Test converter.
	 *
	 * @param string $source   Source.
	 * @param string $expected Expected.
	 * @dataProvider get_data
	 */
	public function test_converter( $source, $expected ) {
		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Img_Sanitizer( $dom );
		$sanitizer->sanitize();
		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$this->assertEquals( $expected, $content );
	}

	/**
	 * Test that amp-anim does not get included for a PNG.
	 */
	public function test_no_gif_no_image_scripts() {
		$source   = '<img src="http://placehold.it/350x150.png" width="350" height="150" alt="Placeholder!" />';
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
		$source   = '<img src="http://placehold.it/350x150.gif" width="350" height="150" alt="Placeholder!" />';
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

	/**
	 * Test handle_centering
	 *
	 * @covers AMP_Img_Sanitizer::handle_centering()
	 */
	public function test_handle_centering() {
		$dom                = new DOMDocument();
		$align_center_class = 'aligncenter';
		$align_left_class   = 'alignleft';
		$sanitizer          = new AMP_Img_Sanitizer( $dom );
		$width              = 300;

		$amp_img = AMP_DOM_Utils::create_node(
			$dom,
			'amp-img',
			array(
				'class' => $align_left_class,
				'width' => $width,
			)
		);

		// There's no aligncenter class, so the node shouldn't change.
		$this->assertEquals( $amp_img, $sanitizer->handle_centering( $amp_img ) );

		$amp_img = AMP_DOM_Utils::create_node(
			$dom,
			'amp-img',
			array(
				'class' => $align_left_class,
			)
		);

		// There's no width attribute, so the node shouldn't change.
		$this->assertEquals( $amp_img, $sanitizer->handle_centering( $amp_img ) );

		$centered_amp_img = AMP_DOM_Utils::create_node(
			$dom,
			'amp-img',
			array(
				'class' => $align_center_class,
				'width' => $width,
			)
		);
		$processed_tag    = $sanitizer->handle_centering( $centered_amp_img );
		$child            = $processed_tag->firstChild;

		$this->assertEquals( 'figure', $processed_tag->nodeName );
		$this->assertEquals( $align_center_class, $processed_tag->getAttribute( 'class' ) );
		$this->assertEquals( "max-width: {$width}px;", $processed_tag->getAttribute( 'style' ) );
		$this->assertEquals( 'amp-img', $child->nodeName );
		$this->assertFalse( strpos( $child->getAttribute( 'class' ), $align_center_class ) );
	}
}
