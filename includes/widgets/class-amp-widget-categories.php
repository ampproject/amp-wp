<?php
/**
 * Class AMP_Widget_Categories
 *
 * @package AMP
 */

/**
 * Class AMP_Widget_Categories
 *
 * @package AMP
 */
class AMP_Widget_Categories extends WP_Widget_Categories {

	/**
	 * Echoes the markup of the widget.
	 *
	 * @todo filter $output, to strip the <script> tag.
	 * @see https://github.com/Automattic/amp-wp/issues/864
	 * @param array $args Widget display data.
	 * @param array $instance Data for widget.
	 * @return void.
	 */
	public function widget( $args, $instance ) {
		ob_start();
		parent::widget( $args, $instance );
		$output = ob_get_clean();
		echo AMP_Theme_Support::filter_the_content( $output ); // WPCS: XSS ok.
	}

}
