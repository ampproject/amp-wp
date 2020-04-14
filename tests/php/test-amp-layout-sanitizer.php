<?php
/**
 * Class AMP_Layout_Sanitizer_Test
 *
 * @package AMP
 */

use AmpProject\AmpWP\Tests\MarkupComparison;

/**
 * Test AMP_Layout_Sanitizer_Test
 *
 * @covers AMP_Layout_Sanitizer_Test
 */
class AMP_Layout_Sanitizer_Test extends WP_UnitTestCase {

	use MarkupComparison;

	/**
	 * Get body data.
	 *
	 * @return array Content.
	 */
	public function get_body_data() {
		return [
			'non_amp_component'                           => [
				'<svg height="10%" width="10%"></svg>',
				'<svg height="10%" width="10%"></svg>',
			],

			'no_width_or_height'                          => [
				'<amp-img src="foo.jpg" data-amp-layout="fill"></amp-img>',
				'<amp-img src="foo.jpg" layout="fill"></amp-img>',
			],

			'no_layout_attr'                              => [
				'<amp-img src="foo.jpg" width="10"></amp-img>',
			],

			'no_data_layout_attr'                         => [
				'<amp-img src="foo.jpg" width="10"></amp-img>',
			],

			'data_layout_attr'                            => [
				'<amp-img src="foo.jpg" width="10" data-amp-layout="fill"></amp-img>',
				'<amp-img src="foo.jpg" width="10" layout="fill"></amp-img>',
			],

			'data_layout_attr_with_100%_width'            => [
				'<amp-img src="foo.jpg" width="100%" height="10" data-amp-layout="fill"></amp-img>',
				'<amp-img src="foo.jpg" width="auto" height="10" layout="fixed-height"></amp-img>',
			],

			'data_layout_attr_with_100%_width_and_height' => [
				'<amp-img src="foo.jpg" width="100%" height="100%" data-amp-layout="fill"></amp-img>',
				'<amp-img src="foo.jpg" layout="fill"></amp-img>',
			],

			'100%_width_with_layout_attr'                 => [
				'<amp-img src="foo.jpg" width="100%" height="10" layout="fill"></amp-img>',
			],

			'100%_width_and_height_with_layout_attr'      => [
				'<amp-img src="foo.jpg" width="100%" height="100%" layout="fill"></amp-img>',
			],

			'100%_width_and_height_style_descriptors'     => [
				'<amp-img src="foo.jpg" style="width:100%; height:100%"></amp-img>',
				'<amp-img src="foo.jpg" layout="fill"></amp-img>',
			],

			'fill_layout_with_unrelated_style_descriptor' => [
				'<amp-img src="foo.jpg" style="width:100%; height:100%; color:#000"></amp-img>',
				'<amp-img src="foo.jpg" style="color:#000" layout="fill"></amp-img>',
			],

			'fill_height_attribute_and_width_style'       => [
				'<amp-img src="foo.jpg" style="width:100%;" height="100%"></amp-img>',
				'<amp-img src="foo.jpg" layout="fill"></amp-img>',
			],

			'fill_width_attribute_and_height_style'       => [
				'<amp-img src="foo.jpg" style="height:100%;" width="100%"></amp-img>',
				'<amp-img src="foo.jpg" layout="fill"></amp-img>',
			],

			'inside_amp_mustache_template_simple_text'    => [
				'<template type="amp-mustache"><span>Hello, {{first_name}}!</span></template>',
			],

			'inside_amp_mustache_template_tag'            => [
				'<template type="amp-mustache"><amp-img src="{{url}}" layout="intrinsic" srcset="{{foo}}" width="{{example_width}}" height="{{example_height}}" alt="This is for {{title}}"></amp-img></template>',
			],

			'inside_amp_mustache_script_tag'              => [
				'<script template="amp-mustache" type="text/plain"><amp-img src="{{url}}" layout="intrinsic" srcset="{{foo}}" width="{{example_width}}" height="{{example_height}}" alt="This is for {{title}}"></amp-img></script>',
			],

			'inside_amp_mustache_template_responsive'     => [
				'<template type="amp-mustache"><div><div><div><amp-img src="{{url}}" layout="responsive" srcset="{{foo}}" width="{{example_width}}" height="{{example_height}}" alt="This is for {{title}}"></amp-img></div></div></div></template>',
			],

			'inside_amp_mustache_template_sizes'          => [
				'<template type="amp-mustache"><div><div><div><amp-img src="https://example.com/image.jpg" sizes="{{sizes}}" layout="responsive" width="200" height="400" alt="Lake vacation"></amp-img></div></div></div></template>',
			],

			'inside_amp_mustache_template_fill'           => [
				'<template type="amp-mustache"><div><div><amp-img width="100%" height="10" data-amp-layout="fill" src="{{src}}"></amp-img></div></div></template>',
				'<template type="amp-mustache"><div><div><amp-img width="auto" height="10" src="{{src}}" layout="fixed-height"></amp-img></div></div></template>',
			],
		];
	}

	/**
	 * @param string      $source  Content.
	 * @param string|null $expected Expected content.
	 * @dataProvider get_body_data
	 * @covers AMP_Layout_Sanitizer::sanitize()
	 */
	public function test_sanitize( $source, $expected = null ) {
		$expected  = isset( $expected ) ? $expected : $source;
		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Layout_Sanitizer( $dom );

		$sanitizer->sanitize();
		$content = AMP_DOM_Utils::get_content_from_dom( $dom );

		$this->assertEqualMarkup( $expected, $content );
	}
}
