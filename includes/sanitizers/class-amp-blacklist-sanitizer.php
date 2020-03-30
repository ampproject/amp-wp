<?php
/**
 * Class AMP_Blacklist_Sanitizer
 *
 * @package AMP
 */

/**
 * Strips blacklisted tags and attributes from content.
 *
 * See following for blacklist:
 *     https://github.com/ampproject/amphtml/blob/master/spec/amp-html-format.md#html-tags
 *
 * @since 0.5 This has been replaced by AMP_Tag_And_Attribute_Sanitizer but is kept around for back-compat.
 * @codeCoverageIgnore
 * @deprecated
 */
class AMP_Blacklist_Sanitizer extends AMP_Base_Sanitizer {
	const PATTERN_REL_WP_ATTACHMENT = '#wp-att-([\d]+)#';

	/**
	 * Default args.
	 *
	 * @var array
	 */
	protected $DEFAULT_ARGS = [
		'add_blacklisted_protocols'  => [],
		'add_blacklisted_tags'       => [],
		'add_blacklisted_attributes' => [],
	];

	/**
	 * Sanitize.
	 */
	public function sanitize() {
		_deprecated_function( __METHOD__, '0.7', 'AMP_Tag_And_Attribute_Sanitizer::sanitize' );

		$blacklisted_tags       = $this->get_blacklisted_tags();
		$blacklisted_attributes = $this->get_blacklisted_attributes();
		$blacklisted_protocols  = $this->get_blacklisted_protocols();

		$body = $this->root_element;
		$this->strip_tags( $body, $blacklisted_tags );
		$this->strip_attributes_recursive( $body, $blacklisted_attributes, $blacklisted_protocols );
	}

	/**
	 * Strip attributes recursively.
	 *
	 * @param DOMNode $node DOM Node.
	 * @param array   $bad_attributes Bad attributes.
	 * @param array   $bad_protocols  Bad protocols.
	 */
	private function strip_attributes_recursive( $node, $bad_attributes, $bad_protocols ) {
		if ( XML_ELEMENT_NODE !== $node->nodeType ) {
			return;
		}

		$node_name = $node->nodeName;

		// Some nodes may contain valid content but are themselves invalid.
		// Remove the node but preserve the children.
		if ( 'font' === $node_name ) {
			$this->replace_node_with_children( $node, $bad_attributes, $bad_protocols );
			return;
		}

		if ( 'a' === $node_name && false === $this->validate_a_node( $node ) ) {
			$this->replace_node_with_children( $node, $bad_attributes, $bad_protocols );
			return;
		}

		if ( $node->hasAttributes() ) {
			$length = $node->attributes->length;
			for ( $i = $length - 1; $i >= 0; $i-- ) {
				/**
				 * Attribute node to check.
				 *
				 * @var DOMAttr $attribute
				 */
				$attribute      = $node->attributes->item( $i );
				$attribute_name = strtolower( $attribute->name );
				if ( in_array( $attribute_name, $bad_attributes, true ) ) {
					$this->remove_invalid_attribute( $node, $attribute_name );
					continue;
				}

				// The on* attributes (like onclick) are a special case.
				if ( 0 === stripos( $attribute_name, 'on' ) && 'on' !== $attribute_name ) {
					$this->remove_invalid_attribute( $node, $attribute_name );
					continue;
				}

				if ( 'a' === $node_name ) {
					$this->sanitize_a_attribute( $node, $attribute );
				}
			}
		}

		$length = $node->childNodes->length;
		for ( $i = $length - 1; $i >= 0; $i-- ) {
			$child_node = $node->childNodes->item( $i );

			$this->strip_attributes_recursive( $child_node, $bad_attributes, $bad_protocols );
		}
	}

	/**
	 * Strip tags.
	 *
	 * @param DOMElement $node      Node.
	 * @param string[]   $tag_names Tag names.
	 */
	private function strip_tags( $node, $tag_names ) {
		foreach ( $tag_names as $tag_name ) {
			$elements = $node->getElementsByTagName( $tag_name );
			$length   = $elements->length;
			if ( 0 === $length ) {
				continue;
			}

			for ( $i = $length - 1; $i >= 0; $i-- ) {
				$element     = $elements->item( $i );
				$parent_node = $element->parentNode;
				$this->remove_invalid_child( $element );

				if ( 'body' !== $parent_node->nodeName && AMP_DOM_Utils::is_node_empty( $parent_node ) ) {
					$this->remove_invalid_child( $parent_node );
				}
			}
		}
	}

