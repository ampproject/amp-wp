<?php

namespace AmpProject\AmpWP\Tests\Validation;

use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;
use AmpProject\AmpWP\Validation\URLScanningContext;
use WP_UnitTestCase;

/** @coversDefaultClass \AmpProject\AmpWP\Validation\URLScanningContext */
final class URLScanningContextTest extends WP_UnitTestCase {
	use PrivateAccess;

	/**
	 * Test instance
	 *
	 * @var URLScanningContext
	 */
	private $test_instance;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();
		$this->test_instance = new URLScanningContext();
	}

	/**
	 * @covers ::__construct()
	 */
	public function test__construct() {
		$this->assertInstanceof( URLScanningContext::class, $this->test_instance );
	}

	/** @covers ::get_limit_per_type() */
	public function test_get_limit_per_type() {
		$this->assertEquals( 1, $this->test_instance->get_limit_per_type() );

		add_filter(
			'amp_url_validation_limit_per_type',
			static function() {
				return -4;
			}
		);
		$this->assertEquals( 1, $this->test_instance->get_limit_per_type() );

		add_filter(
			'amp_url_validation_limit_per_type',
			static function() {
				return -1;
			}
		);
		$this->assertEquals( -1, $this->test_instance->get_limit_per_type() );

		add_filter(
			'amp_url_validation_limit_per_type',
			static function() {
				return 0;
			}
		);
		$this->assertEquals( 0, $this->test_instance->get_limit_per_type() );
	}

	/**
	 * @covers ::get_limit_per_type()
	 * @covers ::get_include_conditionals()
	 * @covers ::get_include_unsupported()
	 */
	public function test_getters() {
		$this->test_instance = new URLScanningContext( 99, [ 'is_date', 'is_search' ], true );

		$this->assertEquals( 99, $this->test_instance->get_limit_per_type() );
		$this->assertEquals(
			[ 'is_date', 'is_search' ],
			$this->test_instance->get_include_conditionals()
		);
		$this->assertTrue( $this->test_instance->get_include_unsupported() );
	}
}
