<?php

require_once( AMP__DIR__ . '/includes/embeds/class-amp-base-embed-handler.php');

class AMP_Pinterest_Embed_Handler extends AMP_Base_Embed_Handler {

	const URL_PATTERN = '#https?://(www\.)?pinterest\.com/pin/.*#i';

	protected $DEFAULT_WIDTH = 450;
	protected $DEFAULT_HEIGHT = 750;

	private static $script_slug = 'amp-pinterest';
	private static $script_src = 'https://cdn.ampproject.org/v0/amp-pinterest-0.1.js';

	public function register_embed() {
		wp_embed_register_handler(
			'amp-pinterest',
			self::URL_PATTERN,
			array($this, 'oembed'),
			-1);
	}

	public function unregister_embed() {
		wp_embed_unregister_handler('amp-pinterest', -1);
	}

	public function get_scripts() {
		if ( ! $this->did_convert_elements) {
			return array();
		}

		return array( self::$script_slug => self::$script_src);
	}

	public function oembed( $matches, $attr, $url, $rawattr ) {
		return $this->render( array( 'url' => $url ) );
	}

	public function render( $args ) {
		$args = wp_parse_args( $args, array(
			'url' => false,
		) );

		if ( empty( $args['url'] ) ) {
			return '';
		}

		$this->did_convert_elements = true;

		return AMP_HTML_Utils::build_tag(
			'amp-pinterest',
			array(
				'width' => $this->args['width'],
				'height' => $this->args['height'],
				'data-do' => "embedPin",
				'data-url' => $args['url'],
			)
		);
	}
}
