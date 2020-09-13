<?php
/**
 * Tests for LikelyCulpritDetector class.
 *
 * @package AMP
 */

namespace AmpProject\AmpWP\Tests\DevTools;

use AmpProject\AmpWP\DevTools\LikelyCulpritDetector;
use WP_UnitTestCase;

/**
 * Tests for LikelyCulpritDetector class.
 *
 * @since 2.0.2
 *
 * @coversDefaultClass \AmpProject\AmpWP\DevTools\LikelyCulpritDetector
 */
class LikelyCulpritDetectorTest extends WP_UnitTestCase {

	/**
	 * Test instance.
	 *
	 * @var LikelyCulpritDetector
	 */
	private $likely_culprit_detector;

	public function setUp() {
		parent::setUp();

		$this->likely_culprit_detector = Services::get( 'injector' )->make( LikelyCulpritDetector::class );
	}

	/**
	 * Tests LikelyCulpritDetector::analyze_backtrace
	 *
	 * @covers ::analyze_backtrace
	 */
	public function test_analyze_backtrace() {
		$this->markTestIncomplete();
	}

	/**
	 * Tests LikelyCulpritDetector::analyze_exception
	 *
	 * @covers ::analyze_exception
	 */
	public function test_analyze_exception() {
		$this->markTestIncomplete();
	}

	/**
	 * Tests LikelyCulpritDetector::analyze_trace
	 *
	 * @covers ::analyze_trace
	 */
	public function test_analyze_trace() {
		$this->markTestIncomplete();
	}
}
