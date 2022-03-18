<?php
/**
 * Analyzes a URL and return analysis results.
 *
 * @package AMP
 * @since 2.3
 */

namespace AmpProject\AmpWP\PageExperience;

use PageExperience\Engine\Analysis;

/**
 * Analyzer interface.
 *
 * @since 2.3
 * @internal
 */
interface Analyzer {

	/**
	 * Analyze a specific URL.
	 *
	 * @param string $url URL to analyze.
	 * @return Analysis Analysis results.
	 */
	public function analyze( $url );
}
