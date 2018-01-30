<?php
/**
 * Class AMP_Widget_Media_Gallery
 *
 * @since 0.7.0
 * @package AMP
 */

if ( class_exists( 'WP_Widget_Media_Gallery' ) ) {
	/**
	 * Class AMP_Widget_Media_Gallery
	 *
	 * @todo This subclass can eventually be eliminated once #25435 is merged and the WP_Widget_Media_Gallery::render_media() does do_single_shortcode( 'gallery', $instance ).
	 * @link https://core.trac.wordpress.org/ticket/25435
	 * @since 0.7.0
	 * @package AMP
	 */
	class AMP_Widget_Media_Gallery extends WP_Widget_Media_Gallery {

		/**
		 * Renders the markup of the widget.
		 *
		 * Mainly copied from WP_Widget_Media_Gallery::render_media().
		 * But instead of calling shortcode_gallery(), it calls do_shortcode().
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

			/*
			 * The following calls do_shortcode() in case another plugin overrides the gallery shortcode.
			 * The AMP_Gallery_Embed_Handler in the plugin is doing this itself, but other plugins may
			 * do it as well, so this ensures that a plugin has the option to override the gallery behavior
			 * via registering a different gallery shortcode handler. The shortcode serialization can be
			 * eliminated once WP Trac #25435 is merged and the Gallery widget uses the proposed do_single_shortcode().
			 */
			$shortcode_atts_str = '';
			if ( is_array( $shortcode_atts['ids'] ) ) {
				$shortcode_atts['ids'] = join( ',', $shortcode_atts['ids'] );
			}
			foreach ( $shortcode_atts as $key => $value ) {
				$shortcode_atts_str .= sprintf( ' %s="%s"', $key, esc_attr( $value ) );
			}
			echo do_shortcode( "[gallery $shortcode_atts_str]" ); // WPCS: XSS ok.
		}

	}
}
