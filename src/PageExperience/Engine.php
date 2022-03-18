<?php
/**
 * Entry-point to the Page Experience Engine (PXE) integration.
 *
 * @package AMP
 * @since 2.3
 */

namespace AmpProject\AmpWP\PageExperience;

use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\RemoteGetRequest;
use PageExperience\Engine as PxEngine;
use PageExperience\Engine\Analysis;
use PageExperience\Engine\ConfigurationProfile;

/**
 * PageExperienceEngine class.
 *
 * @since 2.3
 * @internal
 */
final class Engine implements Service, Analyzer, Optimizer {

	/**
	 * PXE instance to use.
	 *
	 * @var PxEngine
	 */
	private $engine;

	/**
	 * Instantiate a new PageExperienceEngine instance.
	 *
	 * @param RemoteGetRequest $remote_get_request Remote GET request handler to use.
	 */
	public function __construct( RemoteGetRequest $remote_get_request ) {
		$this->engine = new PxEngine( $remote_get_request );
	}

	/**
	 * Analyze a specific URL.
	 *
	 * @param string $url URL to analyze.
	 * @return Analysis Analysis results.
	 */
	public function analyze( $url ) {
		$profile = new ConfigurationProfile();
		return $this->engine->analyze( $url, $profile );
	}

	/**
	 * Optimize a string of HTML.
	 *
	 * @param string $html HTML to optimize.
	 * @return string Optimized HTML
	 */
	public function optimize( $html ) {
		$profile = new ConfigurationProfile();
		return $this->engine->optimizeHtml( $html, $profile );
	}
}
