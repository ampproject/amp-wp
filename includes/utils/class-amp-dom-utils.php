<?php

/**
 * Class AMP_DOM_Utils
 *
 * Functionality to simplify working with DOMDocuments and DOMElements.
 *
 */
class AMP_DOM_Utils {

	/**
	 * Return a valid DOMDocument representing arbitrary HTML content passed as a parameter.
	 *
	 * @see Reciprocal function get_content_from_dom()
	 *
	 * @since 0.2
	 *
	 * @param string $content Valid HTML content to be represented by a DOMDocument
	 *
	 * @return DOMDocument|false Returns DOMDocument, or false if conversion failed.
	 */
	public static function get_dom_from_content( $content ) {
		$libxml_previous_state = libxml_use_internal_errors( true );

		$dom = new DOMDocument;

		/*
		 * Wrap in dummy tags, since XML needs one parent node.
		 * It also makes it easier to loop through nodes.
		 * We can later use this to extract our nodes.
		 * Add utf-8 charset so loadHTML does not have problems parsing it.
		 * See: http://php.net/manual/en/domdocument.loadhtml.php#78243
		 */
		$result = $dom->loadHTML( '<html><head><meta http-equiv="content-type" content="text/html; charset=utf-8"></head><body>' . $content . '</body></html>' );

		libxml_clear_errors();
		libxml_use_internal_errors( $libxml_previous_state );

		if ( ! $result ) {
			return false;
		}

		return $dom;
	}

	/**
	 * Return valid HTML content extracted from the DOMDocument passed as a parameter.
	 *
	 * @see Reciprocal function get_dom_from_content()
	 *
	 * @since 0.2
	 *
	 * @param DOMDocument $dom Represents an HTML document from which to extract HTML content.
	 *
	 * @return string Returns the HTML content represented in the DOMDocument
	 */
	public static function get_content_from_dom( $dom ) {
		/**
		 * We only want children of the body tag, since we have a subset of HTML.
		 */
		$out  = '';
		$body = $dom->getElementsByTagName( 'body' )->item( 0 );

		/**
		 * Most AMP elements need closing tags. To force them, we cannot use
		 * saveHTML (node support is 5.3+) and LIBXML_NOEMPTYTAG results in
		 * issues with self-closing tags like `br` and `hr`. So, we're manually
		 * forcing closing tags.
		 */
		self::recursive_force_closing_tags( $dom, $body );

		foreach ( $body->childNodes as $node ) {
			$html = $dom->saveXML( $node );
			/**
			 * Whitespace just causes unit tests to fail... so whitespace begone.
			 */
			if ( '' === trim( $html ) ) {
				continue;
			}

			/**
			 * Travis w/PHP 7.1 generates <br></br> and <hr></hr> vs. <br/> and <hr/>, respectively.
			 * Seems like LIBXML_NOEMPTYTAG was passed, but as you can see it was not.
			 * This does not happen in my (@mikeschinkel) local testing, btw.
			 */
			$html = preg_replace( '#<(br|hr)></\1>#', '<$1/>', $html );

			/**
			 * Travis w/PHP 7.x generates <source ...></source> vs. <source ... />.
			 * Seems like LIBXML_NOEMPTYTAG was passed, but as you can see it was not.
			 * This does not happen in my (@mikeschinkel) local testing, btw.
			 */
			$html = preg_replace( '#></source>#', '/>', $html );

			$out .= $html;
		}

		return $out;
	}

	/**
	 * Create a new node w/attributes (a DOMElement) and add to the passed DOMDocument.
	 *
	 * @since 0.2
	 *
	 * @param DOMDocument $dom A representation of an HTML document to add the new node to.
	 * @param string $tag A valid HTML element tag for the element to be added
	 * @param string[] $attributes One of more valid attributes for the new node.
	 *
	 * @return DOMElement|false The DOMElement for the given $tag, or false on failure
	 */
	public static function create_node( $dom, $tag, $attributes ) {
		$node = $dom->createElement( $tag );
		self::add_attributes_to_node( $node, $attributes );

		return $node;
	}

