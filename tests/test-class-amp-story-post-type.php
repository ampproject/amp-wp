<?php
/**
 * Test AMP_Story_Post_Type.
 *
 * @package AMP
 */

/**
 * Test AMP_Story_Post_Type.
 */
class AMP_Story_Post_Type_Test extends WP_UnitTestCase {

	/**
	 * Test get_embed_template.
	 *
	 * @covers AMP_Admin_Pointer::get_embed_template()
	 */
	public function test_get_embed_template() {
		$template          = '/srv/www/baz.php';
		$wrong_type        = 'post';
		$correct_type      = 'embed';
		$wrong_templates   = array( 'embed-testimonal.php', 'embed.php' );
		$correct_template  = sprintf( 'embed-%s.php', AMP_Story_Post_Type::POST_TYPE_SLUG );
		$expected_template = 'embed-amp-story.php';
		$correct_templates = array( $correct_template, 'embed.php' );

		$this->assertEquals( $template, AMP_Story_Post_Type::get_embed_template( $template, $wrong_type, $correct_templates ) );
		$this->assertEquals( $template, AMP_Story_Post_Type::get_embed_template( $template, $correct_type, $wrong_templates ) );
		$this->assertContains( $expected_template, AMP_Story_Post_Type::get_embed_template( $template, $correct_type, $correct_templates ) );
	}

}
