<?php
/**
 * Class AMP_Widget_Archives
 *
 * @package AMP
 */

/**
 * Class AMP_Widget_Archives
 *
 * @package AMP
 */
class AMP_Widget_Archives extends WP_Widget_Archives {

	/**
	 * Echoes the markup of the widget.
	 *
	 * @todo filter $output, to strip the onchange attribute
	 * @see https://github.com/Automattic/amp-wp/issues/864
	 * @param array $args Widget display data.
	 * @param array $instance Data for widget.
	 * @return void.
	 */
	public function widget( $args, $instance ) {
		ob_start();
		parent::widget( $args, $instance );
		$output = ob_get_clean();
		echo $output; // WPCS: XSS ok.
	}

}
