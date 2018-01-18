<?php
/**
 * Class AMP_Widget_Media_Audio
 *
 * @package AMP
 */

/**
 * Class AMP_Widget_Media_Audio
 *
 * @package AMP
 */
class AMP_Widget_Media_Audio extends WP_Widget_Media_Audio {

	/**
	 * Echoes the markup of the widget.
	 *
	 * @todo filter $output, to convert <audio> to <amp-audio>.
	 * @see https://github.com/Automattic/amp-wp/issues/864
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
