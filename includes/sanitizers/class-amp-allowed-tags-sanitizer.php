<?php

require_once( AMP__DIR__ . '/includes/sanitizers/class-amp-base-sanitizer.php' );
require_once( AMP__DIR__ . '/includes/sanitizers/class-amp-allowed-tags-generated.php' );

/**
 * Strips tags and attributes not allowed by the AMP sped from the content.
 *
 * Allowed tags array is generated from this protocol buffer:
 *     https://github.com/ampproject/amphtml/blob/master/validator/validator-main.protoascii
 */
class AMP_Allowed_Tags_Sanitizer extends AMP_Base_Sanitizer {

	protected $allowed_tags;
	protected $globally_allowed_attrs;
	private $stack = array();

	public function sanitize() {
		// Get whitelists.
		$this->allowed_tags = apply_filters( 'amp_allowed_tags', AMP_Allowed_Tags_Generated::get_allowed_tags() );
		$this->globally_allowed_attributes = apply_filters( 'amp_globally_allowed_attributes', AMP_Allowed_Tags_Generated::get_allowed_attributes() );

		// Add root of content to the stack
		$body = $this->get_body_node();
		$this->stack[] = $body;

		// This loop iterates through the DOM tree iteratively.
		while ( 0 < count( $this->stack ) ) {

			// Get the next node to process.
			$node = array_pop( $this->stack );

			// Validate this node.
			$this->validate_node( $node );

			// Push child nodes onto the stack, if any exist.
			// Note: if the node was removed, then it's parentNode value is null.
			if ( $node->parentNode ) {
				$child = $node->firstChild;
				while ( $child ) {
					$this->stack[] = $child;
					$child = $child->nextSibling;
				}
			}
		}
	}

	private function validate_node( $node ) {
		// Don't process text nodes
		if ( XML_TEXT_NODE == $node->nodeType ) {
			return;
		}

		// 1) Remove nodes with non-whitelisted tags.
		if ( ! $this->is_amp_allowed_tag( $node ) ) {
			// If it's not an allowed tag, replace the node with it's children
			$this->replace_node_with_children( $node );
			// Return early since this node no longer exists.
			return;
		}

		// 2) Compile a list of attr_specs to validate for this node based on
		//		tag name of the node.
		$attr_spec_list_to_validate = array();
		foreach ( $this->allowed_tags[ $node->nodeName ] as $rule_spec ) {
			if ( $this->tag_spec_is_valid_for_node( $node, $rule_spec[AMP_Rule_Spec::tag_spec] ) ) {
				$attr_spec_list_to_validate[] = $rule_spec[AMP_Rule_Spec::attr_spec_list];		
			}
		}

		// 3) If no valid attr_specs exist, then remove this node and return.
		if ( empty( $attr_spec_list_to_validate ) ) {
			$node->parentNode->removeChild( $node );
			return;
		}

		// 4) Validate remaining attr_specs based on the node's attributes
		//		and their values.
		foreach ( $attr_spec_list_to_validate as $attr_spec_id => $attr_spec ) {
			
			// If we can't validate this attr_spec, remove it from the list.
			if ( false == $this->attr_spec_is_valid_for_node( $node, $attr_spec ) ) {
				unset( $attr_spec_list_to_validate );
			}
		}

		// 5) If no valid attr_specs exist, remove or replace the node.
		if ( empty( $attr_spec_list_to_validate ) ) {

			if ( in_array( $node->nodeName, AMP_Rule_Spec::node_types_to_remove_if_invalid ) ) {
				$node->parentNode->removeChild( $node );
			} else {
				$this->replace_node_with_children( $node );
			}
			return;
		}

		// 6) Remove any remaining disallowed attributes.

		// 7) Remove any blacklisted attribute values.



		// TODO: Need to handle the hypothetical case where we have multiple 
		//	attr_spec lists to validate.
	}

	// private function node_is_valid_for_attr_spec( $node, $attr_name, $attr_node ) {

	// 	foreach ( $this->
	// }

	/**
	 * Rules in a tag_spec are essentially restrictions. So, if a rule
	 *	doesn't exist, then that means there is no restriction ans we can 
	 *	safely skip that test.
	 */
	private function tag_spec_is_valid_for_node( $node, $tag_spec ) {

		if ( ! empty( $tag_spec[AMP_Rule_Spec::mandatory_parent] ) ) {
			if ( ! $this->has_parent( $node, $tag_spec[AMP_Rule_Spec::mandatory_parent] ) ) {
				return false;
			}
		}

		if ( ! empty( $tag_spec[AMP_Rule_Spec::disallowed_ancestor] ) ) {
			foreach ( $tag_spec[AMP_Rule_Spec::disallowed_ancestor] as $disallowed_ancestor_node_name ) {
				if ( $this->has_ancestor( $node, $disallowed_ancestor_node_name ) ) {
					return false;
				}
			}
		}

		if ( ! empty( $tag_spec[AMP_Rule_Spec::mandatory_ancestor] ) ) {
			if ( ! $this->has_ancestor( $node, $tag_spec[AMP_Rule_Spec::mandatory_ancestor] ) ) {
				return false;
			}
		}

		return true;
	}

