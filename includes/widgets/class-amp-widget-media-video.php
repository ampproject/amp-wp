<?php
/**
 * Class AMP_Widget_Media_Video
 *
 * @since 0.7.0
 * @package AMP
 */

if ( class_exists( 'WP_Widget_Media_Video' ) ) {
	/**
	 * Class AMP_Widget_Media_Video
	 *
	 * @since 0.7.0
	 * @package AMP
	 */
	class AMP_Widget_Media_Video extends WP_Widget_Media_Video {

		/**
		 * Overrides the parent callback that strips width and height values.
		 *
		 * @param string $html Video shortcode HTML output.
		 * @return string HTML Output.
		 */
		public function inject_video_max_width_style( $html ) {
			if ( is_amp_endpoint() ) {
				return $html;
			}
			return parent::inject_video_max_width_style( $html );
		}

	}

}
