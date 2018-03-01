<?php

class AMP_WP_Utils__Parse_Url__Test extends WP_UnitTestCase {
	function get_test_data() {
		return array(
			'valid__no_component' => array(
				'https://example.com/path',
				array(
					'scheme' => 'https',
					'host' => 'example.com',
					'path' => '/path',
				),
				-1,
			),

			'valid__with_component' => array(
				'https://example.com/path',
				'example.com',
				PHP_URL_HOST,
			),

			'valid__schemaless__no_component' => array(
				'//example.com/path',
				array(
					'host' => 'example.com',
					'path' => '/path',
				),
				-1,
			),

			'valid__schemaless__with_component' => array(
				'//example.com/path',
				'example.com',
				PHP_URL_HOST,
			),
		);
	}

	/**
	 * @dataProvider get_test_data
	 */
	function test__method( $url, $expected, $component ) {
		$actual = AMP_WP_Utils::parse_url( $url, $component );

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Test AMP_WP_Utils::add_layout().
	 *
	 * @see AMP_WP_Utils::add_layout()
	 */
	public function test_add_layout() {
		$this->assertEquals( array(), AMP_WP_Utils::add_layout( array(), 'explicit' ) );
		$this->assertEquals(
			array(
				'img' => array(
					'data-amp-layout' => true,
				),
			),
			AMP_WP_Utils::add_layout( array(), 'post' )
		);

		add_filter( 'wp_kses_allowed_html', 'AMP_WP_Utils::add_layout', 10, 2 );
		$image = '<img data-amp-layout="fill">';
		$this->assertEquals( $image, wp_kses_post( $image ) );
	}

}
