<?php

namespace AmpProject\AmpWP\Tests;

use Yoast\WPTestUtils\WPIntegration\TestCase as PolyfilledTestCase;

/**
 * Class TestCase.
 *
 * @package AmpProject\AmpWP\Tests
 */
abstract class TestCase extends PolyfilledTestCase {

	/** @var array */
	private $original_wp_theme_features;

	/**
	 * Setup.
	 *
	 * @inheritDoc
	 */
	public function set_up() {
		parent::set_up();

		global $_wp_theme_features, $wp_query, $wp_the_query;
		$this->original_wp_theme_features = $_wp_theme_features;

		// This was needed with the upgrade of yoast/wp-test-utils from 0.2.2 to 1.0.0.
		$wp_the_query = $wp_query;
	}

	/**
	 * Tear down.
	 *
	 * @inheritDoc
	 */
	public function tear_down() {
		parent::tear_down();

		global $_wp_theme_features;
		$_wp_theme_features = $this->original_wp_theme_features;
	}

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
