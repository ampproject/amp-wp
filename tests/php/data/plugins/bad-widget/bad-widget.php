<?php
/**
 * Plugin Name: Bad Widget
 * Description: Widget which outputs a script.
 * Version: 0.2
 */

// phpcs:ignoreFile

add_action(
	'widgets_init',
	function () {
		if ( ! class_exists( 'Bad_Widget' ) ) {
			require_once __DIR__ . '/class-bad-widget.php';
		}
		register_widget( 'Bad_Widget' );

		// Also register an old non-multi widget.
		$id          = 'bad_single';
		$name        = 'Bad Single';
		$widget_ops  = [ 'classname' => 'widget_bad_single' ];
		$control_ops = [
			'width'   => 500,
			'height'  => 100,
			'id_base' => $id,
		];
		wp_register_sidebar_widget(
			$id,
			$name,
			function( $args, $instance ) {
				unset( $instance );
				echo $args['before_widget'];
				echo $args['before_title'] . 'Bad Single Widget' . $args['after_title'];
				echo '<script>document.write("Bad widget!!!");</script><noscript>Bad widget fallback!</noscript>';
				echo $args['after_widget'];
			},
			$widget_ops,
			[]
		);
		wp_register_widget_control( $id, $name, function() {}, $control_ops, [] );
	}
);
