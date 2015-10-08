<?php

abstract class AMP_Converter {
	protected $content;

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
		preg_match_all( '#<' . $tag . '([^>]+?)(></' . $tag . '>|[\/]?>)#i', $this->content, $tags, PREG_SET_ORDER );
		return $tags;
	}

	protected function build_attributes_string( $attributes ) {
		$string = array();
		foreach ( $attributes as $name => $value ) {
			if ( '' === $value ) {
				$string[] = sprintf( '%s', $name );
			} else {
				$string[] = sprintf( '%s="%s"', $name, esc_attr( $value ) );
			}
		}
		return implode( ' ', $string );
	}
}
