<?php

namespace AmpProject\AmpWP\Tests\Optimizer;

use AmpProject\AmpWP\Optimizer\OptimizerService;
use AmpProject\AmpWP\Tests\Helpers\AssertContainsCompatibility;
use AmpProject\Dom\Document;
use AmpProject\Optimizer\ErrorCollection;
use AmpProject\Optimizer\TransformationEngine;
use PHPUnit\Framework\TestCase;

/** @coversDefaultClass \AmpProject\AmpWP\Optimizer\OptimizerService */
final class OptimizerServiceTest extends TestCase {

	use AssertContainsCompatibility;

	const HERO_IMAGE_MARKUP = '<amp-img data-hero src="https://example.com/image.jpg" width="500" height="500"></amp-img>';

	/**
	 * @covers \AmpProject\AmpWP\Optimizer\OptimizerService::optimizeDom()
	 * @covers \AmpProject\AmpWP\Optimizer\OptimizerService::optimizeHtml()
	 */
	public function testMethodForwarding() {
		$html                  = self::HERO_IMAGE_MARKUP . self::HERO_IMAGE_MARKUP . self::HERO_IMAGE_MARKUP;
		$document              = Document::fromHtml( $html );
		$transformation_engine = new TransformationEngine();

		$optimizer_service = new OptimizerService( $transformation_engine );

		$dom_errors = new ErrorCollection();
		$optimizer_service->optimizeDom( $document, $dom_errors );
		$this->assertEquals( 'self;v=1', $document->html->getAttribute( 'transformed' ) );
		$this->assertTrue( $dom_errors->has( 'TooManyHeroImages' ) );

		$html_errors = new ErrorCollection();
		$output      = $optimizer_service->optimizeHtml( $html, $html_errors );
		$this->assertStringContains( 'transformed="self;v=1"', $output );
		$this->assertTrue( $html_errors->has( 'TooManyHeroImages' ) );
	}
}