	/**
	 * Sanitize attribute.
	 *
	 * @param DOMElement $node      Node.
	 * @param DOMAttr    $attribute Attribute.
	 */
	private function sanitize_a_attribute( $node, $attribute ) {
		$attribute_name = strtolower( $attribute->name );

		if ( 'rel' === $attribute_name ) {
			$old_value = $attribute->value;
			$new_value = trim( preg_replace( self::PATTERN_REL_WP_ATTACHMENT, '', $old_value ) );
			if ( empty( $new_value ) ) {
				$this->remove_invalid_attribute( $node, $attribute_name );
			} elseif ( $old_value !== $new_value ) {
				$node->setAttribute( $attribute_name, $new_value );
			}
		} elseif ( 'rev' === $attribute_name ) {
			// rev removed from HTML5 spec, which was used by Jetpack Markdown.
			$this->remove_invalid_attribute( $node, $attribute_name );
		} elseif ( 'target' === $attribute_name ) {
			// _blank is the only allowed value and it must be lowercase.
			// replace _new with _blank and others should simply be removed.
			$old_value = strtolower( $attribute->value );
			if ( '_blank' === $old_value || '_new' === $old_value ) {
				// _new is not allowed; swap with _blank
				$node->setAttribute( $attribute_name, '_blank' );
			} else {
				// Only _blank is allowed.
				$this->remove_invalid_attribute( $node, $attribute_name );
			}
		}
	}

	/**
	 * Validate node.
	 *
	 * @param DOMElement $node Node.
	 * @return bool
	 */
	private function validate_a_node( $node ) {
		// Get the href attribute.
		$href = $node->getAttribute( 'href' );

		if ( empty( $href ) ) {
			/*
			 * If no href, check that a is an anchor or not.
			 * We don't need to validate anchors any further.
			 */
			return $node->hasAttribute( 'name' ) || $node->hasAttribute( 'id' );
		}

		// If this is an anchor link, just return true.
		if ( 0 === strpos( $href, '#' ) ) {
			return true;
		}

		// If the href starts with a '/', append the home_url to it for validation purposes.
		if ( 0 === strpos( $href, '/' ) ) {
			$href = untrailingslashit( get_home_url() ) . $href;
		}

		$valid_protocols   = [ 'http', 'https', 'mailto', 'sms', 'tel', 'viber', 'whatsapp' ];
		$special_protocols = [ 'tel', 'sms' ]; // These ones don't valid with `filter_var+FILTER_VALIDATE_URL`.
		$protocol          = strtok( $href, ':' );

		if ( false === filter_var( $href, FILTER_VALIDATE_URL )
			&& ! in_array( $protocol, $special_protocols, true ) ) {
			return false;
		}

		if ( ! in_array( $protocol, $valid_protocols, true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Replace node with children.
	 *
	 * @param DOMElement $node           Node.
	 * @param array      $bad_attributes Bad attributes.
	 * @param array      $bad_protocols  Bad protocols.
	 */
	private function replace_node_with_children( $node, $bad_attributes, $bad_protocols ) {
		// If the node has children and also has a parent node,
		// clone and re-add all the children just before current node.
		if ( $node->hasChildNodes() && $node->parentNode ) {
			foreach ( $node->childNodes as $child_node ) {
				$new_child = $child_node->cloneNode( true );
				$this->strip_attributes_recursive( $new_child, $bad_attributes, $bad_protocols );
				$node->parentNode->insertBefore( $new_child, $node );
			}
		}

		// Remove the node from the parent, if defined.
		if ( $node->parentNode ) {
			$this->remove_invalid_child( $node );
		}
	}

	/**
	 * Merge defaults with args.
	 *
	 * @param string $key    Key.
	 * @param array  $values Values.
	 * @return array Merged args.
	 */
	private function merge_defaults_with_args( $key, $values ) {
		// Merge default values with user specified args.
		if ( ! empty( $this->args[ $key ] )
			&& is_array( $this->args[ $key ] ) ) {
			$values = array_merge( $values, $this->args[ $key ] );
		}

		return $values;
	}

	/**
	 * Get blacklisted protocols.
	 *
	 * @return array Protocols.
	 */
	private function get_blacklisted_protocols() {
		return $this->merge_defaults_with_args(
			'add_blacklisted_protocols',
			[
				'javascript',
			]
		);
	}

	/**
	 * Get blacklisted tags.
	 *
	 * @return array Tags.
	 */
	private function get_blacklisted_tags() {
		return $this->merge_defaults_with_args(
			'add_blacklisted_tags',
			[
				'script',
				'noscript',
				'style',
				'frame',
				'frameset',
				'object',
				'param',
				'applet',
				'form',
				'label',
				'input',
				'textarea',
				'select',
				'option',
				'link',
				'picture',

				// Sanitizers run after embed handlers, so if anything wasn't matched, it needs to be removed.
				'embed',
				'embedvideo',

				// Other weird ones.
				'comments-count',
			]
		);
	}

	/**
	 * Get blacklisted attributes.
	 *
	 * @return array Attributes.
	 */
	private function get_blacklisted_attributes() {
		return $this->merge_defaults_with_args(
			'add_blacklisted_attributes',
			[
				'style',
				'size',
				'clear',
				'align',
				'valign',
			]
		);
	}
}