	/**
	 * Extract a DOMElement node's HTML element attributes and return as an array.
	 *
	 * @since 0.2
	 *
	 * @param DOMNode $node Represents an HTML element for which to extract attributes.
	 *
	 * @return string[] The attributes for the passed node, or an
	 *                  empty array if it has no attributes.
	 */
	public static function get_node_attributes_as_assoc_array( $node ) {
		$attributes = array();
		if ( ! $node->hasAttributes() ) {
			return $attributes;
		}

		foreach ( $node->attributes as $attribute ) {
			$attributes[ $attribute->nodeName ] = $attribute->nodeValue;
		}

		return $attributes;
	}

	/**
	 * Add one or more HTML element attributes to a node's DOMElement.
	 *
	 * @since 0.2
	 *
	 * @param DOMElement $node Represents an HTML element
	 * @param string[] $attributes One or more attributes for the node's HTML element
	 */
	public static function add_attributes_to_node( $node, $attributes ) {
		foreach ( $attributes as $name => $value ) {
			$node->setAttribute( $name, $value );
		}
	}

	/**
	 * Determines if a DOMElement's node is empty or not..
	 *
	 * @since 0.2
	 *
	 * @param DOMElement $node Represents an HTML element
	 *
	 * @return bool Returns true if the DOMElement has no child nodes and
	 *              the textContent property of the DOMElement is empty;
	 *              Otherwise it returns false.
	 */
	public static function is_node_empty( $node ) {
		return false === $node->hasChildNodes()
		       && empty( $node->textContent );
	}

	/**
	 * Forces HTML element closing tags given a DOMDocument and optional DOMElement
	 *
	 * @since 0.2
	 *
	 * @param DOMDocument $dom Represents HTML document on which to force closing tags.
	 * @param DOMElement $node Represents HTML element to start closing tags on.
	 *                         If not passed, defaults to first child of body.
	 *
	 */
	public static function recursive_force_closing_tags( $dom, $node = null ) {

		if ( is_null( $node ) ) {
			$node = $dom->getElementsByTagName( 'body' )->item( 0 );
		}

		if ( XML_ELEMENT_NODE !== $node->nodeType ) {
			return;
		}

		if ( self::is_self_closing_tag( $node->nodeName ) ) {
			/*
			 * Ensure there is no text content to accidentally force a child
			 */
			$node->textContent = null;
			return;
		}

		if ( self::is_node_empty( $node ) ) {
			$text_node = $dom->createTextNode( '' );
			$node->appendChild( $text_node );

			return;
		}

		$num_children = $node->childNodes->length;
		for ( $i = $num_children - 1; $i >= 0; $i -- ) {
			$child = $node->childNodes->item( $i );
			self::recursive_force_closing_tags( $dom, $child );
		}

	}

	/**
	 * Determines if an HTML element tag is validly a self-closing tag per W3C HTML5 specs.
	 *
	 * @since 0.2
	 *
	 * @param string $tag
	 *
	 * @return bool Returns true if a valid self-closing tag, false if not.
	 */
	private static function is_self_closing_tag( $tag ) {
		// This function is called a lot; the static var prevents having to re-create the array every time.
		static $self_closing_tags;
		if ( ! isset( $self_closing_tags ) ) {
			// https://www.w3.org/TR/html5/syntax.html#serializing-html-fragments
			// Not all are valid AMP, but we include them for completeness.
			$self_closing_tags = array(
				'area',
				'base',
				'basefont',
				'bgsound',
				'br',
				'col',
				'embed',
				'frame',
				'hr',
				'img',
				'input',
				'keygen',
				'link',
				'meta',
				'param',
				'source',
				'track',
				'wbr',
			);
		}

		return in_array( $tag, $self_closing_tags, true );
	}
}