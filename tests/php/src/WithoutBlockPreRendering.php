<?php
/**
 * Trait WithoutBlockPreRendering.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Tests;

/**
 * Helper trait to remove block pre-rendering for tests.
 *
 * @package AmpProject\AmpWP
 */
trait WithoutBlockPreRendering {

	/**
	 * Set up.
	 */
	public function setUp() {
		if (
			defined( 'GUTENBERG_VERSION' ) &&
			version_compare( '8.1.0', GUTENBERG_VERSION, '<=' )
		) {
			remove_filter( 'pre_render_block', 'gutenberg_render_block_with_assigned_block_context', 9 );
		}
	}
}
