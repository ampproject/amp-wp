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
	 * Instance of the widget.
	 *
	 * @var object
	 */
	public $instance;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function setUp() {
		parent::setUp();
		$amp_widgets = new AMP_Widgets();
		$amp_widgets->register_widgets();
		$this->instance = new AMP_Widget_Recent_Comments();
	}

	/**
	 * Test construct().
	 *
	 * @see AMP_Widget_Recent_Comments::__construct().
	 */
	public function test_construct() {
		global $wp_widget_factory;
		$amp_categories = $wp_widget_factory->widgets['AMP_Widget_Recent_Comments'];

		$this->assertEquals( 'recent-comments', $amp_categories->id_base );
		$this->assertEquals( 'Recent Comments', $amp_categories->name );
		$this->assertEquals( 'widget_recent_comments', $amp_categories->widget_options['classname'] );
		$this->assertEquals( true, $amp_categories->widget_options['customize_selective_refresh'] );
		$this->assertEquals( 'Your site&#8217;s most recent comments.', $amp_categories->widget_options['description'] );
		$this->assertFalse( apply_filters( 'show_recent_comments_widget_style', true ) );
	}

}
