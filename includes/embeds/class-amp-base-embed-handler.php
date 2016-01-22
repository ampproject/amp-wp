<?php

// Used by some children
require_once( dirname( dirname( __FILE__ ) ) . '/utils/class-amp-html-utils.php' );

abstract class AMP_Base_Embed_Handler {
	const DEFAULT_WIDTH = 600;
	const DEFAULT_HEIGHT = 480;

	protected $args = array();
	protected $did_convert_elements = false;

	abstract function register_embed();
	abstract function unregister_embed();

	function __construct( $args = array() ) {
		$this->args = wp_parse_args( $args, array(
			'width' => self::DEFAULT_WIDTH,
			'height' => self::DEFAULT_HEIGHT,
		) );
	}

	public function get_scripts() {
		return array();
	}
}