	private function attr_spec_is_valid_for_node( $node, $attr_spec_list ) {
		foreach ( $attr_spec_list as $attr_name => $attr_spec_rule ) {

			// 1) If a mandatory attribute doesn't exist, fail validation
			if ( isset( $attr_spec_rule[AMP_Rule_Spec::mandatory] ) &&
				$attr_spec_rule[AMP_Rule_Spec::mandatory] ) {
				if ( ! $node->hasAttribute( $attr_name ) ) {
					// check if an alternative name list is specified
					if ( isset( $attr_spec_rule[AMP_Rule_Spec::alternative_names] ) ) {
						$found = false;
						foreach ( $attr_spec_rule[AMP_Rule_Spec::alternative_names] as $alt_name ) {
							if ( $node->hasAttribute( $alt_name ) ) {
								$found = true;
							}
						}
						if ( ! $found ) {
							// Neither the specified attribute or an alternate 
							//	was found. Validation failed.
							return false;
						}
					} else {
						// if no alternate names exist, fail
						return false;
					}
				}
			}

			// 2) If a property exists, but doesn't have a required value, fail validation.
			// check 'value' - case sensitive
			if ( isset( $attr_spec_rule[AMP_Rule_Spec::value] ) && $node->hasAttribute( $attr_name ) ) {
				if ( ! ( $node->getAttribute( $attr_name ) == $attr_spec_rule[AMP_Rule_Spec::value] ) ) {
					return false;
				}
			}

			// check 'value_casei' - case insensitive
			if ( isset( $attr_spec_rule[AMP_Rule_Spec::value_casei] ) && $node->hasAttribute( $attr_name ) ) {
				$attr_value = strtolower( $node->getAttribute( $attr_name ) );
				$rule_value = strtolower( $attr_spec_rule[AMP_Rule_Spec::value_casei] );
				if ( ! ( $attr_value == $rule_value ) ) {
					return false;
				}
			}

			// check 'value_regex' - case sensitive regex match
			if ( isset( $attr_spec_rule[AMP_Rule_Spec::value_regex] ) && $node->hasAttribute( $attr_name ) ) {
				$attr_value = $node->getAttribute( $attr_name );
				$rule_value = $attr_spec_rule[AMP_Rule_Spec::value_regex];

				if ( ! preg_match('/^' . $rule_value . '$/u', $attr_value) ) {
					return false;
				}
			}

			// check 'value_regex_casei' - case insensitive regex match
			if ( isset( $attr_spec_rule[AMP_Rule_Spec::value_regex_casei] ) && $node->hasAttribute( $attr_name ) ) {
				$attr_value = $node->getAttribute( $attr_name );
				$rule_value = $attr_spec_rule[AMP_Rule_Spec::value_regex_casei];
				if ( ! preg_match('/^' . $rule_value . '$/ui', $attr_value) ) {
					return false;
				}
			}

			// 3) If property has protocol, but protocol is not on allowed list, fail.
			if ( isset( $attr_spec_rule[AMP_Rule_Spec::allowed_protocol] )  && $node->hasAttribute( $attr_name ) ) {
				$attr_value = $node->getAttribute( $attr_name );
				// This seems to be an acceptable check since the AMP validator
				//	will allow a URL with no protocol to pass validation.
				if ( $url_scheme = parse_url( $attr_value, PHP_URL_SCHEME ) ) {
					$found = false;
					foreach ( $attr_spec_rule[AMP_Rule_Spec::allowed_protocol] as $allowed_protocol ) {
						if ( strtolower( $url_scheme ) == strtolower( $allowed_protocol ) ) {
							// found an acceptable protocol
							$found = true;
							break;
						}
					}
					if ( ! $found ) {
						// if we're here, then there was a protocol specified,
						//	but it wasn't allowed. Fail vaildation.
						return false;
					}
				}
			}

			// 4) If property doesn't have protocol and relative is not allowed, fail.

			// 5) If property is empty, but empty is not allowed, fail.

			// 6) If property exists, but is disallowed domain, fail.
		}

		return true;
	}

