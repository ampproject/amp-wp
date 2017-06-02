<?php

require_once( AMP__DIR__ . '/includes/embeds/class-amp-base-embed-handler.php' );

class AMP_SoundCloud_Embed_Handler extends AMP_Base_Embed_Handler {
	const URL_PATTERN = '#https?://api\.soundcloud\.com/tracks/.*#i';
	protected $DEFAULT_HEIGHT = 200;

	private static $script_slug = 'amp-soundcloud';
	private static $script_src = 'https://cdn.ampproject.org/v0/amp-soundcloud-0.1.js';

	public function register_embed() {
		wp_embed_register_handler( 'amp-soundcloud', self::URL_PATTERN, array( $this, 'oembed' ), -1 );
		add_shortcode( 'soundcloud', array( $this, 'shortcode' ) );
	}

	public function unregister_embed() {
		wp_embed_unregister_handler( 'amp-soundcloud', -1 );
		remove_shortcode( 'soundcloud' );
	}

	public function get_scripts() {
		if ( ! $this->did_convert_elements ) {
			return array();
		}

		return array( self::$script_slug => self::$script_src );
	}

	public function oembed( $matches, $attr, $url, $rawattr ) {
		$track_id = $this->get_track_id_from_url( $url );
		return $this->render( array(
			'track_id' => $track_id,
		) );
	}

	public function shortcode( $attr ) {

		$track_id = false;

		if ( isset( $attr['id'] ) ) {
			$track_id = $attr['id'];
		} else {
			$url = false;
			if ( isset( $attr['url'] ) ) {
				$url = $attr['url'];
			} elseif ( isset( $attr[0] ) ) {
				$url = $attr[0];
			} elseif ( function_exists( 'shortcode_new_to_old_params' ) ) {
				$url = shortcode_new_to_old_params( $attr );
			}

			if ( ! empty( $url ) ) {
				$track_id = $this->get_track_id_from_url( $url );
			}
		}

		if ( empty( $track_id ) ) {
			return '';
		}

		return $this->render( array(
			'track_id' => $track_id,
		) );
	}

	public function render( $args ) {
		$args = wp_parse_args( $args, array(
			'track_id' => false,
		) );

		if ( empty( $args['track_id'] ) ) {
			return AMP_HTML_Utils::build_tag( 'a', array( 'href' => esc_url( $args['url'] ), 'class' => 'amp-wp-embed-fallback' ), esc_html( $args['url'] ) );
		}

		$this->did_convert_elements = true;

		return AMP_HTML_Utils::build_tag(
			'amp-soundcloud',
			array(
				'data-trackid' => $args['track_id'],
				'layout' => 'fixed-height',
				'height' => $this->args['height'],
			)
		);
	}

	private function get_track_id_from_url( $url ) {
		$parsed_url = AMP_WP_Utils::parse_url( $url );
		$tok = explode( '/', $parsed_url['path'] );
		$track_id = $tok[2];

		return $track_id;
	}
}
