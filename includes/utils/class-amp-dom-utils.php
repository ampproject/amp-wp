<?php

class AMP_DOM_Utils {
	public static function get_dom_from_content( $content ) {
		$libxml_previous_state = libxml_use_internal_errors( true );

		$dom = new DOMDocument;
		// Wrap in dummy tags, since XML needs one parent node.
		// It also makes it easier to loop through nodes.
		// We can later use this to extract our nodes.
		// Add utf-8 charset so loadHTML does not have problems parsing it. See: http://php.net/manual/en/domdocument.loadhtml.php#78243
		$result = $dom->loadHTML( '<html><head><meta http-equiv="content-type" content="text/html; charset=utf-8"></head><body>' . $content . '</body></html>' );

		libxml_clear_errors();
		libxml_use_internal_errors( $libxml_previous_state );

		if ( ! $result ) {
			return false;
		}

		return $dom;
	}

	public static function get_content_from_dom( $dom ) {
		// Only want children of the body tag, since we have a subset of HTML.
		$out = '';
		$body = $dom->getElementsByTagName( 'body' )->item( 0 );
		foreach ( $body->childNodes as $node ) {
			$out .= $dom->saveXML( $node, LIBXML_NOEMPTYTAG );
		}
		return $out;
	}

	public static function create_node( $dom, $tag, $attributes ) {
		$node = $dom->createElement( $tag );
		self::add_attributes_to_node( $node, $attributes );
		return $node;
	}

	public static function get_node_attributes_as_assoc_array( $node ) {
		$attributes = array();
		foreach ( $node->attributes as $attribute ) {
			$attributes[ $attribute->nodeName ] = $attribute->nodeValue;
		}
		return $attributes;
	}

	public static function add_attributes_to_node( $node, $attributes ) {
		foreach ( $attributes as $name => $value ) {
			$node->setAttribute( $name, $value );
		}
	}

	public static function is_node_empty( $node ) {
		return 0 === $node->childNodes->length && empty( $node->textContent );
	}
}
