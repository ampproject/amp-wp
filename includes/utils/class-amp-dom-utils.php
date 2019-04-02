<?php
/**
 * Class AMP_DOM_Utils.
 *
 * @package AMP
 */

/**
 * Class AMP_DOM_Utils
 *
 * Functionality to simplify working with DOMDocuments and DOMElements.
 */
class AMP_DOM_Utils {

	/**
	 * HTML elements that are self-closing.
	 *
	 * Not all are valid AMP, but we include them for completeness.
	 *
	 * @since 0.7
	 * @link https://www.w3.org/TR/html5/syntax.html#serializing-html-fragments
	 * @var array
	 */
	private static $self_closing_tags = array(
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

	/**
	 * Stored noscript/comment replacements for libxml<2.8.
	 *
	 * @since 0.7
	 * @var array
	 */
	public static $noscript_placeholder_comments = array();

	/**
	 * Return a valid DOMDocument representing HTML document passed as a parameter.
	 *
	 * @since 0.7
	 * @see AMP_DOM_Utils::get_content_from_dom_node()
	 *
	 * @param string $document Valid HTML document to be represented by a DOMDocument.
	 * @return DOMDocument|false Returns DOMDocument, or false if conversion failed.
	 */
	public static function get_dom( $document ) {
		$libxml_previous_state = libxml_use_internal_errors( true );

		$dom = new DOMDocument();

		// @todo In the future consider an AMP_DOMDocument subclass that does this automatically. See <https://github.com/ampproject/amp-wp/pull/895/files#r163825513>.
		$document = self::convert_amp_bind_attributes( $document );

		// Force all self-closing tags to have closing tags since DOMDocument isn't fully aware.
		$document = preg_replace(
			'#<(' . implode( '|', self::$self_closing_tags ) . ')[^>]*>(?!</\1>)#',
			'$0</$1>',
			$document
		);

		// Deal with bugs in older versions of libxml.
		$added_back_compat_meta_content_type = false;
		if ( version_compare( LIBXML_DOTTED_VERSION, '2.8', '<' ) ) {
			/*
			 * Replace noscript elements with placeholders since libxml<2.8 can parse them incorrectly.
			 * When appearing in the head element, a noscript can cause the head to close prematurely
			 * and the noscript gets moved to the body and anything after it which was in the head.
			 * See <https://stackoverflow.com/questions/39013102/why-does-noscript-move-into-body-tag-instead-of-head-tag>.
			 * This is limited to only running in the head element because this is where the problem lies,
			 * and it is important for the AMP_Script_Sanitizer to be able to access the noscript elements
			 * in the body otherwise.
			 */
			$document = preg_replace_callback(
				'#^.+?(?=<body)#is',
				function( $head_matches ) {
					return preg_replace_callback(
						'#<noscript[^>]*>.*?</noscript>#si',
						function( $noscript_matches ) {
							$placeholder = sprintf( '<!--noscript:%s-->', (string) wp_rand() );
							AMP_DOM_Utils::$noscript_placeholder_comments[ $placeholder ] = $noscript_matches[0];
							return $placeholder;
						},
						$head_matches[0]
					);
				},
				$document
			);

			/*
			 * Add a pre-HTML5-style declaration of the encoding since libxml<2.8 doesn't recognize
			 * HTML5's meta charset. See <https://bugzilla.gnome.org/show_bug.cgi?id=655218>.
			 */
			$document = preg_replace(
				'#(?=<meta\s+charset=["\']?([a-z0-9_-]+))#i',
				'<meta http-equiv="Content-Type" content="text/html; charset=$1" id="meta-http-equiv-content-type">',
				$document,
				1,
				$count
			);
			if ( 1 === $count ) {
				$added_back_compat_meta_content_type = true;
			}
		}

		/*
		 * Wrap in dummy tags, since XML needs one parent node.
		 * It also makes it easier to loop through nodes.
		 * We can later use this to extract our nodes.
		 * Add charset so loadHTML does not have problems parsing it.
		 */
		$result = $dom->loadHTML( $document );

		libxml_clear_errors();
		libxml_use_internal_errors( $libxml_previous_state );

		if ( ! $result ) {
			return false;
		}

		// Remove pre-HTML5-style encoding declaration if added above.
		if ( $added_back_compat_meta_content_type ) {
			$meta_http_equiv_element = $dom->getElementById( 'meta-http-equiv-content-type' );
			if ( $meta_http_equiv_element ) {
				$meta_http_equiv_element->parentNode->removeChild( $meta_http_equiv_element );
			}
		}

		return $dom;
	}

	/**
	 * Get attribute prefix for converted amp-bind attributes.
	 *
	 * This contains a random string to prevent HTML content containing this data- attribute
	 * originally from being mutated to contain an amp-bind attribute when attributes are restored.
	 *
	 * @since 0.7
	 * @see \AMP_DOM_Utils::convert_amp_bind_attributes()
	 * @see \AMP_DOM_Utils::restore_amp_bind_attributes()
	 * @link https://www.ampproject.org/docs/reference/components/amp-bind
	 *
	 * @return string HTML5 data-* attribute name prefix for AMP binding attributes.
	 */
	public static function get_amp_bind_placeholder_prefix() {
		static $attribute_prefix;
		if ( ! isset( $attribute_prefix ) ) {
			$attribute_prefix = sprintf( 'amp-binding-%s-', md5( wp_rand() ) );
		}
		return $attribute_prefix;
	}

	/**
	 * Get amp-mustache tag/placeholder mappings.
	 *
	 * @since 0.7
	 * @see \wpdb::placeholder_escape()
	 *
	 * @return array Mapping of mustache tag token to its placeholder.
	 */
	private static function get_mustache_tag_placeholders() {
		static $placeholders;
		if ( ! isset( $placeholders ) ) {
			$salt = wp_rand();

			// Note: The order of these tokens is important, as it determines the order of the order of the replacements.
			$tokens       = array(
				'{{{',
				'}}}',
				'{{#',
				'{{^',
				'{{/',
				'{{/',
				'{{',
				'}}',
			);
			$placeholders = array();
			foreach ( $tokens as $token ) {
				$placeholders[ $token ] = '_amp_mustache_' . md5( $salt . $token );
			}
		}
		return $placeholders;
	}

	/**
	 * Replace AMP binding attributes with something that libxml can parse (as HTML5 data-* attributes).
	 *
	 * This is necessary because attributes in square brackets are not understood in PHP and
	 * get dropped with an error raised:
	 * > Warning: DOMDocument::loadHTML(): error parsing attribute name
	 * This is a reciprocal function of AMP_DOM_Utils::restore_amp_bind_attributes().
	 *
	 * @since 0.7
	 * @see \AMP_DOM_Utils::convert_amp_bind_attributes()
	 * @link https://www.ampproject.org/docs/reference/components/amp-bind
	 *
	 * @param string $html HTML containing amp-bind attributes.
	 * @return string HTML with AMP binding attributes replaced with HTML5 data-* attributes.
	 */
	public static function convert_amp_bind_attributes( $html ) {
		$amp_bind_attr_prefix = self::get_amp_bind_placeholder_prefix();

		// Pattern for HTML attribute accounting for binding attr name, boolean attribute, single/double-quoted attribute value, and unquoted attribute values.
		$attr_regex = '#^\s+(?P<name>\[?[a-zA-Z0-9_\-]+\]?)(?P<value>=(?:"[^"]*+"|\'[^\']*+\'|[^\'"\s]+))?#';

		/**
		 * Replace callback.
		 *
		 * @param array $tag_matches Tag matches.
		 * @return string Replacement.
		 */
		$replace_callback = function( $tag_matches ) use ( $amp_bind_attr_prefix, $attr_regex ) {
			$old_attrs = rtrim( $tag_matches['attrs'] );
			$new_attrs = '';
			$offset    = 0;
			while ( preg_match( $attr_regex, substr( $old_attrs, $offset ), $attr_matches ) ) {
				$offset += strlen( $attr_matches[0] );

				if ( '[' === $attr_matches['name'][0] ) {
					$new_attrs .= ' ' . $amp_bind_attr_prefix . trim( $attr_matches['name'], '[]' );
					if ( isset( $attr_matches['value'] ) ) {
						$new_attrs .= $attr_matches['value'];
					}
				} else {
					$new_attrs .= $attr_matches[0];
				}
			}

			// Bail on parse error which occurs when the regex isn't able to consume the entire $new_attrs string.
			if ( strlen( $old_attrs ) !== $offset ) {
				return $tag_matches[0];
			}

			return '<' . $tag_matches['name'] . $new_attrs . '>';
		};

		// Match all start tags that contain a binding attribute.
		$pattern   = join(
			'',
			array(
				'#<',
				'(?P<name>[a-zA-Z0-9_\-]+)',               // Tag name.
				'(?P<attrs>\s',                            // Attributes.
				'(?:[^>"\'\[\]]+|"[^"]*+"|\'[^\']*+\')*+', // Non-binding attributes tokens.
				'\[[a-zA-Z0-9_\-]+\]',                     // One binding attribute key.
				'(?:[^>"\']+|"[^"]*+"|\'[^\']*+\')*+',     // Any attribute tokens, including binding ones.
				')>#s',
			)
		);
		$converted = preg_replace_callback(
			$pattern,
			$replace_callback,
			$html
		);

		/**
		 * If the regex engine incurred an error during processing, for example exceeding the backtrack
		 * limit, $converted will be null. In this case we return the originally passed document to allow
		 * DOMDocument to attempt to load it.  If the AMP HTML doesn't make use of amp-bind or similar
		 * attributes, then everything should still work.
		 *
		 * See https://github.com/ampproject/amp-wp/issues/993 for additional context on this issue.
		 * See http://php.net/manual/en/pcre.constants.php for additional info on PCRE errors.
		 */
		return ( ! is_null( $converted ) ) ? $converted : $html;
	}

	/**
	 * Convert AMP bind-attributes back to their original syntax.
	 *
	 * This is a reciprocal function of AMP_DOM_Utils::convert_amp_bind_attributes().
	 *
	 * @since 0.7
	 * @see \AMP_DOM_Utils::convert_amp_bind_attributes()
	 * @link https://www.ampproject.org/docs/reference/components/amp-bind
	 *
	 * @param string $html HTML with amp-bind attributes converted.
	 * @return string HTML with amp-bind attributes restored.
	 */
	public static function restore_amp_bind_attributes( $html ) {
		$html = preg_replace(
			'#\s' . self::get_amp_bind_placeholder_prefix() . '([a-zA-Z0-9_\-]+)#',
			' [$1]',
			$html
		);
		return $html;
	}

	/**
	 * Return a valid DOMDocument representing arbitrary HTML content passed as a parameter.
	 *
	 * @see Reciprocal function get_content_from_dom()
	 *
	 * @since 0.2
	 *
	 * @param string $content Valid HTML content to be represented by a DOMDocument.
	 *
	 * @return DOMDocument|false Returns DOMDocument, or false if conversion failed.
	 */
	public static function get_dom_from_content( $content ) {
		/*
		 * Wrap in dummy tags, since XML needs one parent node.
		 * It also makes it easier to loop through nodes.
		 * We can later use this to extract our nodes.
		 * Add utf-8 charset so loadHTML does not have problems parsing it.
		 * See: http://php.net/manual/en/domdocument.loadhtml.php#78243
		 */
		$document = sprintf(
			'<html><head><meta http-equiv="content-type" content="text/html; charset=%s"></head><body>%s</body></html>',
			get_bloginfo( 'charset' ),
			$content
		);

		return self::get_dom( $document );

	}

	/**
	 * Return valid HTML *body* content extracted from the DOMDocument passed as a parameter.
	 *
	 * @since 0.2
	 * @see AMP_DOM_Utils::get_content_from_dom_node() Reciprocal function.
	 *
	 * @param DOMDocument $dom Represents an HTML document from which to extract HTML content.
	 * @return string Returns the HTML content of the body element represented in the DOMDocument.
	 */
	public static function get_content_from_dom( $dom ) {
		$body = $dom->getElementsByTagName( 'body' )->item( 0 );

		// The DOMDocument may contain no body. In which case return nothing.
		if ( is_null( $body ) ) {
			return '';
		}

		return preg_replace(
			'#^.*?<body.*?>(.*)</body>.*?$#si',
			'$1',
			self::get_content_from_dom_node( $dom, $body )
		);
	}


	/**
	 * Return valid HTML content extracted from the DOMNode passed as a parameter.
	 *
	 * @since 0.6
	 * @see AMP_DOM_Utils::get_dom() Where the operations in this method are mirrored.
	 * @see AMP_DOM_Utils::get_content_from_dom() Reciprocal function.
	 * @todo In the future consider an AMP_DOMDocument subclass that does this automatically at saveHTML(). See <https://github.com/ampproject/amp-wp/pull/895/files#r163825513>.
	 *
	 * @param DOMDocument $dom  Represents an HTML document.
	 * @param DOMElement  $node Represents an HTML element of the $dom from which to extract HTML content.
	 * @return string Returns the HTML content represented in the DOMNode
	 */
	public static function get_content_from_dom_node( $dom, $node ) {
		/**
		 * Self closing tags regex.
		 *
		 * @var string Regular expression to match self-closing tags
		 *      that saveXML() has generated a closing tag for.
		 */
		static $self_closing_tags_regex;

		/*
		 * Cache this regex so we don't have to recreate it every call.
		 */
		if ( ! isset( $self_closing_tags_regex ) ) {
			$self_closing_tags       = implode( '|', self::$self_closing_tags );
			$self_closing_tags_regex = "#</({$self_closing_tags})>#i";
		}

		/*
		 * Prevent amp-mustache syntax from getting URL-encoded in attributes when saveHTML is done.
		 * While this is applying to the entire document, it only really matters inside of <template>
		 * elements, since URL-encoding of curly braces in href attributes would not normally matter.
		 * But when this is done inside of a <template> then it breaks Mustache. Since Mustache
		 * is logic-less and curly braces are not unsafe for HTML, we can do a global replacement.
		 * The replacement is done on the entire HTML document instead of just inside of the <template>
		 * elements since it is faster and wouldn't change the outcome.
		 */
		$mustache_tag_placeholders = self::get_mustache_tag_placeholders();
		$mustache_tags_replaced    = false;
		$xpath                     = new DOMXPath( $dom );
		$templates                 = $dom->getElementsByTagName( 'template' );
		foreach ( $templates as $template ) {

			// These attributes are the only ones that saveHTML() will URL-encode.
			foreach ( $xpath->query( './/*/@src|.//*/@href|.//*/@action', $template ) as $attribute ) {
				$attribute->nodeValue = str_replace(
					array_keys( $mustache_tag_placeholders ),
					array_values( $mustache_tag_placeholders ),
					$attribute->nodeValue,
					$count
				);
				if ( $count ) {
					$mustache_tags_replaced = true;
				}
			}
		}

		if ( version_compare( PHP_VERSION, '7.3', '>=' ) ) {
			$html = $dom->saveHTML( $node );
		} else {
			/*
			 * Temporarily add fragment boundary comments in order to locate the desired node to extract from
			 * the given HTML document. This is required because libxml seems to only preserve whitespace when
			 * serializing when calling DOMDocument::saveHTML() on the entire document. If you pass the element
			 * to DOMDocument::saveHTML() then formatting whitespace gets added unexpectedly. This is seen to
			 * be fixed in PHP 7.3, but for older versions of PHP the following workaround is needed.
			 */

			/*
			 * First make sure meta[charset] gets http-equiv and content attributes to work around issue
			 * with $dom->saveHTML() erroneously encoding UTF-8 as HTML entities.
			 */
			$meta_charset = $xpath->query( '/html/head/meta[ @charset ]' )->item( 0 );
			if ( $meta_charset ) {
				$meta_charset->setAttribute( 'http-equiv', 'Content-Type' );
				$meta_charset->setAttribute( 'content', sprintf( 'text/html; charset=%s', $meta_charset->getAttribute( 'charset' ) ) );
			}

			$boundary       = 'fragment_boundary:' . (string) wp_rand();
			$start_boundary = $boundary . ':start';
			$end_boundary   = $boundary . ':end';
			$comment_start  = $dom->createComment( $start_boundary );
			$comment_end    = $dom->createComment( $end_boundary );
			$node->parentNode->insertBefore( $comment_start, $node );
			$node->parentNode->insertBefore( $comment_end, $node->nextSibling );
			$html = preg_replace(
				'/^.*?' . preg_quote( "<!--$start_boundary-->", '/' ) . '(.*)' . preg_quote( "<!--$end_boundary-->", '/' ) . '.*?\s*$/s',
				'$1',
				$dom->saveHTML()
			);

			// Remove meta[http-equiv] and meta[content] attributes which were added to meta[charset] for HTML serialization.
			if ( $meta_charset ) {
				if ( $dom->documentElement === $node ) {
					$html = preg_replace( '#(<meta\scharset=\S+)[^<]*?>#i', '$1>', $html );
				}

				$meta_charset->removeAttribute( 'http-equiv' );
				$meta_charset->removeAttribute( 'content' );
			}

			$node->parentNode->removeChild( $comment_start );
			$node->parentNode->removeChild( $comment_end );
		}

		// Whitespace just causes unit tests to fail... so whitespace begone.
		if ( '' === trim( $html ) ) {
			return '';
		}

		// Restore amp-mustache placeholders which were replaced to prevent URL-encoded corruption by saveHTML.
		if ( $mustache_tags_replaced ) {
			$html = str_replace(
				array_values( $mustache_tag_placeholders ),
				array_keys( $mustache_tag_placeholders ),
				$html
			);
		}

		// Restore noscript elements which were temporarily removed to prevent libxml<2.8 parsing problems.
		if ( version_compare( LIBXML_DOTTED_VERSION, '2.8', '<' ) ) {
			$html = str_replace(
				array_keys( self::$noscript_placeholder_comments ),
				array_values( self::$noscript_placeholder_comments ),
				$html
			);
		}

		$html = self::restore_amp_bind_attributes( $html );

		/*
		 * Travis w/PHP 7.1 generates <br></br> and <hr></hr> vs. <br/> and <hr/>, respectively.
		 * Travis w/PHP 7.x generates <source ...></source> vs. <source ... />.  Etc.
		 * Seems like LIBXML_NOEMPTYTAG was passed, but as you can see it was not.
		 * This does not happen in my (@mikeschinkel) local testing, btw.
		 */
		$html = preg_replace( $self_closing_tags_regex, '', $html );

		return $html;
	}

	/**
	 * Create a new node w/attributes (a DOMElement) and add to the passed DOMDocument.
	 *
	 * @since 0.2
	 *
	 * @param DOMDocument $dom        A representation of an HTML document to add the new node to.
	 * @param string      $tag        A valid HTML element tag for the element to be added.
	 * @param string[]    $attributes One of more valid attributes for the new node.
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
	 * @param DOMElement $node Represents an HTML element for which to extract attributes.
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
	 * @param DOMElement $node       Represents an HTML element.
	 * @param string[]   $attributes One or more attributes for the node's HTML element.
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
	 * @param DOMElement $node Represents an HTML element.
	 * @return bool Returns true if the DOMElement has no child nodes and
	 *              the textContent property of the DOMElement is empty;
	 *              Otherwise it returns false.
	 */
	public static function is_node_empty( $node ) {
		return false === $node->hasChildNodes() && empty( $node->textContent );
	}

	/**
	 * Forces HTML element closing tags given a DOMDocument and optional DOMElement
	 *
	 * @since 0.2
	 * @deprecated
	 *
	 * @param DOMDocument $dom  Represents HTML document on which to force closing tags.
	 * @param DOMElement  $node Represents HTML element to start closing tags on.
	 *                          If not passed, defaults to first child of body.
	 */
	public static function recursive_force_closing_tags( $dom, $node = null ) {
		_deprecated_function( __METHOD__, '0.7' );

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
	 * @param string $tag Tag.
	 * @return bool Returns true if a valid self-closing tag, false if not.
	 */
	private static function is_self_closing_tag( $tag ) {
		return in_array( strtolower( $tag ), self::$self_closing_tags, true );
	}
}
