<?php

class AMP_Img_Sanitizer_Test extends WP_UnitTestCase {
	public function get_data() {
		return array(
			'no_images' => array(
				'<p>Lorem Ipsum Demet Delorit.</p>',
				'<p>Lorem Ipsum Demet Delorit.</p>',
			),

			'image_without_src' => array(
				'<p><img width="300" height="300" /></p>',
				'<p></p>'
			),

			'image_with_self_closing_tag' => array(
				'<img src="http://placehold.it/350x150" width="350" height="150" alt="Placeholder!" />',
				'<amp-img src="http://placehold.it/350x150" width="350" height="150" alt="Placeholder!" sizes="(min-width: 350px) 350px, 100vw" class="amp-wp-enforced-sizes"></amp-img>',
			),

			'image_with_no_end_tag' => array(
				'<img src="http://placehold.it/350x150" width="350" height="150" alt="Placeholder!">',
				'<amp-img src="http://placehold.it/350x150" width="350" height="150" alt="Placeholder!" sizes="(min-width: 350px) 350px, 100vw" class="amp-wp-enforced-sizes"></amp-img>',
			),

			'image_with_end_tag' => array(
				'<img src="http://placehold.it/350x150" width="350" height="150" alt="Placeholder!"></img>',
				'<amp-img src="http://placehold.it/350x150" width="350" height="150" alt="Placeholder!" sizes="(min-width: 350px) 350px, 100vw" class="amp-wp-enforced-sizes"></amp-img>',
			),

			'image_with_blacklisted_attribute' => array(
				'<img src="http://placehold.it/350x150" style="border: 1px solid red;" />',
				'<amp-img src="http://placehold.it/350x150"></amp-img>',
			),

			'image_with_sizes_attribute' => array(
				'<img src="http://placehold.it/350x150" width="350" height="150" sizes="(min-width: 100px) 300px, 90vw" />',
				'<amp-img src="http://placehold.it/350x150" width="350" height="150" sizes="(min-width: 100px) 300px, 90vw"></amp-img>',
			),

			'gif_image_conversion' => array(
				'<img src="http://placehold.it/350x150.gif" width="350" height="150" alt="Placeholder!" />',
				'<amp-anim src="http://placehold.it/350x150.gif" width="350" height="150" alt="Placeholder!" sizes="(min-width: 350px) 350px, 100vw" class="amp-wp-enforced-sizes"></amp-anim>',
			),

			'gif_image_url_with_querystring' => array(
				'<img src="http://placehold.it/350x150.gif?foo=bar" width="350" height="150" alt="Placeholder!" />',
				'<amp-anim src="http://placehold.it/350x150.gif?foo=bar" width="350" height="150" alt="Placeholder!" sizes="(min-width: 350px) 350px, 100vw" class="amp-wp-enforced-sizes"></amp-anim>',
			),

			'multiple_same_image' => array(
				'<img src="http://placehold.it/350x150" />
<img src="http://placehold.it/350x150" />
<img src="http://placehold.it/350x150" />
<img src="http://placehold.it/350x150" />
				',
				'<amp-img src="http://placehold.it/350x150"></amp-img><amp-img src="http://placehold.it/350x150"></amp-img><amp-img src="http://placehold.it/350x150"></amp-img><amp-img src="http://placehold.it/350x150"></amp-img>'
			),

			'multiple_different_images' => array(
				'<img src="http://placehold.it/350x150" />
<img src="http://placehold.it/360x160" />
<img src="http://placehold.it/370x170" />
<img src="http://placehold.it/380x180" />',
				'<amp-img src="http://placehold.it/350x150"></amp-img><amp-img src="http://placehold.it/360x160"></amp-img><amp-img src="http://placehold.it/370x170"></amp-img><amp-img src="http://placehold.it/380x180"></amp-img>'
			),
		);
	}

	/**
	 * @dataProvider get_data
	 */
	public function test_converter( $source, $expected ) {
		$dom = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Img_Sanitizer( $dom );
		$sanitizer->sanitize();
		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$this->assertEquals( $expected, $content );
	}

	public function test_no_gif_no_image_scripts() {
		$source = '<img src="http://placehold.it/350x150.png" width="350" height="150" alt="Placeholder!" />';
		$expected = array();

		$dom = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Img_Sanitizer( $dom );
		$sanitizer->sanitize();

		$scripts = $sanitizer->get_scripts();
		$this->assertEquals( $expected, $scripts );
	}

	public function test_no_gif_image_scripts() {
		$source = '<img src="http://placehold.it/350x150.gif" width="350" height="150" alt="Placeholder!" />';
		$expected = array( 'amp-anim' => 'https://cdn.ampproject.org/v0/amp-anim-0.1.js' );

		$dom = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Img_Sanitizer( $dom );
		$sanitizer->sanitize();

		$scripts = $sanitizer->get_scripts();
		$this->assertEquals( $expected, $scripts );
	}
}
