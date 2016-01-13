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
}
