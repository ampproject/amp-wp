<?php

require_once( AMP__DIR__ . '/includes/sanitizers/class-amp-base-sanitizer.php' );
require_once( AMP__DIR__ . '/includes/sanitizers/class-amp-allowed-tags-generated.php' );

/**
 * Strips tags and attributes not allowed by the AMP sped from the content.
 *
 * Allowed tags array is generated from this protocol buffer:
 *     https://github.com/ampproject/amphtml/blob/master/validator/validator-main.protoascii
 *     by the python script in amp-wp/bin/amp_wp_build.py. See the comment at the top
 *     of that file for instructions to generate class-amp-allowed-tags-generated.php. 
 *
 * TODO: AMP Spec items not checked by this sanitizer -
 * - `if_value_regex` - if one attribute value matches, this places a restriction
 *		on another attribute/value.
 * - `also_requires_attr` - if one attribute is present, this requires another.
 * - `mandatory_oneof` -  Within the context of the tag, exactly one of the attributes
 *		must be present.
 * - `CdataSpec` - CDATA is not validated or sanitized.
 * - `ChildTagSpec` - Places restrictions on the number and type of child tags.
 */
class AMP_Tag_And_Attribute_Sanitizer extends AMP_Base_Sanitizer {

	protected $allowed_tags;
	protected $globally_allowed_attributes;
	protected $layout_allowed_attributes;
	private $stack = array();

	private function get_whitelist_data() {
			// Get whitelists.
		$this->allowed_tags = apply_filters( 'amp_allowed_tags', AMP_Allowed_Tags_Generated::get_allowed_tags() );
		$this->globally_allowed_attributes = apply_filters( 'amp_globally_allowed_attributes', AMP_Allowed_Tags_Generated::get_allowed_attributes() );
		$this->layout_allowed_attributes = apply_filters( 'amp_layout_allowed_attributes', AMP_Allowed_Tags_Generated::get_layout_attributes() );
	}

