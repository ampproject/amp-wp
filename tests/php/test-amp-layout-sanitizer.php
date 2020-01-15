<?php
/**
 * Class AMP_Layout_Sanitizer_Test
 *
 * @package AMP
 */

/**
 * Test AMP_Layout_Sanitizer_Test
 *
 * @covers AMP_Layout_Sanitizer_Test
 */
class AMP_Layout_Sanitizer_Test extends WP_UnitTestCase {

	/**
	 * Get body data.
	 *
	 * @return array Content.
	 */
	public function get_body_data() {
		return [
			'non_amp_component_with_percent_style_props'  => [
				'<svg height="100%" width="100%" style="width:20%;height:20%"></svg>',
				'<svg height="100%" width="100%" style="width:20%;height:20%"></svg>',
			],

			'amp_component_with_percent_style_props'      => [
				'<amp-img height="100%" width="10%" style="width:20%;height:20%"></amp-img>',
				'<amp-img height="100%" width="10%" style="width:20%;height:20%"></amp-img>',
			],

			'non_amp_component_no_percent_attrs'          => [
				'<svg height="100" width="100"></svg>',
				'<svg height="100" width="100"></svg>',
			],

			'non_amp_component_with_percent_attrs'        => [
				'<svg height="100%" width="100%"></svg>',
				'<svg style="width:100%;height:100%"></svg>',
			],

			'amp_component_with_percent_attrs'            => [
				'<amp-img height="10%" width="10%"></amp-img>',
				'<amp-img style="width:10%;height:10%"></amp-img>',
			],

			'amp_component_with_100%_percent_attrs'       => [
				'<amp-img height="100%" width="100%"></amp-img>',
				'<amp-img layout="fill"></amp-img>',
			],

			'amp_component_with_a_100%_percent_attr'      => [
				'<amp-img height="10%" width="10"></amp-img>',
				'<amp-img width="10" style="height:10%"></amp-img>',
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

	/**
	 * Assert markup is equal.
	 *
	 * @param string $expected Expected markup.
	 * @param string $actual   Actual markup.
	 */
	public function assertEqualMarkup( $expected, $actual ) {
		$actual   = preg_replace( '/\s+/', ' ', $actual );
		$expected = preg_replace( '/\s+/', ' ', $expected );
		$actual   = preg_replace( '/(?<=>)\s+(?=<)/', '', trim( $actual ) );
		$expected = preg_replace( '/(?<=>)\s+(?=<)/', '', trim( $expected ) );

		$this->assertEquals(
			array_filter( preg_split( '#(<[^>]+>|[^<>]+)#', $expected, -1, PREG_SPLIT_DELIM_CAPTURE ) ),
			array_filter( preg_split( '#(<[^>]+>|[^<>]+)#', $actual, -1, PREG_SPLIT_DELIM_CAPTURE ) )
		);
	}
}
