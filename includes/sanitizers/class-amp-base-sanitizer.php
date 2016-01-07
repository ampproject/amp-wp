<?php

abstract class AMP_Base_Sanitizer {
	protected $dom;
	protected $did_convert_elements = false;

	public function __construct( $dom ) {
		$this->dom = $dom;
	}

	abstract public function sanitize( $amp_attributes = array() );

	public function get_scripts() {
		return array();
	}

	protected function url_has_extension( $url, $ext ) {
		$path = parse_url( $url, PHP_URL_PATH );
		return $ext === substr( $path, -strlen( $ext ) );
	}
}
