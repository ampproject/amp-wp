<?php

require_once( AMP__DIR__ . '/includes/sanitizers/class-amp-base-sanitizer.php' );

/**
 * Strips blacklisted tags and attributes from content.
 *
 * See following for blacklist:
 *     https://github.com/ampproject/amphtml/blob/master/spec/amp-html-format.md#html-tags
 */
class AMP_Blacklist_Sanitizer extends AMP_Base_Sanitizer {
	const PATTERN_REL_WP_ATTACHMENT = '#wp-att-([\d]+)#';

	public function sanitize() {
		$blacklisted_tags = $this->get_blacklisted_tags();
		$blacklisted_attributes = $this->get_blacklisted_attributes();
		$blacklisted_protocols = $this->get_blacklisted_protocols();

		$body = $this->get_body_node();
		$this->strip_tags( $body, $blacklisted_tags );
		$this->strip_attributes_recursive( $body, $blacklisted_attributes, $blacklisted_protocols );
	}

	private function strip_attributes_recursive( $node, $bad_attributes, $bad_protocols ) {
		if ( $node->nodeType !== XML_ELEMENT_NODE ) {
			return;
		}

		if ( $node->hasAttributes() ) {
			$node_name = $node->nodeName;
			$length = $node->attributes->length;
			for ( $i = $length - 1; $i >= 0; $i-- ) {
				$attribute = $node->attributes->item( $i );
				$attribute_name = strtolower( $attribute->name );
				if ( in_array( $attribute_name, $bad_attributes ) ) {
					$node->removeAttribute( $attribute_name );
					continue;
				}

				// on* attributes (like onclick) are a special case
				if ( 0 === stripos( $attribute_name, 'on' ) && $attribute_name != 'on' ) {
					$node->removeAttribute( $attribute_name );
					continue;
				} elseif ( 'href' === $attribute_name ) {
					$protocol = strtok( $attribute->value, ':' );
					if ( in_array( $protocol, $bad_protocols ) ) {
						$node->removeAttribute( $attribute_name );
						continue;
					}
				} elseif ( 'a' === $node_name ) {
					$this->sanitize_a_attribute( $node, $attribute );
				}
			}
		}

		foreach ( $node->childNodes as $child_node ) {
			$this->strip_attributes_recursive( $child_node, $bad_attributes, $bad_protocols );
		}
	}

	private function strip_tags( $node, $tag_names ) {
		foreach ( $tag_names as $tag_name ) {
			$elements = $node->getElementsByTagName( $tag_name );
			$length = $elements->length;
			if ( 0 === $length ) {
				continue;
			}

			for ( $i = $length - 1; $i >= 0; $i-- ) {
				$element = $elements->item( $i );
				$parent_node = $element->parentNode;
				$parent_node->removeChild( $element );

				if ( 'body' !== $parent_node->nodeName && AMP_DOM_Utils::is_node_empty( $parent_node ) ) {
					$parent_node->parentNode->removeChild( $parent_node );
				}
			}
		}
	}

	private function sanitize_a_attribute( $node, $attribute ) {
		$attribute_name = strtolower( $attribute->name );

		if ( 'rel' === $attribute_name ) {
			$old_value = $attribute->value;
			$new_value = trim( preg_replace( self::PATTERN_REL_WP_ATTACHMENT, '', $old_value ) );
			if ( empty( $new_value ) ) {
				$node->removeAttribute( $attribute_name );
			} elseif ( $old_value !== $new_value ) {
				$node->setAttribute( $attribute_name, $new_value );
			}
		} elseif ( 'rev' === $attribute_name ) {
			// rev removed from HTML5 spec, which was used by Jetpack Markdown.
			$node->removeAttribute( $attribute_name );
		} elseif ( 'target' === $attribute_name && '_new' === $attribute->value ) {
			// _new is not allowed; swap with _blank
			$node->setAttribute( $attribute_name, '_blank' );
		}
	}

	private function get_blacklisted_protocols() {
		return array(
			'javascript',
		);
	}

	private function get_blacklisted_tags() {
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
			'label',
			'input',
			'textarea',
			'select',
			'option',
			'link',

			// These are converted into amp-* versions
			//'img',
			//'video',
			//'audio',
			//'iframe',
		);
	}

	private function get_blacklisted_attributes() {
		return array(
			'style',
		);
	}
}
