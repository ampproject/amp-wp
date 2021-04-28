<?php

namespace AmpProject\AmpWP\Tests\Optimizer;

use AmpProject\AmpWP\AmpWpPlugin;
use AmpProject\AmpWP\Optimizer\OptimizerService;
use AmpProject\AmpWP\RemoteRequest\CachedRemoteGetRequest;
use AmpProject\AmpWP\Tests\Helpers\AssertContainsCompatibility;
use AmpProject\Dom\Document;
use AmpProject\Optimizer\ErrorCollection;
use AmpProject\Optimizer\TransformationEngine;
use \AmpProject\AmpWP\Tests\Helpers\PrivateAccess;
use AmpProject\RemoteGetRequest;
use AmpProject\RemoteRequest\FallbackRemoteGetRequest;
use PHPUnit\Framework\TestCase;

/** @coversDefaultClass \AmpProject\AmpWP\Optimizer\OptimizerService */
final class OptimizerServiceTest extends TestCase {

	use AssertContainsCompatibility;
	use PrivateAccess;

	const HERO_IMAGE_MARKUP = '<amp-img data-hero src="https://example.com/image.jpg" width="500" height="500"></amp-img>';

	/**
	 * @covers \AmpProject\AmpWP\Optimizer\OptimizerService::optimizeDom()
	 * @covers \AmpProject\AmpWP\Optimizer\OptimizerService::optimizeHtml()
	 */
	public function test_method_forwarding() {
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

	/**
	 * @covers \AmpProject\AmpWP\Optimizer\OptimizerService::__construct()
	 * @covers \AmpProject\AmpWP\AmpWpPlugin::get_delegations()
	 */
	public function test_caching_and_fallback_are_enabled() {
		$plugin = new AmpWpPlugin();
		$plugin->register_services();

		$injector  = $plugin->get_container()->get( 'injector' );
		$optimizer = $injector->make( OptimizerService::class );

		$transformation_engine = $this->get_private_property( $optimizer, 'transformation_engine' );
		$this->assertInstanceOf( TransformationEngine::class, $transformation_engine );

		$remote_request = $this->get_private_property( $transformation_engine, 'remoteRequest' );
		$this->assertInstanceOf( RemoteGetRequest::class, $remote_request );
		$this->assertInstanceOf( CachedRemoteGetRequest::class, $remote_request );

		$cached_remote_request = $this->get_private_property( $remote_request, 'remote_request' );
		$this->assertInstanceOf( RemoteGetRequest::class, $cached_remote_request );
		$this->assertInstanceOf( FallbackRemoteGetRequest::class, $cached_remote_request );
	}
}
