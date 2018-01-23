<?php
/**
 * Tests for class AMP_Widget_Recent_Comments.
 *
 * @package AMP
 */

/**
 * Tests for class AMP_Widget_Recent_Comments.
 *
 * @package AMP
 */
class Test_AMP_Widget_Recent_Comments extends WP_UnitTestCase {

	/**
	 * Test construct().
	 *
	 * @see AMP_Widget_Recent_Comments::__construct().
	 */
	public function test_construct() {
		$widget = new AMP_Widget_Recent_Comments();
		$this->assertEquals( 'recent-comments', $widget->id_base );
		$this->assertEquals( 'Recent Comments', $widget->name );
		$this->assertEquals( 'widget_recent_comments', $widget->widget_options['classname'] );
		$this->assertEquals( true, $widget->widget_options['customize_selective_refresh'] );
		$this->assertEquals( 'Your site&#8217;s most recent comments.', $widget->widget_options['description'] );
		$this->assertFalse( apply_filters( 'show_recent_comments_widget_style', true ) );
	}
}
