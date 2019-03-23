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

		$registry = WP_Block_Type_Registry::get_instance();

		$props = array( 'displayAsDropdown' => true );

		$categories_block = $registry->get_registered( 'core/categories' );
		$archives_block   = $registry->get_registered( 'core/archives' );

		$handler->register_embed();
		$rendered = $categories_block->render( $props );
		$this->assertContains( '<select', $rendered );
		$this->assertNotContains( 'onchange', $rendered );
		$this->assertContains( 'on="change', $rendered );
		if ( $archives_block ) {
			$rendered = $archives_block->render( $props );
			$this->assertContains( '<select', $rendered );
			$this->assertNotContains( 'onchange', $rendered );
			$this->assertContains( 'on="change', $rendered );
		}

		$handler->unregister_embed();
		$rendered = $categories_block->render( $props );
		$this->assertContains( '<select', $rendered );
		$this->assertContains( 'onchange', $rendered );
		$this->assertNotContains( 'on="change', $rendered );
		if ( $archives_block ) {
			$rendered = $archives_block->render( $props );
			$this->assertContains( '<select', $rendered );
			$this->assertContains( 'onchange', $rendered );
			$this->assertNotContains( 'on="change', $rendered );
		}
	}
}
