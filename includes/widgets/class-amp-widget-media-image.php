<?php
/**
 * Class AMP_Widget_Media_Image
 *
 * @package AMP
 */

/**
 * Class AMP_Widget_Media_Image
 *
 * @package AMP
 */
class AMP_Widget_Media_Image extends WP_Widget_Media_Image {

	/**
	 * Echoes the markup of the widget.
	 *
	 * @todo filter $output, to convert <imp> to <amp-img> and remove the 'style' attribute.
	 * @see https://github.com/Automattic/amp-wp/issues/864
	 * @param array $instance Data for widget.
	 * @return void.
	 */
	public function render_media( $instance ) {
		ob_start();
		parent::render_media( $instance );
		$output = ob_get_clean();
		echo $output; // WPCS: XSS ok.
	}

}
