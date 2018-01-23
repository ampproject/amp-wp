<?php
/**
 * Class AMP_Widget_Media_Image
 *
 * @package AMP
 */

if ( class_exists( 'WP_Widget_Media_Image' ) ) {
	/**
	 * Class AMP_Widget_Media_Image
	 *
	 * @package AMP
	 */
	class AMP_Widget_Media_Image extends WP_Widget_Media_Image {

		/**
		 * Echoes the markup of the widget.
		 *
		 * @param array $instance Data for widget.
		 * @return void.
		 */
		public function render_media( $instance ) {
			ob_start();
			parent::render_media( $instance );
			$output = ob_get_clean();
			echo AMP_Theme_Support::filter_the_content( $output ); // WPCS: XSS ok.
		}

	}
}
