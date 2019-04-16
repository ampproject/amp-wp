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
		if ( version_compare( get_bloginfo( 'version' ), '5.0', '<' ) ) {
			$this->markTestSkipped( 'Missing required render_block filter.' );
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

	/**
	 * Test that placeholder blocks don't result in validation errors.
	 *
	 * @covers \AMP_Core_Block_Handler::filter_rendered_block()
	 */
	public function test_placeholder_blocks() {
		if ( version_compare( get_bloginfo( 'version' ), '5.0', '<' ) ) {
			$this->markTestSkipped( 'Missing required render_block filter.' );
		}

		$handler = new AMP_Core_Block_Handler();
		$handler->unregister_embed(); // Make sure we are on the initial clean state.
		$handler->register_embed();

		$audio_placeholder_block = "<!-- wp:audio -->\n<figure class=\"wp-block-audio\"><audio controls></audio></figure>\n<!-- /wp:audio -->";
		$audio_populated_block   = "<!-- wp:audio -->\n<figure class=\"wp-block-audio\"><audio controls src=\"https://wordpressdev.lndo.site/content/uploads/2019/02/do-you-know-I-am-batman.mp3\"></audio></figure>\n<!-- /wp:audio -->";
		$this->assertEmpty( apply_filters( 'the_content', $audio_placeholder_block ) );
		$this->assertNotEmpty( apply_filters( 'the_content', $audio_populated_block ) );

		$image_placeholder_block = "<!-- wp:image -->\n<figure class=\"wp-block-image\"><img alt=\"\"/></figure>\n<!-- /wp:image -->";
		$image_populated_block   = "<!-- wp:image -->\n<figure class=\"wp-block-image\"><img src=\"https://wordpressdev.lndo.site/content/uploads/2019/02/1200px-American_bison_k5680-1-1024x668.jpg\" alt=\"\"/></figure>\n<!-- /wp:image -->";
		$this->assertEmpty( apply_filters( 'the_content', $image_placeholder_block ) );
		$this->assertNotEmpty( apply_filters( 'the_content', $image_populated_block ) );
	}
}
