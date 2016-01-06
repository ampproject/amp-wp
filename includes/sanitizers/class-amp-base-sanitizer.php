<?php

abstract class AMP_Base_Sanitizer {
	protected $dom;
	private $body_node;
	protected $did_convert_elements = false;

	public function __construct( $dom ) {
		$this->dom = $dom;
		$this->body_node = $this->dom->getElementsByTagName( 'body' )->item( 0 );
	}

	abstract public function sanitize( $amp_attributes = array() );

	public function get_scripts() {
		return array();
	}

	public function get_body_node() {
		return $this->body_node;
	}

	public function has_tag( $tag ) {
		return 0 !== $this->dom->getElementsByTagName( $tag )->length;
	}

	public function get_tags( $tag ) {
		return $this->dom->getElementsByTagName( $tag );
	}

	public function add_attributes_to( $node, $attributes ) {
		foreach ( $attributes as $name => $value ) {
			$attr = $this->dom->createAttribute( $name );
			if ( '' !== $value ) {
				$attr->value = $value;
			}
			$node->appendChild( $attr );
		}
	}

	protected function url_has_extension( $url, $ext ) {
		$path = parse_url( $url, PHP_URL_PATH );
		return $ext === substr( $path, -strlen( $ext ) );
	}
}
