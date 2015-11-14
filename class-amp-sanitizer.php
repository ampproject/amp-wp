<?php

class AMP_Sanitizer {
	private static $allowed_html;
	private static $allowed_protocols;

	/**
	 * Strips blacklisted tags and attributes from content.
	 *
	 * See following for blacklist:
	 *     https://github.com/ampproject/amphtml/blob/master/spec/amp-html-format.md#html-tags
	 */
	static public function strip( $content ) {
		if ( empty( $content ) ) {
			return $content;
		}

		$blacklisted_tags = self::get_blacklisted_tags();
		$blacklisted_attributes = self::get_blacklisted_attributes();
		$blacklisted_protocols = self::get_blacklisted_protocols();

		$libxml_previous_state = libxml_use_internal_errors( true );
		$dom = new DOMDocument;
		// Wrap in dummy tags, since XML needs one parent node.
		// It also makes it easier to loop through nodes.
		// We can later use this to extract our nodes.
		$result = $dom->loadHTML( '<html><body>' . $content . '</body></html>' );
		libxml_clear_errors();
		libxml_use_internal_errors( $libxml_previous_state );

		if ( ! $result ) {
			return $content;
		}

		$body = $dom->getElementsByTagName( 'body' )->item( 0 );
		self::strip_tags( $body, $blacklisted_tags );
		self::strip_attributes_recursive( $body, $blacklisted_attributes, $blacklisted_protocols );

		// Only want children of the body tag, since we have a subset of HTML.
		$out = '';
		foreach ( $body->childNodes as $node ) {
			$out .= $dom->saveXML( $node, LIBXML_NOEMPTYTAG );
		}
		return $out;
	}

	static private function strip_attributes_recursive( $node, $bad_attributes, $bad_protocols ) {
		if ( $node->nodeType !== XML_ELEMENT_NODE ) {
			return;
		}

		if ( $node->hasAttributes() ) {
			foreach ( $node->attributes as $attribute ) {
				$attribute_name = strtolower( $attribute->name );
				if ( in_array( $attribute_name, $bad_attributes ) ) {
					$node->removeAttribute( $attribute_name );
					continue;
				}

				// on* attributes (like onclick) are a special case
				if ( 0 === stripos( $attribute_name, 'on' ) ) {
					$node->removeAttribute( $attribute_name );
					continue;
				}

				if ( 'href' === $attribute_name ) {
					$protocol = strtok( $attribute->value, ':' );
					if ( in_array( $protocol, $bad_protocols ) ) {
						$node->removeAttribute( $attribute_name );
						continue;
					}
				}
			}
		}

		foreach ( $node->childNodes as $child_node ) {
			self::strip_attributes_recursive( $child_node, $bad_attributes, $bad_protocols );
		}
	}

	static private function strip_tags( $node, $tags ) {
		foreach ( $tags as $tag_name ) {
			$elements = $node->getElementsByTagName( $tag_name );
			if ( $elements->length ) {
				foreach ( $elements as $element ) {
					$element->parentNode->removeChild( $element );
				}
			}
		}
	}

	static private function get_blacklisted_protocols() {
		return array(
			'javascript',
		);
	}

	static private function get_blacklisted_tags() {
		return array(
			'script',
			'noscript',
			'style',
			'frame',
			'frameset',
			'object',
			'param',
			'applet',
			'form',
			'input',
			'button',
			'textarea',
			'select',
			'option',
			'link',
			'meta',

			// These are converted into amp-* versions
			//'img',
			//'video',
			//'audio',
			//'iframe',
		);
	}

	static private function get_blacklisted_attributes() {
		return array(
			'style',
		);
	}
}
