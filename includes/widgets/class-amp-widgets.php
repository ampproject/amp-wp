<?php
/**
 * Register widgets, and add actions.
 *
 * @package AMP
 */

/**
 * Register the widgets.
 */
class AMP_Widgets {

	/**
	 * Add the actions.
	 *
	 * @return void.
	 */
	public function init() {
		add_action( 'widgets_init', array( $this, 'register_widgets' ) );
		add_action( 'show_recent_comments_widget_style', '__return_false' );
		add_action( 'wp_footer', array( $this, 'dequeue_scripts' ) );
	}

	/**
	 * Add the filters.
	 *
	 * @return void.
	 */
	public function register_widgets() {
		$widgets = self::get_widgets();
		foreach ( $widgets as $native_wp_widget => $amp_widget ) {
			unregister_widget( $native_wp_widget );
			register_widget( $amp_widget );
		}
	}

	/**
	 * Get the widgets to unregister and register.
	 *
	 * @return array $widgets An associative array, with the previous WP widget mapped to the new AMP widget.
	 */
	public function get_widgets() {
		return array(
			'WP_Widget_Archives'   => 'AMP_Widget_Archives',
			'WP_Widget_Categories' => 'AMP_Widget_Categories',
		);
	}

	/**
	 * Dequeue widget scripts and styles, which aren't allowed in AMP.
	 *
	 * Uses the action 'wp_footer' in order to prevent
	 * 'wp_print_footer_scripts' from outputting the scripts.
	 *
	 * @return void.
	 */
	public function dequeue_scripts() {
		wp_dequeue_script( 'wp-mediaelement' );
		wp_dequeue_script( 'mediaelement-vimeo' );
		wp_dequeue_style( 'wp-mediaelement' );
	}

}
