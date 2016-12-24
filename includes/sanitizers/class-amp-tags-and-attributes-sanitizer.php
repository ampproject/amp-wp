<?php

require_once( AMP__DIR__ . '/includes/sanitizers/class-amp-base-sanitizer.php' );
require_once( AMP__DIR__ . '/includes/sanitizers/class-amp-allowed-tags-generated.php' );

/**
 * Strips tags and attributes not allowed by the AMP sped from the content.
 *
 * Allowed tags array is generated from this protocol buffer:
 *     https://github.com/ampproject/amphtml/blob/master/validator/validator-main.protoascii
 */
class AMP_Tags_And_Attributes_Sanitizer extends AMP_Base_Sanitizer {

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

		// This loop traverses through the DOM tree iteratively.
		while ( 0 < count( $this->stack ) ) {

			// Get the next node to process.
			$node = array_pop( $this->stack );

			// Process this node.
			$this->process_node( $node );

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

	private function process_node( $node ) {
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
		$rule_spec_list_to_validate = array();
		foreach ( $this->allowed_tags[ $node->nodeName ] as $id => $rule_spec ) {
			if ( $this->tag_spec_is_valid_for_node( $node, $rule_spec[AMP_Rule_Spec::tag_spec] ) ) {
				$rule_spec_list_to_validate[ $id ] = $rule_spec;
			}
		}

		// 3) If no valid attr_specs exist, then remove this node and return.
		if ( empty( $rule_spec_list_to_validate ) ) {
			$this->remove_node( $node );
			return;
		}

		// The remaining validations and filters all have to do with attributes.
		if ( $node->hasAttributes() ) {

			// 4) If there is more than one valid rule_spec for this node, then
			//	try to deduce which one it is by inspecting the attributes.
			if ( 1 < count( $rule_spec_list_to_validate ) ) {
				foreach ( $rule_spec_list_to_validate as $spec_id => $rule_spec ) {
					// If we can't validate this attr_spec, remove it from the list.
					if ( false == $this->attr_spec_is_valid_for_node( $node, $rule_spec[AMP_Rule_Spec::attr_spec_list] ) ) {
						unset( $rule_spec_list_to_validate[ $spec_id ] );
					}
				}
			}

			// // 5) If no valid attr_specs exist, remove or replace the node.
			// if ( empty( $rule_spec_list_to_validate ) ) {
			// 	if ( in_array( $node->nodeName, AMP_Rule_Spec::node_types_to_remove_if_invalid ) ) {
			// 		$this->remove_node( $node );
			// 	} else {
			// 		$this->replace_node_with_children( $node );
			// 	}
			// 	return;
			// }

			// If this node doesn't conform to any of the attr_spec lists, then
			// there's nothing else we can do,
			if ( ! empty( $rule_spec_list_to_validate ) ) {

				// Hopefully, we've narrowed this down to a single attr_spec_list.
				// If not, merge them and cross your fingers.
				$rule_spec_list_merged = array_pop( $rule_spec_list_to_validate );
				if ( ! empty( $rule_spec_list_to_validate ) ) {
					foreach( $rule_spec_list_to_validate as $spec_id => $rule_spec ) {
						$rule_spec_list_merged = array_merge( $rule_spec_list_merged, $rule_spec_list_to_validate[ $spec_id ] );
					}
				}

				// 5) Remove any remaining disallowed attributes.
				$this->remove_disallowed_attributes_from_node( $node, $rule_spec_list_merged[AMP_Rule_Spec::attr_spec_list] );

				// 6) Remove values that don't conform to the attr_spec.
				$this->remove_disallowed_attribute_values_from_node( $node, $rule_spec_list_merged[AMP_Rule_Spec::attr_spec_list] );
			}
		}
	}

	/**
	 * Checks to see if a node's placement with the DOM seems to be valid for 
	 * the given tag_spec. If there are restrictions placed on the type of node 
	 * that can be an immediate parent or an ancestor of this node, then make 
	 * sure those restrictions are met.
	 *
	 * If any of the tests on the restrictions fail, return false, otherwise
	 *	return true.
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

	/**
	 * Checks to see if a spec is potentially valid for the given node based on
	 * 	the attributes present in the node.
	 *
	 * Note: This is a very expensive function. Use it sparingly.
	 */
	private function attr_spec_is_valid_for_node( $node, $attr_spec_list ) {
		// If this node doesn't have any attributes, there is no point in
		//	continuing.
		if ( ! $node->hasAttributes() ) {
			return true;
		}

		// Iterate through each attribute rule in this attr spec list and run
		//	the series of tests 
		foreach ( $attr_spec_list as $attr_name => $attr_spec_rule ) {

			// 1) If a mandatory attribute is required, but doesn't exist, fail 
			//	validation.
			if ( isset( $attr_spec_rule[AMP_Rule_Spec::mandatory] ) ) {
				if ( AMP_Rule_Spec::fail == $this->mandatory_attr_test( $node, $attr_name, $attr_spec_rule ) ) {
					return false;
				}
			}

			// 2) Check to make sure that this attribute meets any value
			//	restrictions. If a property exists, but doesn't have a required
			//	value, fail validation. Otherwise move on to the next test.

			// 2a) check 'value' - case sensitive
			if ( isset( $attr_spec_rule[AMP_Rule_Spec::value] ) ) {
				if ( AMP_Rule_Spec::fail == $this->attr_spec_value_test( $node, $attr_name, $attr_spec_rule ) ) {
					return false;
				}
			}

			// 2b) check 'value_regex' - case sensitive regex match
			if ( isset( $attr_spec_rule[AMP_Rule_Spec::value_regex] ) ) {
				if ( AMP_Rule_Spec::fail == $this->attr_spec_value_regex_test( $node, $attr_name, $attr_spec_rule ) ) {
					return false;
				}
			}

			// 2c) check 'value_casei' - case insensitive
			if ( isset( $attr_spec_rule[AMP_Rule_Spec::value_casei] ) ) {
				if ( AMP_Rule_Spec::fail == $this->attr_spec_value_casei_test( $node, $attr_name, $attr_spec_rule ) ) {
					return false;
				}
			}

			// 2d) check 'value_regex_casei' - case insensitive regex match
			if ( isset( $attr_spec_rule[AMP_Rule_Spec::value_regex_casei] ) ) {
				if ( AMP_Rule_Spec::fail == $this->attr_spec_value_regex_casei_test( $node, $attr_name, $attr_spec_rule ) ) {
					return false;
				}
			}

			// 3) If property has a protocol, but protocol is not on allowed list, fail.
			if ( isset( $attr_spec_rule[AMP_Rule_Spec::allowed_protocol] ) ) {
				if ( AMP_Rule_Spec::fail == $this->attr_spec_allowed_protocol_test( $node, $attr_name, $attr_spec_rule ) ) {
					return false;
				}
			}

			// 4) If allow_relative is specified as false and url is relative, then fail.
			if ( isset( $attr_spec_rule[AMP_Rule_Spec::allow_relative] ) ) {
				if ( AMP_Rule_Spec::fail == $this->attr_spec_disallowed_relative_protocol_test( $node, $attr_name, $attr_spec_rule ) ) {
					return false;
				}
			}

			// 5) If attribute exists and is empty, but empty is not allowed, fail.
			if ( isset( $attr_spec_rule[AMP_Rule_Spec::allow_empty] ) ) {
				if ( AMP_Rule_Spec::fail == $this->attr_spec_disallowed_empty_test( $node, $attr_name, $attr_spec_rule ) ) {
					return false;
				}
			}

			// 6) If attribute exists, but has a disallowed domain, fail.
			if ( isset( $attr_spec_rule[AMP_Rule_Spec::disallowed_domain] ) ) {
				if ( AMP_Rule_Spec::fail == $this->attr_spec_disallowed_domain_test( $node, $attr_name, $attr_spec_rule ) ) {
					return false;
				}
			}

			// 7) If attribute exists, and has a blacklisted value, fail.
			if ( isset( $attr_spec_rule[AMP_Rule_Spec::blacklisted_value_regex] ) ) {
				if ( AMP_Rule_Spec::fail == $this->attr_spec_blacklisted_attribute_values_test( $node, $attr_name, $attr_spec_rule ) ) {
					return false;
				}
			}
		}

		// If we made it here, then all validation checks passed. Whew!
		return true;
	}

	/**
	 * If an attribute is not listed in $allowed_attrs, then it will be removed 
	 * from $node.
	 */
	private function remove_disallowed_attributes_from_node( $node, $attr_spec_list ) {
		// Note: We can't remove the attributes inside the 'foreach' loop
		//	because that breaks the process of iterating through the attrs. So,
		//	we keep track of what needs to be removed in the first loop, then
		//	actually remove the attributes in the second loop.
		$attrs_to_remove = array();
		foreach ( $node->attributes as $attr_name => $attr_node ) {
			// see if this attribute is allowed for this node
			if ( ! $this->is_amp_allowed_attribute( $attr_name, $attr_spec_list ) ) {
				$attrs_to_remove[] = $attr_name;
			}
		}

		if ( ! empty( $attrs_to_remove ) ) {
			// Make sure we're not removing an attribtue that is listed as an alternative
			//	for some other allowed attribtue. ex. 'srcset' is an alternative for 'src'.
			foreach ( $attr_spec_list as $attr_name => $attr_spec_rule_value ) {
				if ( isset( $attr_spec_rule_value[AMP_Rule_Spec::alternative_names] ) ) {
					foreach ( $attr_spec_rule_value[AMP_Rule_Spec::alternative_names] as $alternative_name ) {
						$alt_name_keys = array_keys( $attrs_to_remove, $alternative_name, true );
						if ( ! empty( $alt_name_keys ) ) {
							unset( $attrs_to_remove[ $alt_name_keys[0] ] );
						}
					}
				}
			}

			// Remove the disllowed attributes
			foreach ( $attrs_to_remove as $attr_name ) {
				$node->removeAttribute( $attr_name );
			}
		}
	}

	/**
	 * If a node has an attribute value that is disallowed, then that value will
	 * be removed from the attribute on this node.
	 */
	private function remove_disallowed_attribute_values_from_node( $node, $attr_spec_list ) {
		$this->_remove_disallowed_attribute_values_from_node( $node, $attr_spec_list );
		$this->_remove_disallowed_attribute_values_from_node( $node, $this->globally_allowed_attributes );
	}
	private function _remove_disallowed_attribute_values_from_node( $node, $attr_spec_list ) {
		$attrs_to_remove = array();
		foreach( $node->attributes as $attr_name => $attr_node ) {

			if ( isset( $attr_spec_list[$attr_name] ) ) {
				$attr_spec_rule = $attr_spec_list[$attr_name];
				$remove_value = false;

				if ( isset( $attr_spec_rule[AMP_Rule_Spec::value] ) ) {
					if ( AMP_Rule_Spec::fail == $this->attr_spec_value_test( $node, $attr_name, $attr_spec_list[ $attr_name ] ) ) {
						if ( $this->is_empty_attribute_allowed( $node, $attr_name, $attr_spec_rule ) ) {
							$attr_node->value = '';
						} else {
							$attrs_to_remove[] = $attr_name;
						}
						continue;
					}
				}

				if ( isset( $attr_spec_rule[AMP_Rule_Spec::value_casei] ) ) {
					if ( AMP_Rule_Spec::fail == $this->attr_spec_value_casei_test( $node, $attr_name, $attr_spec_list[ $attr_name ] ) ) {
						if ( $this->is_empty_attribute_allowed( $node, $attr_name, $attr_spec_rule ) ) {
							$attr_node->value = '';
						} else {
							$attrs_to_remove[] = $attr_name;
						}
						continue;
					}
				}

				if ( isset( $attr_spec_rule[AMP_Rule_Spec::value_regex] ) ) {
					if ( AMP_Rule_Spec::fail == $this->attr_spec_value_regex_test( $node, $attr_name, $attr_spec_list[ $attr_name ] ) ) {
						if ( $this->is_empty_attribute_allowed( $node, $attr_name, $attr_spec_rule ) ) {
							$attr_node->value = '';
						} else {
							$attrs_to_remove[] = $attr_name;
						}
						continue;
					}
				}

				if ( isset( $attr_spec_rule[AMP_Rule_Spec::value_regex_casei] ) ) {
					if ( AMP_Rule_Spec::fail == $this->attr_spec_value_regex_casei_test( $node, $attr_name, $attr_spec_list[ $attr_name ] ) ) {
						if ( $this->is_empty_attribute_allowed( $node, $attr_name, $attr_spec_rule ) ) {
							$attr_node->value = '';
						} else {
							$attrs_to_remove[] = $attr_name;
						}
						continue;
					}
				}

				if ( isset( $attr_spec_rule[AMP_Rule_Spec::allowed_protocol] ) ) {
					if ( AMP_Rule_Spec::fail == $this->attr_spec_allowed_protocol_test( $node, $attr_name, $attr_spec_list[ $attr_name ] ) ) {
						if ( $this->is_empty_attribute_allowed( $node, $attr_name, $attr_spec_rule ) ) {
							$attr_node->value = '';
						} else {
							$attrs_to_remove[] = $attr_name;
						}
					}
				}

				if ( isset( $attr_spec_rule[AMP_Rule_Spec::allow_relative] ) ) {
					if ( AMP_Rule_Spec::fail == $this->attr_spec_disallowed_relative_protocol_test( $node, $attr_name, $attr_spec_list[ $attr_name ] ) ) {
						if ( $this->is_empty_attribute_allowed( $node, $attr_name, $attr_spec_rule ) ) {
							$attr_node->value = '';
						} else {
							$attrs_to_remove[] = $attr_name;
						}
					}
				}

				if ( isset( $attr_spec_rule[AMP_Rule_Spec::allow_empty] ) ) {
					if ( AMP_Rule_Spec::fail == $this->attr_spec_disallowed_empty_test( $node, $attr_name, $attr_spec_rule ) ) {
						if ( $this->is_empty_attribute_allowed( $node, $attr_name, $attr_spec_rule ) ) {
							$attr_node->value = '';
						} else {
							$attrs_to_remove[] = $attr_name;
						}
					}
				}

				if ( isset( $attr_spec_rule[AMP_Rule_Spec::disallowed_domain] ) ) {
					if ( AMP_Rule_Spec::fail == $this->attr_spec_disallowed_domain_test( $node, $attr_name, $attr_spec_rule ) ) {
						if ( $this->is_empty_attribute_allowed( $node, $attr_name, $attr_spec_rule ) ) {
							$attr_node->value = '';
						} else {
							$attrs_to_remove[] = $attr_name;
						}
					}
				}

				if ( isset( $attr_spec_rule[AMP_Rule_Spec::blacklisted_value_regex] ) ) {
					if ( AMP_Rule_Spec::fail == $this->attr_spec_blacklisted_attribute_values_test( $node, $attr_name, $attr_spec_rule ) ) {
						if ( $this->is_empty_attribute_allowed( $node, $attr_name, $attr_spec_rule ) ) {
							$attr_node->value = '';
						} else {
							$attrs_to_remove[] = $attr_name;
						}
					}
				}
			}
		}

		// Remove the attributes with disallowed values
		foreach ( $attrs_to_remove as $attr_name ) {
			$node->removeAttribute( $attr_name );
		}
	}

	/**
	 * Check to see if the given attribute is allowed to be empty in the given
	 * node.
	 */
	private function is_empty_attribute_allowed( $node, $attr_name, $attr_spec_rule ) {

		if ( isset( $attr_spec_rule[AMP_Rule_Spec::allow_empty] ) &&
			( true == $attr_spec_rule[AMP_Rule_Spec::allow_empty] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Checks to see if the given attribute is mandatory for the given node and
	 * whether the attribute (or a specified alternate) exists.
	 *	
	 *	Returns:
	 *		AMP_Rule_Spec::pass - $attr_name is mandatory and it exists
	 *		AMP_Rule_Spec::fail - $attr_name is mandatory, but doesn't exist
	 *		AMP_Rule_Spec::not_applicable - $attr_name is not mandatory
	 */
	private function mandatory_attr_test( $node, $attr_name, $attr_spec_rule ) {
		if ( isset( $attr_spec_rule[AMP_Rule_Spec::mandatory] ) &&
			( true == $attr_spec_rule[AMP_Rule_Spec::mandatory] ) ) {
			if ( $node->hasAttribute( $attr_name ) ) {
				return AMP_Rule_Spec::pass;
			} else {
				// check if an alternative name list is specified
				if ( isset( $attr_spec_rule[AMP_Rule_Spec::alternative_names] ) ) {
					foreach ( $attr_spec_rule[AMP_Rule_Spec::alternative_names] as $alt_name ) {
						if ( $node->hasAttribute( $alt_name ) ) {
							return AMP_Rule_Spec::pass;
						}
					}
				}

				return AMP_Rule_Spec::fail;
			}
		}

		return AMP_Rule_Spec::not_applicable;
	}

	private function attr_spec_value_test( $node, $attr_name, $attr_spec_rule ) {
		return $this->_attr_spec_value_test( $node, $attr_name, $attr_spec_rule, $node->getAttribute( $attr_name ) );
	}

	private function _attr_spec_value_test( $node, $attr_name, $attr_spec_rule, $attr_value ) {
		// check 'value' - case sensitive
		if ( isset( $attr_spec_rule[AMP_Rule_Spec::value] ) && $node->hasAttribute( $attr_name ) ) {
			if ( $attr_value == $attr_spec_rule[AMP_Rule_Spec::value] ) {
				return AMP_Rule_Spec::pass;
			} else {
				return AMP_Rule_Spec::fail;
			}
		}
		return AMP_Rule_Spec::not_applicable;
	}

	private function attr_spec_value_casei_test( $node, $attr_name, $attr_spec_rule ) {
		return $this->_attr_spec_value_casei_test( $node, $attr_name, $attr_spec_rule, $node->getAttribute( $attr_name ) );
	}

	private function _attr_spec_value_casei_test( $node, $attr_name, $attr_spec_rule, $attr_value ) {
		// check 'value_casei' - case insensitive
		if ( isset( $attr_spec_rule[AMP_Rule_Spec::value_casei] ) && $node->hasAttribute( $attr_name ) ) {
			$attr_value = strtolower( $attr_value );
			$rule_value = strtolower( $attr_spec_rule[AMP_Rule_Spec::value_casei] );
			if ( $attr_value == $rule_value ) {
				return AMP_Rule_Spec::pass;
			} else {
				return AMP_Rule_Spec::fail;
			}
		}
		return AMP_Rule_Spec::not_applicable;
	}

	private function attr_spec_value_regex_test( $node, $attr_name, $attr_spec_rule ) {
		return $this->_attr_spec_value_regex_test( $node, $attr_name, $attr_spec_rule, $node->getAttribute( $attr_name ) );
	}

	private function _attr_spec_value_regex_test( $node, $attr_name, $attr_spec_rule, $attr_value ) {
		// check 'value_regex' - case sensitive regex match
		if ( isset( $attr_spec_rule[AMP_Rule_Spec::value_regex] ) && $node->hasAttribute( $attr_name ) ) {
			$rule_value = $attr_spec_rule[AMP_Rule_Spec::value_regex];
			// Note: I added in the '^' and '$' to the regex pattern even though
			//	they weren't in the AMP spec. But leaving them out would allow
			//	both '_blank' and 'yyy_blankzzz' to be matched  by a regex spec of
			//	'(_blank|_self|_top)'. The AMP JS validator only accepts '_blank',
			//	so I'm leaving it this way for now.
			if ( preg_match('/^' . $rule_value . '$/u', $attr_value) ) {
				return AMP_Rule_Spec::pass;
			} else {
				return AMP_Rule_Spec::fail;
			}
		}
		return AMP_Rule_Spec::not_applicable;
	}

	private function attr_spec_value_regex_casei_test( $node, $attr_name, $attr_spec_rule ) {
		return $this->_attr_spec_value_regex_casei_test( $node, $attr_name, $attr_spec_rule, $node->getAttribute( $attr_name ) );
	}

	private function _attr_spec_value_regex_casei_test( $node, $attr_name, $attr_spec_rule, $attr_value ) {
		// check 'value_regex_casei' - case insensitive regex match
		if ( isset( $attr_spec_rule[AMP_Rule_Spec::value_regex_casei] ) && $node->hasAttribute( $attr_name ) ) {
			$rule_value = $attr_spec_rule[AMP_Rule_Spec::value_regex_casei];
			// See note above regarding the '^' and '$' that are added here.
			if ( preg_match('/^' . $rule_value . '$/ui', $attr_value) ) {
				return AMP_Rule_Spec::pass;
			} else {
				return AMP_Rule_Spec::fail;
			}
		}
		return AMP_Rule_Spec::not_applicable;
	}

	private function attr_spec_allowed_protocol_test( $node, $attr_name, $attr_spec_rule ) {
		if ( isset( $attr_spec_rule[AMP_Rule_Spec::allowed_protocol] )  && $node->hasAttribute( $attr_name ) ) {
			$attr_value = $node->getAttribute( $attr_name );
			// This seems to be an acceptable check since the AMP validator
			//	will allow a URL with no protocol to pass validation.
			if ( $url_scheme = parse_url( $attr_value, PHP_URL_SCHEME ) ) {
				$found = false;
				// The 'allowed_protocol' rule contains an array of allowed protocols.
				foreach ( $attr_spec_rule[AMP_Rule_Spec::allowed_protocol] as $allowed_protocol ) {
					if ( strtolower( $url_scheme ) == strtolower( $allowed_protocol ) ) {
						// found an acceptable protocol
						$found = true;
						break;
					}
				}
				if ( ! $found ) {
					// If we're here, then there was a required protocol 
					//	specified, but it wasn't found. Fail vaildation.
					return AMP_Rule_Spec::fail;
				}
			}
			return AMP_Rule_Spec::pass;
		}
		return AMP_Rule_Spec::not_applicable;
	}

	private function attr_spec_disallowed_relative_protocol_test( $node, $attr_name, $attr_spec_rule ) {
		if ( isset( $attr_spec_rule[AMP_Rule_Spec::allow_relative] ) &&
			 ( false == $attr_spec_rule[AMP_Rule_Spec::allow_relative] ) &&
			 $node->hasAttribute( $attr_name ) ) {
			$attr_value = $node->getAttribute( $attr_name );
			$parsed_url = parse_url( $attr_value );
			// The JS AMP validator seems to consider 'relative' to mean 
			//	*protocol* relative, not *host* relative for this rule. So,
			//	a url with an empty 'scheme' is considered "relative" by AMP.
			// 	ie. '//domain.com/path' and '/path' should both be considered
			//	relative for purposes of AMP validation.
			if ( empty( $parsed_url['scheme'] ) ) {
				return AMP_Rule_Spec::fail;
			}
			return AMP_Rule_Spec::pass;
		}
		return AMP_Rule_Spec::not_applicable;
	}

	private function attr_spec_disallowed_empty_test( $node, $attr_name, $attr_spec_rule ) {
		if ( isset( $attr_spec_rule[AMP_Rule_Spec::allow_empty] ) &&
			( false == $attr_spec_rule[AMP_Rule_Spec::allow_empty] ) &&
			 $node->hasAttribute( $attr_name ) ) {
			$attr_value = $node->getAttribute( $attr_name );
			if ( empty( $attr_value ) ) {
				return AMP_Rule_Spec::fail;
			}
			return AMP_Rule_Spec::pass;
		}
		return AMP_Rule_Spec::not_applicable;
	}

	private function attr_spec_disallowed_domain_test( $node, $attr_name, $attr_spec_rule ) {
		if ( isset( $attr_spec_rule[AMP_Rule_Spec::disallowed_domain] ) &&
			$node->hasAttribute( $attr_name ) ) {
			$attr_value = $node->getAttribute( $attr_name );
			$url_domain = parse_url( $attr_value, PHP_URL_HOST );
			if ( ! empty( $url_domain ) ) {
				foreach ( $attr_spec_rule[AMP_Rule_Spec::disallowed_domain] as $disallowed_domain ) {
					if ( strtolower( $url_domain ) == strtolower( $disallowed_domain ) ) {
						// Found a disallowed domain, fail validation.
						return AMP_Rule_Spec::fail;
					}
				}
				return AMP_Rule_Spec::pass;
			}
		}
		return AMP_Rule_Spec::not_applicable;
	}

	private function attr_spec_blacklisted_attribute_values_test( $node, $attr_name, $attr_spec_rule ) {
		if ( isset( $attr_spec_rule[AMP_Rule_Spec::blacklisted_value_regex] ) &&
			$node->hasAttribute( $attr_name ) ) {
			//	*Not* adding '^' and '$' to the regex pattern here because
			//	the AMP JS validator doesn't allow variants that still contain
			//	the blacklisted string. For example, neither '__amp_source_origin'
			//	nor 'AAA__amp_source_originZZZ' are allowed.
			$pattern = '/' . $attr_spec_rule[AMP_Rule_Spec::blacklisted_value_regex] . '/u';
			$attr_value = $node->getAttribute( $attr_name );
			if ( preg_match( $pattern, $attr_value ) ) {
				// Found a blacklisted value, fail test.
				return AMP_Rule_Spec::fail;
			}
			return AMP_Rule_Spec::pass;
		}
		return AMP_Rule_Spec::not_applicable;
	}

	/**
	 * Return true if the attribute name is valid for this attr_spec, false otherwise.
	 */
	private function is_amp_allowed_attribute( $attr_name, $attr_spec_list ) {
		return ( isset( $this->globally_allowed_attributes[ $attr_name ] ) || 
			isset( $attr_spec_list[ $attr_name ] ) ||
			isset( AMP_Rule_Spec::tags_allowed_for_styling[ $attr_name ] ) );
	}

	/**
	 * Return true if the specified node's name is an AMP allowed tag, false otherwise.
	 */
	private function is_amp_allowed_tag( $node ) {
		// Return true if node is on the allowed tags list or if it is a text node.
		return ( isset( $this->allowed_tags[ $node->nodeName ] ) || 
			( XML_TEXT_NODE == $node->nodeType ) );
	}

	/**
	 * Return true if the given node has a direct parent with the given name,
	 * otherwise return false.
	 */
	private function has_parent( $node, $parent_tag_name ) {
		if ( $node && $node->parentNode && ( $node->parentNode->nodeName == $parent_tag_name ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Return true if the given node has any ancestor with the give name,
	 * otherwise return false.
	 */
	private function has_ancestor( $node, $ancestor_tag_name ) {
		if ( $this->get_ancestor_with_tag_name( $node, $ancestor_tag_name ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Returns the first ancestor with the given tag name. If no ancestor
	 * with that name is found, returns null.
	 */
	private function get_ancestor_with_tag_name( $node, $ancestor_tag_name ) {
		while ( $node && $node = $node->parentNode ) {
			if ( $node->nodeName == $ancestor_tag_name ) {
				return $node;
			}
		}
		return null;
	}

	/**
	 * Replaces the given node with it's child nodes, if any, and adds them to
	 * the stack for processing by the sanitize() function.
	 */
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
			$this->remove_node( $node );
		}
	}

	/**
	 * Remove a node. If removing the node makes the parent node empty, then
	 * remove the parent as well. Continue until a non-empty parent is reached
	 * or the 'body' element is reached.
	 */
	private function remove_node( $node ) {
		if ( $node && $parent = $node->parentNode ) {
			$parent->removeChild( $node );
		}
		while( $parent && ( ! $parent->hasChildNodes() ) && ( 'body' != $parent->nodeName ) ) {
			$node = $parent;
			$parent = $parent->parentNode;
			if ( $parent ) {
				$parent->removeChild( $node );
			}
		}
	}
}

/**
 * This is a set of constants that are used throughout the sanitizer.
 * The rule name strings are listed here because it's easier to have the php
 * interpreter catch a typo than for me to catch mistyping a string.
 */
abstract class AMP_Rule_Spec {

	// AMP rule_spec types
	const attr_spec_list = 'attr_spec_list';
	const tag_spec = 'tag_spec';

	// AMP attr_spec value check results
	const pass = 'pass';
	const fail = 'fail';
	const not_applicable = 'not_applicable';

	// tag rule names
	const disallowed_ancestor = 'disallowed_ancestor';
	const mandatory_ancestor = 'mandatory_ancestor';
	const mandatory_parent = 'mandatory_parent';

	// attr rule names
	const allow_empty = 'allow_empty';
	const allow_relative = 'allow_relative';
	const allowed_protocol = 'allowed_protocol';
	const alternative_names = 'alternative_names';
	const blacklisted_value_regex = 'blacklisted_value_regex';
	const disallowed_domain = 'disallowed_domain';
	const mandatory = 'mandatory';
	const value = 'value';
	const value_casei = 'value_casei';
	const value_regex = 'value_regex';
	const value_regex_casei = 'value_regex_casei';

	// If a node type listed here is invalid, it and it's subtree will be 
	//	removed if it is invalid. This is mainly  because any children will be 
	//	non-functional without this parent.
	//
	// If a tag is not listed here, it will be replaced by its children if it
	//	is invalid.
	//	
	// TODO: There are other nodes that should probably be listed here as well.
	const node_types_to_remove_if_invalid = array(
		'form',
		'input',
		'link',
		'meta',
		'script',
		'style',
	);

	// Node types listed here will not be removed from the DOM, even if they are
	//	empty after being sanitized.
	const node_types_to_allow_empty = array(
		'br',
		'th',
		'td',
	);

	// This is here because these tags are not listed in either the AMP global
	//	attributes or the attr_spec_list for elements such as 'amp-img' for which
	//	they are still valid.
	//
	// I decided to add them here instead of hard-coding them into amp-wp-build.py
	//	because I want the generated whitelist to accurately reflect the protoascii
	//	file it was built from as much as possible.
	const tags_allowed_for_styling = array(
		'height' => array(),
		'layout' => array(),
		'sizes' => array(),
		'width' => array(),
	);
}
