<?php
// phpcs:ignoreFile

class Bad_Widget extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		parent::__construct( 'bad', 'Bad Widget' );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args     Args.
	 * @param array $instance Instance.
	 */
	public function widget( $args, $instance ) {
		unset( $instance );
		echo $args['before_widget'];
		echo $args['before_title'] . 'Bad Multi Widget' . $args['after_title'];

		echo '<script>document.write("Bad widget!!!");</script><noscript>Bad widget fallback!</noscript>';
		echo $args['after_widget'];
	}
}
