<?php
/**
 * Optimizes a string of HTML and returns optimized HTML.
 *
 * @package AMP
 * @since 2.3
 */

namespace AmpProject\AmpWP\PageExperience;

/**
 * Optimizer interface.
 *
 * @since 2.3
 * @internal
 */
interface Optimizer {

	/**
	 * Optimize a string of HTML.
	 *
	 * @param string $html HTML to optimize.
	 * @return string Optimized HTML
	 */
	public function optimize( $html );
}
