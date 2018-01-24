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
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();
		add_theme_support( 'amp' );
	}

	/**
	 * Test construct().
	 *
	 * @covers AMP_Widget_Recent_Comments::__construct()
	 */
	public function test_construct() {
		add_theme_support( 'amp' );
		$widget = new AMP_Widget_Recent_Comments();
		$this->assertEquals( 'recent-comments', $widget->id_base );
		$this->assertEquals( 'Recent Comments', $widget->name );
		$this->assertEquals( 'widget_recent_comments', $widget->widget_options['classname'] );
		$this->assertEquals( true, $widget->widget_options['customize_selective_refresh'] );
		$this->assertEquals( 'Your site&#8217;s most recent comments.', $widget->widget_options['description'] );
		$this->assertSame( 0, has_action( 'wp_head', array( $widget, 'remove_head_style_in_amp' ) ) );
	}

	/**
	 * Test remove_head_style_in_amp.
	 *
	 * @covers AMP_Widget_Recent_Comments::remove_head_style_in_amp()
	 */
	public function test_remove_head_style_in_amp() {
		new AMP_Widget_Recent_Comments();
		$this->assertTrue( apply_filters( 'show_recent_comments_widget_style', true ) );
		ob_start();
		do_action( 'wp_head' );
		ob_end_clean();
		$this->assertFalse( apply_filters( 'show_recent_comments_widget_style', true ) );
	}

	/**
	 * Test remove_head_style_in_amp when not in AMP.
	 *
	 * @covers AMP_Widget_Recent_Comments::remove_head_style_in_amp()
	 */
	public function test_remove_head_style_in_amp_not() {
		remove_theme_support( 'amp' );
		new AMP_Widget_Recent_Comments();
		$this->assertTrue( apply_filters( 'show_recent_comments_widget_style', true ) );
		ob_start();
		do_action( 'wp_head' );
		ob_end_clean();
		$this->assertTrue( apply_filters( 'show_recent_comments_widget_style', true ) );
	}
}
