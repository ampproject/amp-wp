<?php
/**
 * Class AMP_Widget_Media_Gallery
 *
 * @since 0.7.0
 * @package AMP
 */

/**
 * Class AMP_Widget_Media_Gallery
 *
 * @since 0.7.0
 * @package AMP
 */
class AMP_Widget_Media_Gallery extends WP_Widget_Media_Gallery {

	/**
	 * Renders the markup of the widget.
	 *
	 * Mainly copied from WP_Widget_Media_Gallery::render_media().
	 * But instead of calling shortcode_gallery(),
	 * It uses this plugin's embed handler for galleries.
	 *
	 * @since 0.7.0
	 *
	 * @param array $instance Data for widget.
	 * @return void
	 */
	public function render_media( $instance ) {
		if ( ! is_amp_endpoint() ) {
			parent::render_media( $instance );
			return;
		}

		$instance       = array_merge( wp_list_pluck( $this->get_instance_schema(), 'default' ), $instance );
		$shortcode_atts = array_merge(
			$instance,
			array(
				'link' => $instance['link_type'],
			)
		);

		if ( isset( $instance['orderby_random'] ) && ( true === $instance['orderby_random'] ) ) {
			$shortcode_atts['orderby'] = 'rand';
		}

		$handler = new AMP_Gallery_Embed_Handler();
		echo $handler->shortcode( $shortcode_atts ); // WPCS: XSS ok.
	}

}
