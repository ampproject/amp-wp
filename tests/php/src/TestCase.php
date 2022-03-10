<?php

namespace AmpProject\AmpWP\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase as PolyfilledTestCase;

/**
 * Class TestCase.
 *
 * @package AmpProject\AmpWP\Tests
 */
abstract class TestCase extends PolyfilledTestCase {

	/**
	 * Assert that one associative array contains another.
	 *
	 * @param array $expected_subset Expected subset associative array.
	 * @param array $actual_superset Actual superset associative array.
	 */
	public function assertAssocArrayContains( $expected_subset, $actual_superset ) {
		$this->assertArrayNotHasKey( 0, $expected_subset, 'Expected $expected_subset to be associative array.' );
		$this->assertArrayNotHasKey( 0, $actual_superset, 'Expected $actual_superset to be associative array.' );

		foreach ( $expected_subset as $expected_key => $expected_value ) {
			$this->assertArrayHasKey( $expected_key, $actual_superset );
			$this->assertEquals( $expected_value, $actual_superset[ $expected_key ] );
		}
	}

	/**
	 * Assert that one indexed array contains another.
	 *
	 * @param array $expected_subset Expected subset indexed array.
	 * @param array $actual_superset Actual superset indexed array.
	 */
	public function assertIndexedArrayContains( $expected_subset, $actual_superset ) {
		$this->assertArrayHasKey( 0, $expected_subset, 'Expected $expected_subset to be indexed array.' );
		$this->assertArrayHasKey( 0, $actual_superset, 'Expected $actual_superset to be indexed array.' );

		foreach ( $expected_subset as $expected_value ) {
			$this->assertContains( $expected_value, $actual_superset );
		}
	}
}
