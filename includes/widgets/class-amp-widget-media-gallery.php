<?php
/**
 * Class AMP_Widget_Media_Gallery
 *
 * @package AMP
 */

/**
 * Class AMP_Widget_Media_Gallery
 *
 * @package AMP
 */
class AMP_Widget_Media_Gallery extends WP_Widget_Media_Gallery {

	/**
	 * Echoes the markup of the widget.
	 *
	 * @todo filter $output, to convert <imp> to <amp-img> and remove the <style>.
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
