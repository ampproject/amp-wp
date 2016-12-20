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

		// Check whether this node's tag name is on the AMP whitelist
		if ( ! $this->is_amp_allowed_tag( $node ) ) {
			// If it's not an allowed tag, replace the node with it's children
			$this->replace_node_with_children( $node );
			// Return early since this node no longer exists.
			return;
		}

		// Validate each tag_spec for this node. If the a tag_spec is valid
		//	we'll have to validate the corresponding attr_spec later, so
		//	we keep a list of those.
		$attr_spec_list_to_validate = array();
		foreach ( $this->allowed_tags[ $node->nodeName ] as $rule_spec ) {
			if ( $this->tag_spec_is_valid_for_node( $node, $rule_spec[AMP_Rule_Spec::tag_spec] ) ) {
				$attr_spec_list_to_validate[] = $rule_spec[AMP_Rule_Spec::attr_spec_list];			
			}
		}

		// If there were no valid tag_specs, then $attr_spec_list_to_validate
		//	will be empty and we can remove this node and return.
		if ( empty( $attr_spec_list_to_validate ) ) {
			$node->parentNode->removeChild( $node );
			return;
		}


		// If we made it here, there is at least one attr_spec to validate.
		foreach ( $attr_spec_list_to_validate as $attr_spec_id => $attr_spec ) {
			
			// If we can't validate this attr_spec, remove it from the list.
			if ( false == $this->attr_spec_is_valid_for_node( $node, $attr_spec ) ) {
				unset( $attr_spec_list_to_validate );
			}
		}

		// TODO: Need to handle the case where we have multiple attr_spec lists to validate
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

		$attrs_to_remove = array();
		foreach ( $node->attributes as $attr_name => $attr_node ) {
			// see if this attribute is allowed for this node
			if ( ! $this->is_amp_allowed_attribute( $attr_name, $attr_spec_list ) ) {
				$attrs_to_remove[] = $attr_name;
			
			// if the attribute was allowed, check for a value restriction
			} else {
				if ( ! empty( $attr_spec_list[ $attr_name ] ) ) {
					foreach ( $attr_spec_list[ $attr_name ] as $attr_spec_rule => $attr_spec_rule_value ) {
						$this->process_attr_spec_rules( $attr_spec_rule, $attr_spec_rule_value, $attr_node );
					}
				}

				if ( ! empty( $this->globally_allowed_attributes[ $attr_name ] ) ) {
					foreach ( $this->globally_allowed_attributes[ $attr_name ] as $attr_spec_rule => $attr_spec_rule_value ) {
						$this->process_attr_spec_rules( $attr_spec_rule, $attr_spec_rule_value, $attr_node );
					}
				} 
			}
		}

		// Remove the disllowed attributes
		foreach ( $attrs_to_remove as $attr_name ) {
			$node->removeAttribute( $attr_name );
		}

		return true;
	}

	private function process_attr_spec_rules( $attr_spec_rule, $attr_spec_rule_value, $attr_node ) {
		switch ( $attr_spec_rule ) {
			case AMP_Rule_Spec::blacklisted_value_regex:
				$this->enforce_attr_spec_rule_blacklisted_value_regex( $attr_spec_rule_value, $attr_node );
				break;

			case AMP_Rule_Spec::value_regex:
				$this->enforce_attr_spec_rule_value_regex( $attr_spec_rule_value, $attr_node );
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

abstract class AMP_Rule_Spec_Status {
	const not_done = 0;
	const does_not_apply = 1;
	const passed = 2;
	const failed = 3;
	const bad_test_should_not_be_here = 9999;
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
	const blacklisted_value_regex = 'blacklisted_value_regex';
	const value_regex = 'value_regex';
}