	public function sanitize() {
		$this->get_whitelist_data();

		foreach( AMP_Rule_Spec::$additional_allowed_tags as $tag_name => $tag_rule_spec ) {
			$this->allowed_tags[ $tag_name ][] = $tag_rule_spec;
		}

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
		// Don't process text or comment nodes
		if ( ( XML_TEXT_NODE == $node->nodeType ) ||
			( XML_COMMENT_NODE == $node->nodeType ) ||
			( XML_CDATA_SECTION_NODE == $node->nodeType ) ) {
			return;
		}

		// Remove nodes with non-whitelisted tags.
		if ( ! $this->is_amp_allowed_tag( $node ) ) {

			// If it's not an allowed tag, replace the node with it's children
			$this->replace_node_with_children( $node );
			// Return early since this node no longer exists.
			return;
		}

		// Compile a list of rule_specs to validate for this node based on
		// tag name of the node.
		$rule_spec_list_to_validate = array();
		if ( isset( $this->allowed_tags[ $node->nodeName ] ) ) {
			$rule_spec_list = $this->allowed_tags[ $node->nodeName ];
		}
		foreach ( $rule_spec_list as $id => $rule_spec ) {
			if ( $this->validate_tag_spec_for_node( $node, $rule_spec[AMP_Rule_Spec::tag_spec] ) ) {
				$rule_spec_list_to_validate[ $id ] = $rule_spec;
			}
		}

		// Allow rule_spec_list to be filtered. ex.:
        // add_filter( 'amp_tags_and_attributes_rule_spec_list_for_node', array( $this, 'example_rule_spec_list_filter' ), 10, 2 );
        // public function example_rule_spec_list_filter( $rule_spec_list, $node ) {
		//		// remove audio tags
		//		if ( 'amp-audio' == $node->nodeName ) {
		//			$rule_spec_list = array();
		//		}
        // 		return $rule_spec_list;
        // }
		$rule_spec_list_to_validate = apply_filters( 'amp_tags_and_attributes_rule_spec_list_for_node', $rule_spec_list_to_validate, $node );

		// If no valid rule_specs exist, then remove this node and return.
		if ( empty( $rule_spec_list_to_validate ) ) {
			$this->remove_node( $node );
			return;
		}

		// The remaining validations all have to do with attributes.
		if ( $node->hasAttributes() ) {

			$attr_spec_list = array();

			// If we have exactly one rule_spec, use it's attr_spec_list to 
			// validate the node's attributes.
			if ( 1 == count( $rule_spec_list_to_validate ) ) {
				$rule_spec = array_pop( $rule_spec_list_to_validate );
				$attr_spec_list = $rule_spec[AMP_Rule_Spec::attr_spec_list];

			// If there is more than one valid rule_spec for this node, then
			// try to deduce which one is intended by inspecting the node's
			// attributes.
			} else {

				// Get a score from each attr_spec_list by seeing how many
				// attributes and values match the node.
				$attr_spec_scores = array();
				foreach ( $rule_spec_list_to_validate as $spec_id => $rule_spec ) {
					$attr_spec_scores[ $spec_id ] = $this->validate_attr_spec_list_for_node( $node, $rule_spec[AMP_Rule_Spec::attr_spec_list] );
				}

				// Get the key(s) to the highest score(s).
				$spec_ids_sorted = array_keys( $attr_spec_scores, max( $attr_spec_scores ) );

				// If there is exactly one attr_spec with a max score, use that one.
				if ( 1 == count( $spec_ids_sorted ) ) {
					$attr_spec_list = $rule_spec_list_to_validate[ $spec_ids_sorted[0] ][AMP_Rule_Spec::attr_spec_list];

				// Otherwise...
				} else {
					// This shoud not happen very often, but...
					// If we're here, then we have no idea which spec should
					// be used. Try to merge the top scoring ones and cross
					// your fingers.
					foreach( $spec_ids_sorted as $id ) {
						$attr_spec_list = array_merge( $attr_spec_list, $rule_spec_list_to_validate[ $id ][AMP_Rule_Spec::attr_spec_list] );
					}
				}
			}

			// Allow attr_spec_list to be filtered. ex.:
			// add_filter( 'amp_tags_and_attributes_attr_spec_list_for_node', array( $this, 'example_attr_spec_list_filter' ), 10, 2 );
			// public function example_attr_spec_list_filter( $attr_spec_list, $node ) {
			// 	// Look for an anchor tag.
			// 	if ( 'a' == $node->nodeName ) {
			// 		// Add some domains to the disallowed_domain array.
			// 		$disallowed_domains = array(
			// 			'example.com',
			// 			'rea-bad-domain.com',
			// 		);
			// 		if ( isset( $attr_spec_list['href']['disallowed_domain'] ) ) {
			// 			$disallowed_domains = array_merge( $attr_spec_list['href']['disallowed_domain'], $disallowed_domains );
			// 		}
			// 		$attr_spec_list['href']['disallowed_domain'] = $disallowed_domains;
			//
			// 		// Add a new protocol to the allowed_protocol array.
			// 		$allowed_protocol = array(
			// 			'protocol'
			// 		);
			//
			// 		if ( isset( $attr_spec_list['href']['allowed_protocol'] ) ) {
			// 			$allowed_protocol = array_merge( $attr_spec_list['href']['allowed_protocol'], $allowed_protocol );
			// 		}
			// 		$attr_spec_list['href']['allowed_protocol'] = $allowed_protocol;
			// 	}
			// 	return $attr_spec_list;
			// }
			$attr_spec_list = apply_filters( 'amp_tags_and_attributes_attr_spec_list_for_node', $attr_spec_list, $node );

			// Remove any remaining disallowed attributes.
			$this->sanitize_disallowed_attributes_in_node( $node, $attr_spec_list );

			// Remove values that don't conform to the attr_spec.
			$this->sanitize_disallowed_attribute_values_in_node( $node, $attr_spec_list );

			// Allow additional sanitization to be done here.
			do_action( 'amp_tags_and_attributes_sanitize_node', $node, $attr_spec_list );
		}
	}

