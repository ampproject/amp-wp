<?php
/**
 * Class AMP_Widget_Recent_Comments
 *
 * @package AMP
 */

/**
 * Class AMP_Widget_Recent_Comments
 *
 * @package AMP
 */
class AMP_Widget_Recent_Comments extends WP_Widget_Recent_Comments {

	/**
	 * Instantiates the widget, and prevents inline styling.
	 */
	public function __construct() {
		parent::__construct();
		add_filter( 'show_recent_comments_widget_style', '__return_false' );
	}

}
