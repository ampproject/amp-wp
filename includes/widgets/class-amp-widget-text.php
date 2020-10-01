<?php
/**
 * Class AMP_Widget_Text
 *
 * @since 0.7.0
 * @package AMP
 * @codeCoverageIgnore
 */

_deprecated_file( __FILE__, '2.0.0' );

if ( class_exists( 'WP_Widget_Text' ) ) {
	/**
	 * Class AMP_Widget_Text
	 *
	 * @since 0.7.0
	 * @deprecated As of 2.0 the AMP_Core_Block_Handler will sanitize the core widgets instead.
	 * @internal
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
			if ( amp_is_request() ) {
				return $matches[0];
			}
			return parent::inject_video_max_width_style( $matches );
		}
	}

}
