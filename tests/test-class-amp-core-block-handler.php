<?php
/**
 * Tests for AMP_Core_Block_Handler.
 *
 * @package AMP
 * @since 1.0
 */

/**
 * Tests for AMP_Core_Block_Handler.
 *
 * @package AMP
 * @covers AMP_Core_Block_Handler
 */
class Test_AMP_Core_Block_Handler extends WP_UnitTestCase {

	/**
	 * Test register_embed().
	 *
	 * @covers AMP_Core_Block_Handler::register_embed()
	 * @covers AMP_Core_Block_Handler::unregister_embed()
	 */
	public function test_register_and_unregister_embed() {

		if ( ! function_exists( 'register_block_type' ) ) {
			$this->markTestIncomplete( 'Files needed for testing missing.' );
		}

		$handler = new AMP_Core_Block_Handler();
		$handler->unregister_embed(); // Make sure we are on the initial clean state.

		$categories_block = '<!-- wp:categories {"displayAsDropdown":true,"showHierarchy":true,"showPostCounts":true} /-->';
		$archives_block   = '<!-- wp:archives {"displayAsDropdown":true,"showPostCounts":true} /-->';

		$handler->register_embed();
		$rendered = do_blocks( $categories_block );
		$this->assertContains( '<select', $rendered );
		$this->assertNotContains( 'onchange', $rendered );
		$this->assertContains( 'on="change', $rendered );
		if ( WP_Block_Type_Registry::get_instance()->is_registered( 'core/archives' ) ) {
			$rendered = do_blocks( $archives_block );
			$this->assertContains( '<select', $rendered );
			$this->assertNotContains( 'onchange', $rendered );
			$this->assertContains( 'on="change', $rendered );
		}

		$handler->unregister_embed();
		$rendered = do_blocks( $categories_block );
		$this->assertContains( '<select', $rendered );
		$this->assertContains( 'onchange', $rendered );
		$this->assertNotContains( 'on="change', $rendered );
		if ( WP_Block_Type_Registry::get_instance()->is_registered( 'core/archives' ) ) {
			$rendered = do_blocks( $archives_block );
			$this->assertContains( '<select', $rendered );
			$this->assertContains( 'onchange', $rendered );
			$this->assertNotContains( 'on="change', $rendered );
		}
	}
}
