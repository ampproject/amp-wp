<?php

require_once( dirname( __FILE__ ) . '/class-amp-embed-handler.php' );

// Much of this class is borrowed from Jetpack embeds
class AMP_Instagram_Embed_Handler extends AMP_Embed_Handler {
	const SHORT_URL_HOST = 'instagr.am';
	const URL_PATTERN = '#http(s?)://(www\.)?instagr(\.am|am\.com)/p/([^/?]+)#i';
	const DEFAULT_WIDTH = 600;
	const DEFAULT_HEIGHT = 480;

	private static $script_slug = 'amp-instagram';
	private static $script_src = 'https://cdn.ampproject.org/v0/amp-instagram-0.1.js';

	private $args;

	function __construct( $args = array() ) {
		$this->args = wp_parse_args( $args, array(
			'width' => self::DEFAULT_WIDTH,
			'height' => self::DEFAULT_HEIGHT,
		) );

		wp_embed_register_handler( 'amp-instagram', self::URL_PATTERN, array( $this, 'oembed' ), -1 );
		add_shortcode( 'instagram', array( $this, 'shortcode' ) );
	}

	public function get_scripts() {
		if ( ! $this->did_convert_elements ) {
			return array();
		}

		return array( self::$script_slug => self::$script_src );
	}

	public function shortcode( $attr ) {
		$url = false;

		$instagram_id = false;
		if ( isset( $attr['url'] ) ) {
			$url = trim( $attr['url'] );
		}

		if ( empty( $url ) ) {
			return '';
		}

		$instagram_id = $this->get_instagram_id_from_url( $url );

		return $this->render( array(
			'url' => $url,
			'instagram_id' => $instagram_id,
		) );
	}

	public function oembed( $matches, $attr, $url, $rawattr ) {
		return $this->render( array( 'url' => $url, 'instagram_id' =>  end($matches)) );
	}

	public function render( $args ) {
		$this->did_convert_elements = true;

		$args = wp_parse_args( $args, array(
			'url' => false,
			'instagram_id' => false,
		) );

		if ( empty( $args['instagram_id'] ) ) {
			return AMP_HTML_Utils::build_tag( 'a', array( 'href' => $args['url'], 'class' => 'amp-wp-fallback' ), $args['url'] );
		}

		return AMP_HTML_Utils::build_tag(
			'amp-instagram',
			wp_parse_args( array(
				'data-shortcode' => $args['instagram_id'],
				'layout' => 'responsive',
			), $this->args )
		);
	}

	private function get_instagram_id_from_url( $url ) {
		$url_path = parse_url( $url, PHP_URL_PATH );

		// /p/{id} on both, short url and normal urls
		$instagram_id = mb_substr($url_path, 3);

		if( !empty($instagram_id) ) {
			return $instagram_id;
		}
		return false;
	}
}
