<?php

abstract class AMP_Converter {
	abstract public function convert( $content, $amp_attributes = array() );

	public function has_tag( $content, $tag ) {
		return false !== stripos( $content, sprintf( '<%s', $tag ) );
	}

	public function get_tags( $content, $tag ) {
		preg_match_all( '#<' . $tag . '([^>]+?)(></' . $tag . '>|[\/]?>)#i', $content, $tags, PREG_SET_ORDER );
		return $tags;
	}

	protected function build_attributes_string( $attributes ) {
		$string = '';
		foreach ( $attributes as $name => $value ) {
			$string .= sprintf( ' %s="%s"', $name, esc_attr( $value ) );
		}
		return $string;
	}
}
