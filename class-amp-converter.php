<?php

require_once( dirname( __FILE__ ) . '/class-amp-html-utils.php' );

abstract class AMP_Converter {
	protected $content;
	protected $did_convert_elements = false;

	public function __construct( $content ) {
		$this->content = $content;
	}

	abstract public function convert( $amp_attributes = array() );

	public function get_scripts() {
		return array();
	}

	public function has_tag( $tag ) {
		return false !== stripos( $this->content, sprintf( '<%s', $tag ) );
	}

	public function get_tags( $tag ) {
		preg_match_all( '#<(' . $tag . ')([^>]+?)(>(.*?)</\\1>|[\/]?>)#si', $this->content, $tags, PREG_SET_ORDER );
		return $tags;
	}

	protected function build_attributes_string( $attributes ) {
		return AMP_HTML_Utils::build_attributes_string( $attributes );
	}

	protected function url_has_extension( $url, $ext ) {
		$path = parse_url( $url, PHP_URL_PATH );
		return $ext === substr( $path, -strlen( $ext ) );
	}
}
