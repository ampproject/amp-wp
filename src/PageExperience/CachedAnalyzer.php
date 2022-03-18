<?php
/**
 * Uses cached retrieval of analyzer results.
 *
 * @package AMP
 * @since 2.3
 */

namespace AmpProject\AmpWP\PageExperience;

use PageExperience\Engine\Analysis;

/**
 * CachedAnalyzer implementation.
 *
 * This is a Decorator around the Analyzer interface.
 *
 * @since 2.3
 * @internal
 */
final class CachedAnalyzer implements Analyzer {

	/**
	 * Analyzer instance to decorate with caching.
	 *
	 * @var Analyzer
	 */
	private $analyzer;

	/**
	 * Expiry of the cache in seconds.
	 *
	 * @var int
	 */
	private $expiry;

	/**
	 * Instantiate an analyzer interface.
	 *
	 * @param Analyzer $analyzer Analyzer instance to decorate with caching.
	 * @param int      $expiry   Optional. Cache expiry in seconds. Defaults to a week.
	 */
	public function __construct( Analyzer $analyzer, $expiry = WEEK_IN_SECONDS ) {
		$this->analyzer = $analyzer;
		$this->expiry   = (int) $expiry;
	}

	/**
	 * Analyze a specific URL.
	 *
	 * @param string $url URL to analyze.
	 * @return Analysis Analysis results.
	 */
	public function analyze( $url ) {
		// @TODO: Add caching.
		return $this->analyzer->analyze( $url );
	}
}
