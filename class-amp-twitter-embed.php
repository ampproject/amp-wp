<?php

require_once( dirname( __FILE__ ) . '/class-amp-embed-handler.php' );

// Much of this class is borrowed from Jetpack embeds
class AMP_Twitter_Embed_Handler extends AMP_Embed_Handler {
	const URL_PATTERN = '#http(s|):\/\/twitter\.com(\/\#\!\/|\/)([a-zA-Z0-9_]{1,20})\/status(es)*\/(\d+)#i';
	const DEFAULT_WIDTH = 600;
	const DEFAULT_HEIGHT = 400;

	private static $script_slug = 'amp-twitter';
	private static $script_src = 'https://cdn.ampproject.org/v0/amp-twitter-0.1.js';

	private $args;

	function __construct( $args = array() ) {
		$this->args = wp_parse_args( $args, array(
			'width' => self::DEFAULT_WIDTH,
			'height' => self::DEFAULT_HEIGHT,
		) );

		add_shortcode( 'tweet', array( $this, 'shortcode' ) );
		wp_embed_register_handler( 'amp-twitter', self::URL_PATTERN, array( $this, 'oembed' ), -1 );
	}

	public function get_scripts() {
		if ( ! $this->did_convert_elements ) {
			return array();
		}

		return array( self::$script_slug => self::$script_src );
	}

	function shortcode( $attr ) {
		$attr = wp_parse_args( $attr, array(
			'tweet' => false,
		) );

		$id = false;
		if ( intval( $attr['tweet'] ) ) {
			$id = intval( $attr['tweet'] );
		} else {
			preg_match( self::URL_PATTERN, $attr['tweet'], $matches );
			if ( isset( $matches[5] ) && intval( $matches[5] ) ) {
				$id = intval( $matches[5] );
			}

			if ( empty( $id ) ) {
				return '';
			}
		}

		$this->did_convert_elements = true;

		return AMP_HTML_Utils::build_tag( 'amp-twitter', array_merge( $this->args, array(
			'data-tweetid' => $id,
			'layout' => 'responsive',
		) ) );
	}

	function oembed( $matches, $attr, $url, $rawattr ) {
		$id = false;

		if ( isset( $matches[5] ) && intval( $matches[5] ) ) {
			$id = intval( $matches[5] );
		}

		if ( ! $id ) {
			return '';
		}

		return $this->shortcode( array( 'tweet' => $id ) );
	}
}