	private function process_attr_spec_rules( $attr_spec_rule, $attr_spec_rule_value, $attr_node ) {
		switch ( $attr_spec_rule ) {
			case AMP_Rule_Spec::blacklisted_value_regex:
				return $this->enforce_attr_spec_rule_blacklisted_value_regex( $attr_spec_rule_value, $attr_node );
				break;

			// case AMP_Rule_Spec::mandatory:
			// 	return $this->enforce_attr_spec_rule_mandatory( $attr_spec_rule_value, $attr_node );
			// 	break;

			case AMP_Rule_Spec::value_regex:
				return $this->enforce_attr_spec_rule_value_regex( $attr_spec_rule_value, $attr_node );
				break;

			// TODO: add the rest of the property checks
			
			default:
				break;
		}
	}

	/**
	 * property must *not* match regex
	 */
	private function enforce_attr_spec_rule_blacklisted_value_regex( $attr_spec_rule_value, $attr_node ) {
		$pattern = '/' . $attr_spec_rule_value . '/u';
		if ( preg_match( $pattern, $attr_node->value ) ) {
			$attr_node->value = '';
		}
	}

	/**
	 * property *must* match regex
	 */
	private function enforce_attr_spec_rule_value_regex( $attr_spec_rule_value, $attr_node ) {
		$pattern = '/' . $attr_spec_rule_value . '/u';
		$x = preg_match( $pattern, $attr_node->value );
		if ( 0 == preg_match( $pattern, $attr_node->value ) ) {
			$attr_node->value = '';
		}
	}

	private function is_amp_allowed_attribute( $attr_name, $attr_spec_list ) {
		return ( isset( $this->globally_allowed_attributes[ $attr_name ] ) || isset( $attr_spec_list[ $attr_name ] ) );
	}

	private function is_amp_allowed_tag( $node ) {
		// Return true if node is on the allowed tags list or if it is a text node.
		return ( isset( $this->allowed_tags[ $node->nodeName ] ) || ( $node->nodeType == XML_TEXT_NODE ) );
	}

	/**
	 * Below are some utility functions to search and manipulate the DOM.
	 */

	private function has_parent( $node, $parent_tag_name ) {
		if ( $node && $node->parentNode && ( $node->parentNode->nodeName == $parent_tag_name ) ) {
			return true;
		}

		return false;
	}

	private function has_ancestor( $node, $ancestor_tag_name ) {
		if ( $this->get_ancestor_with_tag_name( $node, $ancestor_tag_name ) ) {
			return true;
		}

		return false;
	}

	private function get_ancestor_with_tag_name( $node, $ancestor_tag_name ) {
		while ( $node && $node = $node->parentNode ) {
			if ( $node->nodeName == $ancestor_tag_name ) {
				return $node;
			}
		}
		return null;
	}

	private function replace_node_with_children( $node ) {
		// If node has children, replace it with them and push children onto stack
		if ( $node->hasChildNodes() && $node->parentNode ) {

			// create a DOM fragment to hold the children
			$fragment = $this->dom->createDocumentFragment();

			// Add all children to fragment/stack
			$child = $node->firstChild;
			while( $child ) {
				$fragment->appendChild( $child );
				$this->stack[] = $child;
				$child = $node->firstChild;
			}

			// replace node with fragment
			$node->parentNode->replaceChild( $fragment, $node );

		// If node has no children, just remove the node.
		} else {
			$node->parentNode->removeChild( $node );
		}
	}
}

abstract class AMP_Rule_Spec {

	const tag_spec = 'tag_spec';
	const attr_spec_list = 'attr_spec_list';
	const rule_spec = 'rule_spec';
	const status_list = 'status_list';
	const status_check = 'status_check';

	// tag rules
	const mandatory_parent = 'mandatory_parent';
	const disallowed_ancestor = 'disallowed_ancestor';
	const mandatory_ancestor = 'mandatory_ancestor';

	// attr rules
	const alternative_names = 'alternative_names';
	const allowed_protocol = 'allowed_protocol';
	const allow_relative = 'allow_relative';
	const allow_empty = 'allow_empty';
	const disallowed_domain = 'disallowed_domain';
	const blacklisted_value_regex = 'blacklisted_value_regex';
	const mandatory = 'mandatory';
	const value = 'value';
	const value_casei = 'value_casei';
	const value_regex = 'value_regex';
	const value_regex_casei = 'value_regex_casei';

	const node_types_to_remove_if_invalid = array (
		'form',
		'script',
		'input',
		'style',
		'link',
		'meta',
	);
}
