<?php

// Used by some children
require_once( AMP__DIR__ . '/includes/utils/class-amp-html-utils.php' );

abstract class AMP_Base_Embed_Handler {
	protected $default_width = 600;
	protected $default_height = 480;

	protected $args = array();
	protected $did_convert_elements = false;

	abstract function register_embed();
	abstract function unregister_embed();

	function __construct( $args = array() ) {
		$this->args = wp_parse_args( $args, array(
			'width' => $this->default_width,
			'height' => $this->default_height,
		) );
	}

	public function get_scripts() {
		return array();
	}
}
