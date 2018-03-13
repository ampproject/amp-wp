<?php
/**
 * Tests for AMP_WP_Utils_Parse_Url_Test.
 *
 * @package AMP
 * @since 0.7
 */

/**
 * Tests for AMP_WP_Utils_Parse_Url_Test.
 *
 * @covers AMP_WP_Utils_Parse_Url_Test
 */
class AMP_WP_Utils_Parse_Url_Test extends WP_UnitTestCase {
	/**
	 * Data for testing URL parsing.
	 *
	 * @return array
	 */
	public function get_test_data() {
		return array(
			'valid__no_component'               => array(
				'https://example.com/path',
				array(
					'scheme' => 'https',
					'host'   => 'example.com',
					'path'   => '/path',
				),
				-1,
			),

			'valid__with_component'             => array(
				'https://example.com/path',
				'example.com',
				PHP_URL_HOST,
			),

			'valid__schemaless__no_component'   => array(
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
	 * Test URL parsing.
	 *
	 * @param string $url       The URL.
	 * @param string $expected  Expected value.
	 * @param string $component Derived value.
	 *
	 * @dataProvider get_test_data
	 */
	public function test__method( $url, $expected, $component ) {
		$actual = AMP_WP_Utils::parse_url( $url, $component );

		$this->assertEquals( $expected, $actual );
	}
}
