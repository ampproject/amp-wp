<?php
/**
 * Class AMP_Widget_Text
 *
 * @since 0.7.0
 * @package AMP
 */

_deprecated_file( __FILE__, '1.5.2' );

if ( class_exists( 'WP_Widget_Text' ) ) {
	/**
	 * Class AMP_Widget_Text
	 *
	 * @since 0.7.0
	 * @deprecated As of 1.5.2 the AMP_Core_Block_Handler will sanitize the core widgets instead.
	 * @package AMP
	 */
	class AMP_Widget_Text extends WP_Widget_Text {

		/**
		 * Overrides the parent callback that strips width and height attributes.
		 *
		 * @param array $matches The matches returned from preg_replace_callback().
		 * @return string $html The markup, unaltered.
		 */
		public function inject_video_max_width_style( $matches ) {
			if ( is_amp_endpoint() ) {
				return $matches[0];
			}
			return parent::inject_video_max_width_style( $matches );
		}
	}

}
