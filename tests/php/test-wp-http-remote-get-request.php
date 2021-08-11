<?php
/**
 * Class Test_WP_Http_Remote_Get_Request.
 *
 * @package AmpProject\AmpWP
 */

use AmpProject\AmpWP\RemoteRequest\WpHttpRemoteGetRequest;

/**
 * Tests for the WpHttpRemoteGetRequest class.
 *
 * @coversDefaultClass \AmpProject\AmpWP\RemoteRequest\WpHttpRemoteGetRequest
 */
class Test_WP_Http_Remote_Get_Request extends \WP_UnitTestCase {

	/**
	 * Provide the data to test the processing of headers.
	 *
	 * @return array
	 */
	public function headers_test_data() {
		return [
			'accepts arrays'       => [ [ 'something' => 'to test' ], 'something', 'to test' ],

			'accepts traversables' => [
				new Requests_Utility_CaseInsensitiveDictionary( [ 'something' => 'to test' ] ),
				'something',
				'to test',
			],
		];
	}

	/**
	 * Test whether the headers are correctly parsed.
	 *
	 * @dataProvider headers_test_data()
	 *
	 * @covers ::get()
	 *
	 * @param mixed  $headers Headers that are provided to the response.
	 * @param string $header_to_check Name of the header entry to check.
	 * @param mixed  $expected Expected header entry value.
	 */
	public function test_processing_headers( $headers, $header_to_check, $expected ) {
		$href = 'https://example.com/some_file.txt';

		add_filter(
			'pre_http_request',
			function( $preempt, $request, $url ) use ( $href, $headers ) {
				$this->assertMatchesRegularExpression( '#^https?://#', $url );
				if ( set_url_scheme( $url, 'https' ) === set_url_scheme( $href, 'https' ) ) {
					$preempt = [
						'response' => [
							'code' => 200,
						],
						'headers'  => $headers,
						'body'     => '',
					];
				}
				return $preempt;
			},
			10,
			3
		);

		$remote_request = new WpHttpRemoteGetRequest();
		$response       = $remote_request->get( 'https://example.com/some_file.txt' );
		$this->assertEquals( $expected, $response->getHeader( $header_to_check )[0] );
	}
}
