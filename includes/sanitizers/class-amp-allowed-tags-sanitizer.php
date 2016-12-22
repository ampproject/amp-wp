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

		// This loop traverses through the DOM tree iteratively.
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
				unset( $attr_spec_list_to_validate[$attr_spec_id] );
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

		// 5a) This is my solution to the hypothetical case where we have multiple
		//	attr_specs that are still valid for this node: merge the attr_spec_lists
		//	and the remaining checks will operate on the merged list.
		$attr_spec_list_merged = array();
		foreach ( $attr_spec_list_to_validate as $attr_spec_id => $attr_spec ) {
			$attr_spec_list_merged = array_merge($attr_spec, $attr_spec_list_merged);
		}

		// 6) Remove any remaining disallowed attributes.
		$this->remove_disallowed_attributes_from_node( $node, $attr_spec_list_merged );

		// 7) Remove any blacklisted attribute values.
		$this->remove_blacklisted_attribute_values( $node, $attr_spec_list_merged );
	}

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

		// Iterate through each attribute rule in ths attr spec list and run
		//	the series of tests 
		foreach ( $attr_spec_list as $attr_name => $attr_spec_rule ) {

			// 1) If a mandatory attribute doesn't exist, fail validation
			if ( isset( $attr_spec_rule[AMP_Rule_Spec::mandatory] ) &&
				( true == $attr_spec_rule[AMP_Rule_Spec::mandatory] ) ) {
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
						// If no alternate names exist, validation failed.
						return false;
					}
				}
			}

			// 2) Check to make sure that this attribute meets any restrictions
			//	on any attribute value restrictions. If a property exists, but 
			//	doesn't have a required value, fail validation.

			// 2a) check 'value' - case sensitive
			if ( isset( $attr_spec_rule[AMP_Rule_Spec::value] ) && $node->hasAttribute( $attr_name ) ) {
				if ( ! ( $node->getAttribute( $attr_name ) == $attr_spec_rule[AMP_Rule_Spec::value] ) ) {
					return false;
				}
			}

			// 2b)check 'value_casei' - case insensitive
			if ( isset( $attr_spec_rule[AMP_Rule_Spec::value_casei] ) && $node->hasAttribute( $attr_name ) ) {
				$attr_value = strtolower( $node->getAttribute( $attr_name ) );
				$rule_value = strtolower( $attr_spec_rule[AMP_Rule_Spec::value_casei] );
				if ( ! ( $attr_value == $rule_value ) ) {
					return false;
				}
			}

			// 2c)check 'value_regex' - case sensitive regex match
			if ( isset( $attr_spec_rule[AMP_Rule_Spec::value_regex] ) && $node->hasAttribute( $attr_name ) ) {
				$attr_value = $node->getAttribute( $attr_name );
				$rule_value = $attr_spec_rule[AMP_Rule_Spec::value_regex];
				// Note: I added in the '^' and '$' to the regex pattern even though
				//	they weren't in the AMP spec. But leaving them out would allow
				//	both '_blank' and 'yyy_blankzzz' to be matched  by a regex spec of
				//	'(_blank|_self|_top)'. The AMP JS validator only accepts '_blank',
				//	so I'm leaving it this way for now.
				if ( ! preg_match('/^' . $rule_value . '$/u', $attr_value) ) {
					return false;
				}
			}

			// 2d)check 'value_regex_casei' - case insensitive regex match
			if ( isset( $attr_spec_rule[AMP_Rule_Spec::value_regex_casei] ) && $node->hasAttribute( $attr_name ) ) {
				$attr_value = $node->getAttribute( $attr_name );
				$rule_value = $attr_spec_rule[AMP_Rule_Spec::value_regex_casei];
				// See note on 2c) above regarding the '^' and '$' that I added.
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
					// The 'allowed_protocol' rule contains an array of allowed protocols.
					foreach ( $attr_spec_rule[AMP_Rule_Spec::allowed_protocol] as $allowed_protocol ) {
						if ( strtolower( $url_scheme ) == strtolower( $allowed_protocol ) ) {
							// found an acceptable protocol
							$found = true;
							break;
						}
					}
					if ( ! $found ) {
						// If we're here, then there was a protocol specified,
						//	but it wasn't allowed. Fail vaildation.
						return false;
					}
				}
			}

			// 4) If allow_relative is specified as false and url is relative, then fail.
			if ( isset( $attr_spec_rule[AMP_Rule_Spec::allow_relative] ) &&
				 ( false == $attr_spec_rule[AMP_Rule_Spec::allow_relative] ) &&
				 $node->hasAttribute( $attr_name ) ) {
				$attr_value = $node->getAttribute( $attr_name );
				$parsed_url = parse_url( $attr_value );
				// It doesn't seem to be specified anywhere, but the JS AMP validator
				//	seems to consider 'relative' to mean *protocol* relative, not 
				//	*host* relative for this rule.  So, a url with an empty
				//	'scheme' is considered "relative" by AMP.
				// 	ie. '//domain.com/path' and '/path' should both be considered
				//	relative for purposes of AMP validation.
				if ( empty( $parsed_url['scheme'] ) ) {
				}
			}

			// 5) If attribute exists and is empty, but empty is not allowed, fail.
			if ( isset( $attr_spec_rule[AMP_Rule_Spec::allow_empty] ) &&
				( false == $attr_spec_rule[AMP_Rule_Spec::allow_empty] ) &&
				 $node->hasAttribute( $attr_name ) ) {
				$attr_value = $node->getAttribute( $attr_name );
				if ( empty( $attr_value ) ) {
					return false;
				}
			}

			// 6) If attribute exists, but has a disallowed domain, fail.
			if ( isset( $attr_spec_rule[AMP_Rule_Spec::disallowed_domain] ) &&
				$node->hasAttribute( $attr_name ) ) {
				$attr_value = $node->getAttribute( $attr_name );
				$url_domain = parse_url( $attr_value, PHP_URL_HOST );
				if ( ! empty( $url_domain ) ) {
					foreach ( $attr_spec_rule[AMP_Rule_Spec::disallowed_domain] as $disallowed_domain ) {
						if ( strtolower( $url_domain ) == strtolower( $disallowed_domain ) ) {
							// Found a disallowed domain, fail validation.
							return false;
						}
					}
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

		// Make sure we're not removing an attribtue that is listed as an alternate
		//	for some other allowed attribtue. ex. 'srcset' is an alternate for 'src'.
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

	/**
	 * If a node has an attribute that matches a blacklisted regex value, then
	 * that value will be removed from the attribute on this node.
	 */
	private function remove_blacklisted_attribute_values( $node, $attr_spec_list ) {
		foreach( $node->attributes as $attr_name => $attr_node ) {
			if ( isset( $attr_spec_list[ $attr_name ][AMP_Rule_Spec::blacklisted_value_regex] ) ) {
				// Note: Unlike the note on step 2c in attr_spec_is_valid_for_node(),
				//	I am *not* adding the '^' and '$' to the regex patternc because
				//	the AMP JS validator doesn't allow either '__amp_source_origin'
				//	or 'AAA__amp_source_originZZZ', for example.
				$pattern = '/' . $attr_spec_list[ $attr_name ][AMP_Rule_Spec::blacklisted_value_regex] . '/u';
				if ( preg_match( $pattern, $attr_node->value ) ) {
					$attr_node->value = '';
				}
			}
		}
	}

	/**
	 * Below are some utility functions to search and manipulate the DOM.
	 */

	/**
	 * Return true if the attribute name valid for this attr_spec, false otherwise.
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
			$node->parentNode->removeChild( $node );
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
	//	removed. If it isn't listed here, it will be replaced by its children.
	//	...mostly because any children will be non-functional without this
	//	parent.
	//	
	// TODO: There are other nodes that should probably be listed here as well.
	//	I should probably go through the whole spec and evaluate them all.
	const node_types_to_remove_if_invalid = array (
		'form',
		'input',
		'link',
		'meta',
		'script',
		'style',
	);

	// This is here because these tags are not listed in either the AMP global
	//	attributes or the attr_spec_list for elements such as 'amp-img'.
	//
	// I decided to add them here instead of hard-coding them into amp-wp-build.py
	//	because I want the generated file to accurately reflect the protoascii
	//	file it was built from as much as possible.
	const tags_allowed_for_styling = array(
		'height' => array(),
		'layout' => array(),
		'sizes' => array(),
		'width' => array(),
	);
}