	/**
	 * Checks to see if a node's placement with the DOM is be valid for the 
	 * given tag_spec. If there are restrictions placed on the type of node 
	 * that can be an immediate parent or an ancestor of this node, then make 
	 * sure those restrictions are met.
	 *
	 * If any of the tests on the restrictions fail, return false, otherwise
	 *	return true.
	 */
	private function validate_tag_spec_for_node( $node, $tag_spec ) {

		if ( ! empty( $tag_spec[AMP_Rule_Spec::mandatory_parent] ) &&
			! $this->has_parent( $node, $tag_spec[AMP_Rule_Spec::mandatory_parent] ) ) {
			return false;
		}

		if ( ! empty( $tag_spec[AMP_Rule_Spec::disallowed_ancestor] ) ) {
			foreach ( $tag_spec[AMP_Rule_Spec::disallowed_ancestor] as $disallowed_ancestor_node_name ) {
				if ( $this->has_ancestor( $node, $disallowed_ancestor_node_name ) ) {
					return false;
				}
			}
		}

		if ( ! empty( $tag_spec[AMP_Rule_Spec::mandatory_ancestor] ) &&
			! $this->has_ancestor( $node, $tag_spec[AMP_Rule_Spec::mandatory_ancestor] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Checks to see if a spec is potentially valid for the given node based on
	 * 	the attributes present in the node.
	 *
	 * Note: This can be a very expensive function. Use it sparingly.
	 */
	private function validate_attr_spec_list_for_node( $node, $attr_spec_list ) {
		// If this node doesn't have any attributes, there is no point in
		//	continuing.
		if ( ! $node->hasAttributes() ) {
			return 0;
		}

		foreach( $node->attributes as $attr_name => $attr_node ) {
			if ( isset( $attr_spec_list[ $attr_name ][AMP_Rule_Spec::alternative_names] ) ) {
				foreach( $attr_spec_list[ $attr_name ][AMP_Rule_Spec::alternative_names] as $attr_alt_name ) {
					$attr_spec_list[ $attr_alt_name ] = $attr_spec_list[ $attr_name ];
				}
			}
		}

		$score = 0;

		// Iterate through each attribute rule in this attr spec list and run
		// the series of tests. Each filter is given a `$node`, an `$attr_name`,
		// and an `$attr_spec_rule`. If the `$attr_spec_rule` seems to be valid
		// for the given node, then the filter should increment the score by one.
		foreach ( $attr_spec_list as $attr_name => $attr_spec_rule ) {

		 	// If a mandatory attribute is required, and attribute exists, pass.
			if ( isset( $attr_spec_rule[AMP_Rule_Spec::mandatory] ) ) {
				if ( AMP_Rule_Spec::pass == $this->check_attr_spec_rule_mandatory( $node, $attr_name, $attr_spec_rule ) ) {
					$score += 1;
				}
			}

			// Check 'value' - case sensitive
			// Given attribute's value must exactly equal value of the rule to pass.
			if ( isset( $attr_spec_rule[AMP_Rule_Spec::value] ) ) {
				if ( AMP_Rule_Spec::pass == $this->check_attr_spec_rule_value( $node, $attr_name, $attr_spec_rule ) ) {
					$score += 1;
				}
			}

			// Check 'value_regex' - case sensitive regex match
			// Given attribute's value must be a case insensitive match to regex pattern
			// specified by the value of rule to pass.
			if ( isset( $attr_spec_rule[AMP_Rule_Spec::value_regex] ) ) {
				if ( AMP_Rule_Spec::pass == $this->check_attr_spec_rule_value_regex( $node, $attr_name, $attr_spec_rule ) ) {
					$score += 1;
				}
			}

			// Check 'value_casei' - case insensitive
			// Given attribute's value must be a case insensitive match to the value of
			// the rule to pass.
			if ( isset( $attr_spec_rule[AMP_Rule_Spec::value_casei] ) ) {
				if ( AMP_Rule_Spec::pass == $this->check_attr_spec_rule_value_casei( $node, $attr_name, $attr_spec_rule ) ) {
					$score += 1;
				}
			}

			// Check 'value_regex_casei' - case insensitive regex match
			// Given attribute's value must be a case insensitive match to the regex
			// pattern specified by the value of the rule to pass.
			if ( isset( $attr_spec_rule[AMP_Rule_Spec::value_regex_casei] ) ) {
				if ( AMP_Rule_Spec::pass == $this->check_attr_spec_rule_value_regex_casei( $node, $attr_name, $attr_spec_rule ) ) {
					$score += 1;
				}
			}

			// If given attribute's value is a URL with a protocol, the protocol must
			// be in the array specified by the rule's value to pass.
			if ( isset( $attr_spec_rule[AMP_Rule_Spec::allowed_protocol] ) ) {
				if ( AMP_Rule_Spec::pass == $this->check_attr_spec_rule_allowed_protocol( $node, $attr_name, $attr_spec_rule ) ) {
					$score += 1;
				}
			}

			// If the given attribute's value is *not* a relative path, and the rule's
			// value is `false`, then pass.
			if ( isset( $attr_spec_rule[AMP_Rule_Spec::allow_relative] ) ) {
				if ( AMP_Rule_Spec::pass == $this->check_attr_spec_rule_disallowed_relative( $node, $attr_name, $attr_spec_rule ) ) {
					$score += 1;
				}
			}

			// If the given attribute's value exists, is non-empty and the rule's value 
			// is false, then pass.
			if ( isset( $attr_spec_rule[AMP_Rule_Spec::allow_empty] ) ) {
				if ( AMP_Rule_Spec::pass == $this->check_attr_spec_rule_disallowed_empty( $node, $attr_name, $attr_spec_rule ) ) {
					$score += 1;
				}
			}

			// If the given attribute's value is a URL and does not match any of the list
			// of domains in the value of the rule, then pass.
			if ( isset( $attr_spec_rule[AMP_Rule_Spec::disallowed_domain] ) ) {
				if ( AMP_Rule_Spec::pass == $this->check_attr_spec_rule_disallowed_domain( $node, $attr_name, $attr_spec_rule ) ) {
					$score += 1;
				}
			}

			// If the attribute's value exists and does not match the regex specified
			// by the rule's value, then pass.
			if ( isset( $attr_spec_rule[AMP_Rule_Spec::blacklisted_value_regex] ) ) {
				if ( AMP_Rule_Spec::pass == $this->check_attr_spec_rule_blacklisted_value_regex( $node, $attr_name, $attr_spec_rule ) ) {
					$score += 1;
				}
			}
		}
					
		// return true;
		return $score;
	}

	/**
	 * If an attribute is not listed in $allowed_attrs, then it will be removed 
	 * from $node.
	 */
	private function sanitize_disallowed_attributes_in_node( $node, $attr_spec_list ) {
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

			$attrs_to_remove = apply_filters( 'amp_tags_and_attributes_sanitize_disallowed_attributes_in_node', $attrs_to_remove, $node, $attr_name, $attr_spec_list );
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
	private function sanitize_disallowed_attribute_values_in_node( $node, $attr_spec_list ) {
		$this->_sanitize_disallowed_attribute_values_in_node( $node, $this->globally_allowed_attributes );
		if ( ! empty( $attr_spec_list ) ) {
			$this->_sanitize_disallowed_attribute_values_in_node( $node, $attr_spec_list );
		}
	}
	private function _sanitize_disallowed_attribute_values_in_node( $node, $attr_spec_list ) {
		$attrs_to_remove = array();

		foreach( $attr_spec_list as $attr_name => $attr_val ) {
			if ( isset( $attr_spec_list[ $attr_name ][AMP_Rule_Spec::alternative_names] ) ) {
				foreach( $attr_spec_list[ $attr_name ][AMP_Rule_Spec::alternative_names] as $attr_alt_name ) {
					$attr_spec_list[ $attr_alt_name ] = $attr_spec_list[ $attr_name ];
				}
			}
		}

		foreach( $node->attributes as $attr_name => $attr_node ) {

			if ( ! isset( $attr_spec_list[$attr_name] ) ) {
				continue;
			}

			$attr_spec_rule = $attr_spec_list[$attr_name];

			if ( isset( $attr_spec_rule[AMP_Rule_Spec::value] ) &&
				AMP_Rule_Spec::fail == $this->check_attr_spec_rule_value( $node, $attr_name, $attr_spec_rule ) ) {
				$attrs_to_remove[] = $attr_name;
				continue;
			}

			if ( isset( $attr_spec_rule[AMP_Rule_Spec::value_casei] ) &&
				AMP_Rule_Spec::fail == $this->check_attr_spec_rule_value_casei( $node, $attr_name, $attr_spec_rule ) ) {
				$attrs_to_remove[] = $attr_name;
				continue;
			}

			if ( isset( $attr_spec_rule[AMP_Rule_Spec::value_regex] ) &&
				AMP_Rule_Spec::fail == $this->check_attr_spec_rule_value_regex( $node, $attr_name, $attr_spec_rule ) ) {
				$attrs_to_remove[] = $attr_name;
				continue;
			}

			if ( isset( $attr_spec_rule[AMP_Rule_Spec::value_regex_casei] ) &&
				AMP_Rule_Spec::fail == $this->check_attr_spec_rule_value_regex_casei( $node, $attr_name, $attr_spec_rule ) ) {
				$attrs_to_remove[] = $attr_name;
				continue;
			}

			if ( isset( $attr_spec_rule[AMP_Rule_Spec::allowed_protocol] ) &&
				AMP_Rule_Spec::fail == $this->check_attr_spec_rule_allowed_protocol( $node, $attr_name, $attr_spec_rule ) ) {
				$attrs_to_remove[] = $attr_name;
				continue;
			}

			if ( isset( $attr_spec_rule[AMP_Rule_Spec::allow_relative] ) &&
				AMP_Rule_Spec::fail == $this->check_attr_spec_rule_disallowed_relative( $node, $attr_name, $attr_spec_rule ) ) {
				$attrs_to_remove[] = $attr_name;
				continue;
			}

			if ( isset( $attr_spec_rule[AMP_Rule_Spec::allow_empty] ) &&
				AMP_Rule_Spec::fail == $this->check_attr_spec_rule_disallowed_empty( $node, $attr_name, $attr_spec_rule ) ) {
				$attrs_to_remove[] = $attr_name;
				continue;
			}

			if ( isset( $attr_spec_rule[AMP_Rule_Spec::disallowed_domain] ) &&
				AMP_Rule_Spec::fail == $this->check_attr_spec_rule_disallowed_domain( $node, $attr_name, $attr_spec_rule ) ) {
				$attrs_to_remove[] = $attr_name;
				continue;
			}

			if ( isset( $attr_spec_rule[AMP_Rule_Spec::blacklisted_value_regex] ) &&
				AMP_Rule_Spec::fail == $this->check_attr_spec_rule_blacklisted_value_regex( $node, $attr_name, $attr_spec_rule ) ) {
				$attrs_to_remove[] = $attr_name;
				continue;
			}

			$attrs_to_remove = apply_filters( 'amp_tags_and_attributes_sanitize_attr_for_node', $attrs_to_remove, $node, $attr_name, $attr_spec_rule );
		}

		// Remove the disallowed values
		foreach ( $attrs_to_remove as $attr_name ) {
			if ( isset( $attr_spec_list[$attr_name][AMP_Rule_Spec::allow_empty] ) &&
				( true == $attr_spec_list[$attr_name][AMP_Rule_Spec::allow_empty] ) ) {
				$attr = $node->attributes;
				$attr[ $attr_name ]->value = '';
			} else {
				$node->removeAttribute( $attr_name );
			}
		}
	}

	/**
	 * Checks to see if the given attribute is mandatory for the given node and
	 * whether the attribute (or a specified alternate) exists.
	 *	
	 *	Returns:
	 *		- AMP_Rule_Spec::pass - $attr_name is mandatory and it exists
	 *		- AMP_Rule_Spec::fail - $attr_name is mandatory, but doesn't exist
	 *		- AMP_Rule_Spec::not_applicable - $attr_name is not mandatory
	 */
	private function check_attr_spec_rule_mandatory( $node, $attr_name, $attr_spec_rule ) {
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

	/**
	 * Checks to see if the given attribute exists, has a value rule, and whether
	 * the attribute matches the value.
	 *
	 * Returns:
	 * 	- AMP_Rule_Spec::pass - $attr_name has a value that matches the rule.
	 * 	- AMP_Rule_Spec::fail - $attr_name has a value that does *not* match rule.
	 * 	- AMP_Rule_Spec::not_applicable - $attr_name does not exist or there
	 * 		is no rule for this attribute.
	 */
	private function check_attr_spec_rule_value( $node, $attr_name, $attr_spec_rule ) {
		if ( isset( $attr_spec_rule[AMP_Rule_Spec::value] ) ) {
			if ( $node->hasAttribute( $attr_name ) ) {
				if ( $node->getAttribute( $attr_name ) == $attr_spec_rule[AMP_Rule_Spec::value] ) {
					return AMP_Rule_Spec::pass;
				} else {
					return AMP_Rule_Spec::fail;
				}
			} elseif ( isset( $attr_spec_rule[AMP_Rule_Spec::alternative_names] ) ) {
				foreach ( $attr_spec_rule[AMP_Rule_Spec::alternative_names] as $alternative_name ) {
					if ( $node->hasAttribute( $alternative_name ) ) {
						if ( $node->getAttribute( $alternative_name ) == $attr_spec_rule[AMP_Rule_Spec::value] ) {
							return AMP_Rule_Spec::pass;
						} else {
							return AMP_Rule_Spec::fail;
						}
					}
				}
			}
		}
		return AMP_Rule_Spec::not_applicable;
	}

	/**
	 * Checks to see if the given attribute exists, has a value rule, and whether
	 * or not the value matches the rule without respect to case.
	 *
	 * Returns:
	 * 	- AMP_Rule_Spec::pass - $attr_name has a value that matches the rule.
	 * 	- AMP_Rule_Spec::fail - $attr_name has a value that does *not* match rule.
	 * 	- AMP_Rule_Spec::not_applicable - $attr_name does not exist or there
	 * 		is no rule for this attribute.
	 */
	private function check_attr_spec_rule_value_casei( $node, $attr_name, $attr_spec_rule ) {
		// check 'value_casei' - case insensitive
		if ( isset( $attr_spec_rule[AMP_Rule_Spec::value_casei] ) ) {
			$rule_value = strtolower( $attr_spec_rule[AMP_Rule_Spec::value_casei] );
			if ( $node->hasAttribute( $attr_name ) ) {
				$attr_value = strtolower( $node->getAttribute( $attr_name ) );
				if ( $attr_value == $rule_value ) {
					return AMP_Rule_Spec::pass;
				} else {
					return AMP_Rule_Spec::fail;
				}
			} elseif ( isset( $attr_spec_rule[AMP_Rule_Spec::alternative_names] ) ) {
				foreach ( $attr_spec_rule[AMP_Rule_Spec::alternative_names] as $alternative_name ) {
					if ( $node->hasAttribute( $alternative_name ) ) {
						$attr_value = strtolower( $node->getAttribute( $alternative_name ) );
						if ( $attr_value == $rule_value ) {
							return AMP_Rule_Spec::pass;
						} else {
							return AMP_Rule_Spec::fail;
						}
					}
				}
			}
		}
		return AMP_Rule_Spec::not_applicable;
	}

	/**
	 * Checks to see if the given attribute exists, has a value_regex rule, and
	 * whether or not the value matches the rule.
	 *
	 * Returns:
	 * 	- AMP_Rule_Spec::pass - $attr_name has a value that matches the rule.
	 * 	- AMP_Rule_Spec::fail - $attr_name has a value that does *not* match rule.
	 * 	- AMP_Rule_Spec::not_applicable - $attr_name does not exist or there
	 * 		is no rule for this attribute.
	 */
	private function check_attr_spec_rule_value_regex( $node, $attr_name, $attr_spec_rule ) {
		// check 'value_regex' - case sensitive regex match
		if ( isset( $attr_spec_rule[AMP_Rule_Spec::value_regex] ) && $node->hasAttribute( $attr_name ) ) {
			$rule_value = $attr_spec_rule[AMP_Rule_Spec::value_regex];
			// Note: I added in the '^' and '$' to the regex pattern even though
			//	they weren't in the AMP spec. But leaving them out would allow
			//	both '_blank' and 'yyy_blankzzz' to be matched by a regex rule of
			//	'(_blank|_self|_top)'. The AMP JS validator only accepts '_blank',
			//	so I'm leaving it this way for now.
			if ( preg_match('@^' . $rule_value . '$@u', $node->getAttribute( $attr_name )) ) {
				return AMP_Rule_Spec::pass;
			} else {
				return AMP_Rule_Spec::fail;
			}
		}
		return AMP_Rule_Spec::not_applicable;
	}

	/**
	 * Checks to see if the given attribute exists, has a value_regex_casei rule,
	 * and whether or not the value matches the rule without respect to case.
	 *
	 * Returns:
	 * 	- AMP_Rule_Spec::pass - $attr_name has a value that matches the rule.
	 * 	- AMP_Rule_Spec::fail - $attr_name has a value that does *not* match rule.
	 * 	- AMP_Rule_Spec::not_applicable - $attr_name does not exist or there
	 * 		is no rule for this attribute.
	 */
	private function check_attr_spec_rule_value_regex_casei( $node, $attr_name, $attr_spec_rule ) {
		// check 'value_regex_casei' - case insensitive regex match
		if ( isset( $attr_spec_rule[AMP_Rule_Spec::value_regex_casei] ) && $node->hasAttribute( $attr_name ) ) {
			$rule_value = $attr_spec_rule[AMP_Rule_Spec::value_regex_casei];
			// See note above regarding the '^' and '$' that are added here.
			if ( preg_match('/^' . $rule_value . '$/ui', $node->getAttribute( $attr_name ) ) ) {
				return AMP_Rule_Spec::pass;
			} else {
				return AMP_Rule_Spec::fail;
			}
		}
		return AMP_Rule_Spec::not_applicable;
	}

	/**
	 * Checks to see if the given attribute exists, has a protocol rule, and
	 * whether or not the value matches the rule.
	 *
	 * Returns:
	 * 	- AMP_Rule_Spec::pass - $attr_name has a value that matches the rule.
	 * 	- AMP_Rule_Spec::fail - $attr_name has a value that does *not* match rule.
	 * 	- AMP_Rule_Spec::not_applicable - $attr_name does not exist or there
	 * 		is no rule for this attribute.
	 */
	private function check_attr_spec_rule_allowed_protocol( $node, $attr_name, $attr_spec_rule ) {
		if ( isset( $attr_spec_rule[AMP_Rule_Spec::allowed_protocol] ) ) {
			if ( $node->hasAttribute( $attr_name ) ) {
				$attr_value = $node->getAttribute( $attr_name );
				$attr_value = preg_replace( '/\s*,\s*/', ',', $attr_value );
				$urls_to_test = explode( ',', $attr_value );
				foreach ( $urls_to_test as $url ) {
					// This seems to be an acceptable check since the AMP validator
					//	will allow a URL with no protocol to pass validation.
					if ( $url_scheme = parse_url( $url, PHP_URL_SCHEME ) ) {
						if ( ! in_array( strtolower( $url_scheme ), $attr_spec_rule[AMP_Rule_Spec::allowed_protocol] ) ) {
							return AMP_Rule_Spec::fail;
						}
					}
				}
				return AMP_Rule_Spec::pass;
			} elseif ( isset( $attr_spec_rule[AMP_Rule_Spec::alternative_names] ) ) {
				foreach ( $attr_spec_rule[AMP_Rule_Spec::alternative_names] as $alternative_name ) {
					if ( $node->hasAttribute( $alternative_name ) ) {
						$attr_value = $node->getAttribute( $alternative_name );
						$attr_value = preg_replace( '/\s*,\s*/', ',', $attr_value );
						$urls_to_test = explode( ',', $attr_value );
						foreach ( $urls_to_test as $url ) {
							// This seems to be an acceptable check since the AMP validator
							//	will allow a URL with no protocol to pass validation.
							if ( $url_scheme = parse_url( $url, PHP_URL_SCHEME ) ) {
								if ( ! in_array( strtolower( $url_scheme ), $attr_spec_rule[AMP_Rule_Spec::allowed_protocol] ) ) {
									return AMP_Rule_Spec::fail;
								}
							}
						}
						return AMP_Rule_Spec::pass;
					}
				}
			}
		}
		return AMP_Rule_Spec::not_applicable;
	}

	/**
	 * Checks to see if the given attribute exists, has a disallowed_relative rule,
	 * and whether or not the value matches the rule.
	 *
	 * Returns:
	 * 	- AMP_Rule_Spec::pass - $attr_name has a value that matches the rule.
	 * 	- AMP_Rule_Spec::fail - $attr_name has a value that does *not* match rule.
	 * 	- AMP_Rule_Spec::not_applicable - $attr_name does not exist or there
	 * 		is no rule for this attribute.
	 */
	private function check_attr_spec_rule_disallowed_relative( $node, $attr_name, $attr_spec_rule ) {
		if ( isset( $attr_spec_rule[AMP_Rule_Spec::allow_relative] ) &&
			( false == $attr_spec_rule[AMP_Rule_Spec::allow_relative] ) ) {
			if ( $node->hasAttribute( $attr_name ) ) {
				$attr_value = $node->getAttribute( $attr_name );
				$attr_value = preg_replace( '/\s*,\s*/', ',', $attr_value );
				$urls_to_test = explode( ',', $attr_value );
				foreach ( $urls_to_test as $url ) {
					$parsed_url = parse_url( $url );
					// The JS AMP validator seems to consider 'relative' to mean 
					//	*protocol* relative, not *host* relative for this rule. So,
					//	a url with an empty 'scheme' is considered "relative" by AMP.
					// 	ie. '//domain.com/path' and '/path' should both be considered
					//	relative for purposes of AMP validation.
					if ( empty( $parsed_url['scheme'] ) ) {
						return AMP_Rule_Spec::fail;
					}
				}
				return AMP_Rule_Spec::pass;
			} elseif ( isset( $attr_spec_rule[AMP_Rule_Spec::alternative_names] ) ) {
				foreach ( $attr_spec_rule[AMP_Rule_Spec::alternative_names] as $alternative_name ) {
					if ( $node->hasAttribute( $alternative_name ) ) {
						$attr_value = $node->getAttribute( $alternative_name );
						$attr_value = preg_replace( '/\s*,\s*/', ',', $attr_value );
						$urls_to_test = explode( ',', $attr_value );
						foreach ( $urls_to_test as $url ) {
							$parsed_url = parse_url( $url );
							if ( empty( $parsed_url['scheme'] ) ) {
								return AMP_Rule_Spec::fail;
							}
						}
					}
				}
				return AMP_Rule_Spec::pass;
			}
		}
		return AMP_Rule_Spec::not_applicable;
	}

	/**
	 * Checks to see if the given attribute exists, has a disallowed_relative rule,
	 * and whether or not the value matches the rule.
	 *
	 * Returns:
	 * 	- AMP_Rule_Spec::pass - $attr_name has a value that matches the rule.
	 * 	- AMP_Rule_Spec::fail - $attr_name has a value that does *not* match rule.
	 * 	- AMP_Rule_Spec::not_applicable - $attr_name does not exist or there
	 * 		is no rule for this attribute.
	 */
	private function check_attr_spec_rule_disallowed_empty( $node, $attr_name, $attr_spec_rule ) {
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

	/**
	 * Checks to see if the given attribute exists, has a disallowed_domain rule,
	 * and whether or not the value matches the rule.
	 *
	 * Returns:
	 * 	- AMP_Rule_Spec::pass - $attr_name has a value that matches the rule.
	 * 	- AMP_Rule_Spec::fail - $attr_name has a value that does *not* match rule.
	 * 	- AMP_Rule_Spec::not_applicable - $attr_name does not exist or there
	 * 		is no rule for this attribute.
	 */
	private function check_attr_spec_rule_disallowed_domain( $node, $attr_name, $attr_spec_rule ) {
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

	/**
	 * Checks to see if the given attribute exists, has a blacklisted_value_regex rule,
	 * and whether or not the value matches the rule.
	 *
	 * Returns:
	 * 	- AMP_Rule_Spec::pass - $attr_name has a value that matches the rule.
	 * 	- AMP_Rule_Spec::fail - $attr_name has a value that does *not* match rule.
	 * 	- AMP_Rule_Spec::not_applicable - $attr_name does not exist or there
	 * 		is no rule for this attribute.
	 */
	private function check_attr_spec_rule_blacklisted_value_regex( $node, $attr_name, $attr_spec_rule ) {
		if ( isset( $attr_spec_rule[AMP_Rule_Spec::blacklisted_value_regex] ) ) {
			$pattern = '/' . $attr_spec_rule[AMP_Rule_Spec::blacklisted_value_regex] . '/u';
			if ( $node->hasAttribute( $attr_name ) ) {
				$attr_value = $node->getAttribute( $attr_name );
				if ( preg_match( $pattern, $attr_value ) ) {
					return AMP_Rule_Spec::fail;
				} else {
					return AMP_Rule_Spec::pass;
				}
			} elseif ( isset( $attr_spec_rule[AMP_Rule_Spec::alternative_names] ) ) {
				foreach( $attr_spec_rule[AMP_Rule_Spec::alternative_names] as $alternative_name ) {
					if ( $node->hasAttribute( $alternative_name ) ) {
						$attr_value = $node->getAttribute( $alternative_name );
						if ( preg_match( $pattern, $attr_value ) ) {
							return AMP_Rule_Spec::fail;
						} else {
							return AMP_Rule_Spec::pass;
						}
					}
				}
			}
		}
		return AMP_Rule_Spec::not_applicable;
	}

	/**
	 * Return true if the attribute name is valid for this attr_spec, false otherwise.
	 */
	private function is_amp_allowed_attribute( $attr_name, $attr_spec_list ) {
		if ( isset( $this->globally_allowed_attributes[ $attr_name ] ) || 
			isset( $this->layout_allowed_attributes[ $attr_name ] ) ||
			isset( $attr_spec_list[ $attr_name ] ) ) {
			return true;
		} else {
			foreach ( AMP_Rule_Spec::$whitelisted_attr_regex as $whitelisted_attr_regex ) {
				if ( preg_match( $whitelisted_attr_regex, $attr_name ) ) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Return true if the specified node's name is an AMP allowed tag, false otherwise.
	 */
	private function is_amp_allowed_tag( $node ) {
		// Return true if node is on the allowed tags list or if it is a text
		// or comment node.
		return ( ( XML_TEXT_NODE == $node->nodeType ) ||
			isset( $this->allowed_tags[ $node->nodeName ] ) || 
			( XML_COMMENT_NODE == $node->nodeType ) ||
			( XML_CDATA_SECTION_NODE == $node->nodeType ) );
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

	// attr names
	const srcset = 'srcset';

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
	static $node_types_to_remove_if_invalid = array(
		'form',
		'input',
		'link',
		'meta',
		// 'script',
		'style',
	);

	// It is mentioned in the documentation in several places that data-* is
	// generally allowed, but there is no specific rule for it in the protoascii
	// file, so I'm including it here.
	static $whitelisted_attr_regex = array(
		'@^data-[a-zA-Z][\\w:.-]*$@uis',
		'(update|item|pagination)',	// allowed for live reference points
	);

	static $additional_allowed_tags = array(

		// this is an experimental tag with no protoascii
		'amp-share-tracking' => array(
			'attr_spec_list' => array(),
			'tag_spec' => array(),
		),

		// this is needed for some tags such as analytics
		'script' => array(
			'attr_spec_list' => array(
				'type' => array(
					'mandatory' => true,
					'value_casei' => 'text/javascript',
				),
			),
			'tag_spec' => array(),
		),
		'script' => array(
			'attr_spec_list' => array(
				'type' => array(
					'mandatory' => true,
					'value_casei' => 'application/json',
				),
			),
			'tag_spec' => array(),
		),
	);
}
