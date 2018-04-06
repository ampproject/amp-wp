<?php
/**
 * Tests for AMP_Response_Headers.
 *
 * @package AMP
 * @since 1.0
 */

/**
 * Tests for AMP_Response_Headers.
 *
 * @covers AMP_Response_Headers
 */
class Test_AMP_Response_Headers extends WP_UnitTestCase {

	/**
	 * After a test method runs, reset any state in WordPress the test method might have changed.
	 *
	 * @global WP_Scripts $wp_scripts
	 */
	public function tearDown() {
		parent::tearDown();
		AMP_Response_Headers::$headers_sent = array();
	}

	/**
	 * Test \AMP_Response_Headers::send_header() when no args are passed.
	 *
	 * @covers \AMP_Response_Headers::send_header()
	 */
	public function test_send_header_no_args() {
		AMP_Response_Headers::send_header( 'Foo', 'Bar' );
		$this->assertContains(
			array(
				'name'        => 'Foo',
				'value'       => 'Bar',
				'replace'     => true,
				'status_code' => null,
			),
			AMP_Response_Headers::$headers_sent
		);
	}

	/**
	 * Test \AMP_Response_Headers::send_header() when replace arg is passed.
	 *
	 * @covers \AMP_Response_Headers::send_header()
	 */
	public function test_send_header_replace_arg() {
		AMP_Response_Headers::send_header( 'Foo', 'Bar', array(
			'replace' => false,
		) );
		$this->assertContains(
			array(
				'name'        => 'Foo',
				'value'       => 'Bar',
				'replace'     => false,
				'status_code' => null,
			),
			AMP_Response_Headers::$headers_sent
		);
	}

	/**
	 * Test \AMP_Response_Headers::send_header() when status code is passed.
	 *
	 * @covers \AMP_Response_Headers::send_header()
	 */
	public function test_send_header_status_code() {
		AMP_Response_Headers::send_header( 'Foo', 'Bar', array(
			'status_code' => 400,
		) );
		$this->assertContains(
			array(
				'name'        => 'Foo',
				'value'       => 'Bar',
				'replace'     => true,
				'status_code' => 400,
			),
			AMP_Response_Headers::$headers_sent
		);
	}

	/**
	 * Test \AMP_Response_Headers::send_server_timing() when positive duration passed.
	 *
	 * @covers \AMP_Response_Headers::send_server_timing()
	 */
	public function test_send_server_timing_positive_duration() {
		AMP_Response_Headers::send_server_timing( 'name', 123, 'Description' );
		$this->assertCount( 1, AMP_Response_Headers::$headers_sent );
		$this->assertEquals( 'Server-Timing', AMP_Response_Headers::$headers_sent[0]['name'] );
		$values = preg_split( '/\s*;\s*/', AMP_Response_Headers::$headers_sent[0]['value'] );
		$this->assertEquals( 'name', $values[0] );
		$this->assertEquals( 'desc="Description"', $values[1] );
		$this->assertStringStartsWith( 'dur=123000.', $values[2] );
		$this->assertFalse( AMP_Response_Headers::$headers_sent[0]['replace'] );
		$this->assertNull( AMP_Response_Headers::$headers_sent[0]['status_code'] );
	}

	/**
	 * Test \AMP_Response_Headers::send_server_timing() when positive duration passed.
	 *
	 * @covers \AMP_Response_Headers::send_server_timing()
	 */
	public function test_send_server_timing_negative_duration() {
		AMP_Response_Headers::send_server_timing( 'name', -microtime( true ) );
		$this->assertCount( 1, AMP_Response_Headers::$headers_sent );
		$this->assertEquals( 'Server-Timing', AMP_Response_Headers::$headers_sent[0]['name'] );
		$values = preg_split( '/\s*;\s*/', AMP_Response_Headers::$headers_sent[0]['value'] );
		$this->assertEquals( 'name', $values[0] );
		$this->assertStringStartsWith( 'dur=0.', $values[1] );
		$this->assertFalse( AMP_Response_Headers::$headers_sent[0]['replace'] );
		$this->assertNull( AMP_Response_Headers::$headers_sent[0]['status_code'] );
	}
}
