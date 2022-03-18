<?php

namespace AmpProject\AmpWP\Tests\PageExperience;

use AmpProject\AmpWP\PageExperience\Analyzer;
use AmpProject\AmpWP\PageExperience\CachedAnalyzer;
use AmpProject\AmpWP\Tests\TestCase;
use PageExperience\Engine\Analysis;

/** @coversDefaultClass \AmpProject\AmpWP\PageExperience\CachedAnalyzer */
class CachedAnalyzerTest extends TestCase {

	/** @covers ::__construct() */
	public function test_it_can_be_instantiated()
	{
		$mock_analyzer   = self::createMock( Analyzer::class );
		$cached_analyzer = new CachedAnalyzer( $mock_analyzer );
		self::assertInstanceOf( CachedAnalyzer::class, $cached_analyzer );
	}

	/** @covers ::analyze() */
	public function test_it_can_pass_through_an_analysis()
	{
		$mock_analysis = self::createMock( Analysis::class );
		$mock_analyzer = self::createMock( Analyzer::class );
		$mock_analyzer->method( 'analyze' )
		              ->willReturn( $mock_analysis );

		$cached_analyzer = new CachedAnalyzer( $mock_analyzer );

		$analysis = $cached_analyzer->analyze( 'https://example.com' );

		self::assertInstanceOf( Analysis::class, $analysis );
	}

	// @TODO: These tests have to be completed once actual caching was added.
}
