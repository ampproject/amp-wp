<?php

class WPCOM_AMP_Polldaddy_Embed extends AMP_Base_Embed_Handler {
	public function register_embed() {
		add_shortcode( 'polldaddy', array( $this, 'shortcode' ) );
	}

	public function unregister_embed() {
		remove_shortcode( 'polldaddy', array( $this, 'shortcode' ) );
	}

	public function shortcode( $attr ) {
		$url = 'https://polldaddy.com/';
		if ( ! empty( $attr['poll'] ) ) {
			$url .= 'poll/' . $attr['poll'] . '/';
			$name = ! empty( $attr['title'] ) ? $attr['title'] : __( 'View Poll' );
		} else if ( !empty( $attr['survey'] ) ) { // Surveys and Quizzes both use attr survey
			$url .= 's/' . $attr['survey'] . '/';
			$name = ! empty( $attr['title'] ) ? $attr['title'] : __( 'View Survey' );
		} else {
			return ''; // We can't embed anything useful for rating
		}

		return '<p><a href="' . esc_url( $url ) . '">' . esc_html( $name ) . '</a></p>';
	}
}
