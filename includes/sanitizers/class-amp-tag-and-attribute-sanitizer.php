<?php

/**
 * Strips the tags and attributes from the content that are not allowed by the AMP spec.
 *
 * Allowed tags array is generated from this protocol buffer:
 *
 *     https://github.com/ampproject/amphtml/blob/master/validator/validator-main.protoascii
 *     by the python script in amp-wp/bin/amp_wp_build.py. See the comment at the top
 *     of that file for instructions to generate class-amp-allowed-tags-generated.php.
 *
 * @todo Need to check the following items that are not yet checked by this sanitizer:
 *
 *     - `also_requires_attr` - if one attribute is present, this requires another.
 *     - `CdataSpec`          - CDATA is not validated or sanitized.
 *     - `ChildTagSpec`       - Places restrictions on the number and type of child tags.
 *     - `if_value_regex`     - if one attribute value matches, this places a restriction
 *	                            on another attribute/value.
 *     - `mandatory_oneof`    - Within the context of the tag, exactly one of the attributes
 *		                        must be present.
 */
class AMP_Tag_And_Attribute_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * @since 0.5
	 *
	 * @var string[]
	 */
	protected $allowed_tags;

	/**
	 * @since 0.5
	 *
	 * @var array[][]
	 */
	protected $globally_allowed_attributes;

	/**
	 * @since 0.5
	 *
	 * @var string[]
	 */
	protected $layout_allowed_attributes;

	/**
	 * @since 0.5
	 *
	 * @var DOMElement[]
	 */
	private $stack = array();

	/**
	 * @since 0.5
	 *
	 * @var array
	 */
	protected $DEFAULT_ARGS = array();

	/**
	 * AMP_Tag_And_Attribute_Sanitizer constructor.
	 *
	 * @since 0.5
	 *
	 * @param DOMDocument $dom
	 * @param array $args
	 */
	public function __construct( $dom, $args = array() ) {
		$this->DEFAULT_ARGS = array(
			'amp_allowed_tags' => AMP_Allowed_Tags_Generated::get_allowed_tags(),
			'amp_globally_allowed_attributes' => AMP_Allowed_Tags_Generated::get_allowed_attributes(),
			'amp_layout_allowed_attributes' => AMP_Allowed_Tags_Generated::get_layout_attributes(),
		);

		parent::__construct( $dom, $args );

		/**
		 * Prepare whitelists
		 */
		$this->allowed_tags = $this->args['amp_allowed_tags'];
		$this->globally_allowed_attributes = $this->args['amp_globally_allowed_attributes'];
		$this->layout_allowed_attributes = $this->args['amp_layout_allowed_attributes'];
	}

	/**
	 * Sanitize the <video> elements from the HTML contained in this instance's DOMDocument.
	 *
	 * @since 0.5
	 */
	public function sanitize() {

		foreach( AMP_Rule_Spec::$additional_allowed_tags as $tag_name => $tag_rule_spec ) {
			$this->allowed_tags[ $tag_name ][] = $tag_rule_spec;
		}

		/**
		 * Add root of content to the stack
		 *
		 * @var DOMElement $body
		 */
		$body = $this->get_body_node();
		$this->stack[] = $body;

		/**
		 * This loop traverses through the DOM tree iteratively.
		 */
		while ( 0 < count( $this->stack ) ) {

			/**
			 * Get the next node to process.
			 */
			$node = array_pop( $this->stack );

			/**
			 * Process this node.
			 */
			$this->process_node( $node );

			/**
			 * Push child nodes onto the stack, if any exist.
			 * @note if node was removed, then it's parentNode value is null.
			 */
			if ( $node->parentNode ) {
				$child = $node->firstChild;
				while ( $child ) {
					$this->stack[] = $child;
					$child = $child->nextSibling;
				}
			}
		}
	}

	/**
	 * Process a node by sanitizing and/or validating it per
	 * @param DOMNode $node
	 */
	private function process_node( $node ) {

		/**
		 * Don't process text or comment nodes
		 */
		if ( ( XML_TEXT_NODE == $node->nodeType ) ||
		     ( XML_COMMENT_NODE == $node->nodeType ) ||
		     ( XML_CDATA_SECTION_NODE == $node->nodeType ) ) {
			return;
		}

		/**
		 * Remove nodes with tags that have not been whitelisted.
		 */
		if ( ! $this->is_amp_allowed_tag( $node ) ) {
			/**
			 * If it's not an allowed tag, replace the node with it's children
			 */
			$this->replace_node_with_children( $node );

			/**
			 * Return early since this node no longer exists.
			 */
			return;
		}

		/**
		 * Compile a list of rule_specs to validate for this node
		 * based on tag name of the node.
		 */
		$rule_spec_list = $rule_spec_list_to_validate = array();
		if ( isset( $this->allowed_tags[ $node->nodeName ] ) ) {
			$rule_spec_list = $this->allowed_tags[ $node->nodeName ];
		}
		foreach ( $rule_spec_list as $id => $rule_spec ) {
			if ( $this->validate_tag_spec_for_node( $node, $rule_spec[AMP_Rule_Spec::TAG_SPEC] ) ) {
				$rule_spec_list_to_validate[ $id ] = $rule_spec;
			}
		}

		/**
		 * If no valid rule_specs exist, then remove this node and return.
		 */
		if ( empty( $rule_spec_list_to_validate ) ) {
			$this->remove_node( $node );
			return;
		}

		/**
		 * The remaining validations all have to do with attributes.
		 */
		$attr_spec_list = array();

		/**
		 * If we have exactly one rule_spec, use it's attr_spec_list
		 * to validate the node's attributes.
		 */
		if ( 1 == count( $rule_spec_list_to_validate ) ) {
			$rule_spec = array_pop( $rule_spec_list_to_validate );
			$attr_spec_list = $rule_spec[AMP_Rule_Spec::ATTR_SPEC_LIST];

		} else {
			/**
			 * If there is more than one valid rule_spec for this node,
			 * then try to deduce which one is intended by inspecting
			 * the node's attributes.
			 */

			/**
			 * Get a score from each attr_spec_list by seeing how many
			 * attributes and values match the node.
			 */
			$attr_spec_scores = array();
			foreach ( $rule_spec_list_to_validate as $spec_id => $rule_spec ) {
				$attr_spec_scores[ $spec_id ] = $this->validate_attr_spec_list_for_node( $node, $rule_spec[AMP_Rule_Spec::ATTR_SPEC_LIST] );
			}

			/**
			 * Get the key(s) to the highest score(s).
			 */
			$spec_ids_sorted = array_keys( $attr_spec_scores, max( $attr_spec_scores ) );

			/**
			 * If there is exactly one attr_spec with a max score, use that one.
			 */
			if ( 1 == count( $spec_ids_sorted ) ) {
				$attr_spec_list = $rule_spec_list_to_validate[ $spec_ids_sorted[0] ][AMP_Rule_Spec::ATTR_SPEC_LIST];
			} else {
				/**
				 * This should not happen very often, but...
				 * If we're here, then we're not sure which spec should be used.
				 * Let's use the top scoring ones.
				 */
				foreach( $spec_ids_sorted as $id ) {
					$attr_spec_list = array_merge( $attr_spec_list, $rule_spec_list_to_validate[ $id ][AMP_Rule_Spec::ATTR_SPEC_LIST] );
				}
			}
		}

		/**
		 * If an attribute is mandatory and we don't have it,
		 * just remove the node and move on.
		 */
		foreach ( $attr_spec_list as $attr_name => $attr_spec_rule_value ) {

			$is_mandatory = isset( $attr_spec_rule_value[ AMP_Rule_Spec::MANDATORY ] )
				? (bool) $attr_spec_rule_value[ AMP_Rule_Spec::MANDATORY ]
				: false;
			if ( ! $is_mandatory ) {
				continue;
			}

			$attribute_exists = $node instanceof DOMElement
			                    && $node->hasAttribute( $attr_name );

			if ( $attribute_exists ) {
				continue;
			}

			$this->remove_node( $node );
			return;

		}

		/**
		 * Remove any remaining disallowed attributes.
		 */
		$this->sanitize_disallowed_attributes_in_node( $node, $attr_spec_list );

		/**
		 * Remove values that don't conform to the attr_spec.
		 */
		$this->sanitize_disallowed_attribute_values_in_node( $node, $attr_spec_list );
	}

	/**
	 * Determines is a node is currently valid per its tag specification.
	 *
	 * Checks to see if a node's placement with the DOM is be valid for the
	 * given tag_spec. If there are restrictions placed on the type of node
	 * that can be an immediate parent or an ancestor of this node, then make
	 * sure those restrictions are met.
	 *
	 * @since 0.5
	 *
	 * @param DOMNode $node
	 * @param array[]|string[] $tag_spec
	 *
	 * @return bool If any of the tests on the restrictions fail return false,
	 *	            otherwise return true.
	 */
	private function validate_tag_spec_for_node( $node, $tag_spec ) {
		if ( ! empty( $tag_spec[AMP_Rule_Spec::MANDATORY_PARENT] ) &&
		     ! $this->has_parent( $node, $tag_spec[AMP_Rule_Spec::MANDATORY_PARENT] ) ) {
			return false;
		}

		if ( ! empty( $tag_spec[AMP_Rule_Spec::DISALLOWED_ANCESTOR] ) ) {
			foreach ( $tag_spec[AMP_Rule_Spec::DISALLOWED_ANCESTOR] as $disallowed_ancestor_node_name ) {
				if ( $this->has_ancestor( $node, $disallowed_ancestor_node_name ) ) {
					return false;
				}
			}
		}

		if ( ! empty( $tag_spec[AMP_Rule_Spec::MANDATORY_ANCESTOR] ) &&
		     ! $this->has_ancestor( $node, $tag_spec[AMP_Rule_Spec::MANDATORY_ANCESTOR] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Checks to see if a spec is potentially valid.
	 *
	 * Checks the given node based on the attributes present in the node.
	 *
	 * @note This can be a very expensive function. Use it sparingly.
	 *
	 * @param DOMNode $node
	 * @param array[] $attr_spec_list
	 *
	 * @return int
	 */
	private function validate_attr_spec_list_for_node( $node, $attr_spec_list ) {

		/**
		 * If node has no attributes there is no point in continuing.
		 */
		if ( ! $node->hasAttributes() ) {
			return 0;
		}

		foreach( $node->attributes as $attr_name => $attr_node ) {
			if ( ! isset( $attr_spec_list[ $attr_name ][AMP_Rule_Spec::ALTERNATIVE_NAMES] ) ) {
				continue;
			}
			foreach( $attr_spec_list[ $attr_name ][AMP_Rule_Spec::ALTERNATIVE_NAMES] as $attr_alt_name ) {
				$attr_spec_list[ $attr_alt_name ] = $attr_spec_list[ $attr_name ];
			}
		}

		if ( ! $node instanceof DOMElement ) {
			/**
			 * A DOMNode is not valid for checks so might
			 * as well bail here is not an DOMElement.
			 */
			return 0;
		}

		$score = 0;

		/**
		 * Iterate through each attribute rule in this attr spec list and run
		 * the series of tests. Each filter is given a `$node`, an `$attr_name`,
		 * and an `$attr_spec_rule`. If the `$attr_spec_rule` seems to be valid
		 * for the given node, then the filter should increment the score by one.
		 */
		foreach ( $attr_spec_list as $attr_name => $attr_spec_rule ) {

			/**
			 * If a mandatory attribute is required, and attribute exists, pass.
			 */
			if ( isset( $attr_spec_rule[AMP_Rule_Spec::MANDATORY] ) ) {
				if ( AMP_Rule_Spec::PASS == $this->check_attr_spec_rule_mandatory( $node, $attr_name, $attr_spec_rule ) ) {
					$score += 1;
				}
			}

			/**
			 * Check 'value' - case sensitive
			 * Given attribute's value must exactly equal value of the rule to pass.
			 */
			if ( isset( $attr_spec_rule[AMP_Rule_Spec::VALUE] ) ) {
				if ( AMP_Rule_Spec::PASS == $this->check_attr_spec_rule_value( $node, $attr_name, $attr_spec_rule ) ) {
					$score += 1;
				}
			}

			/**
			 * Check 'value_regex' - case sensitive regex match
			 * Given attribute's value must be a case insensitive match to regex pattern
			 * specified by the value of rule to pass.
			 */
			if ( isset( $attr_spec_rule[AMP_Rule_Spec::VALUE_REGEX] ) ) {
				if ( AMP_Rule_Spec::PASS == $this->check_attr_spec_rule_value_regex( $node, $attr_name, $attr_spec_rule ) ) {
					$score += 1;
				}
			}

			/**
			 * Check 'value_casei' - case insensitive
			 * Given attribute's value must be a case insensitive match to the value of
			 * the rule to pass.
			 */
			if ( isset( $attr_spec_rule[AMP_Rule_Spec::VALUE_CASEI] ) ) {
				if ( AMP_Rule_Spec::PASS == $this->check_attr_spec_rule_value_casei( $node, $attr_name, $attr_spec_rule ) ) {
					$score += 1;
				}
			}

			/**
			 * Check 'value_regex_casei' - case insensitive regex match
			 * Given attribute's value must be a case insensitive match to the regex
			 * pattern specified by the value of the rule to pass.
			 */
			if ( isset( $attr_spec_rule[AMP_Rule_Spec::VALUE_REGEX_CASEI] ) ) {
				if ( AMP_Rule_Spec::PASS == $this->check_attr_spec_rule_value_regex_casei( $node, $attr_name, $attr_spec_rule ) ) {
					$score += 1;
				}
			}

			/**
			 * If given attribute's value is a URL with a protocol, the protocol must
			 * be in the array specified by the rule's value to pass.
			 */
			if ( isset( $attr_spec_rule[AMP_Rule_Spec::ALLOWED_PROTOCOL] ) ) {
				if ( AMP_Rule_Spec::PASS == $this->check_attr_spec_rule_allowed_protocol( $node, $attr_name, $attr_spec_rule ) ) {
					$score += 1;
				}
			}

			/**
			 * If the given attribute's value is *not* a relative path, and the rule's
			 * value is `false`, then pass.
			 */
			if ( isset( $attr_spec_rule[AMP_Rule_Spec::ALLOW_RELATIVE] ) ) {
				if ( AMP_Rule_Spec::PASS == $this->check_attr_spec_rule_disallowed_relative( $node, $attr_name, $attr_spec_rule ) ) {
					$score += 1;
				}
			}

			/**
			 * If the given attribute's value exists, is non-empty and the rule's value
			 * is false, then pass.
			 */
			if ( isset( $attr_spec_rule[AMP_Rule_Spec::ALLOW_EMPTY] ) ) {
				if ( AMP_Rule_Spec::PASS == $this->check_attr_spec_rule_disallowed_empty( $node, $attr_name, $attr_spec_rule ) ) {
					$score += 1;
				}
			}

			/**
			 * If the given attribute's value is a URL and does not match any of the list
			 * of domains in the value of the rule, then pass.
			 */
			if ( isset( $attr_spec_rule[AMP_Rule_Spec::DISALLOWED_DOMAIN] ) ) {
				if ( AMP_Rule_Spec::PASS == $this->check_attr_spec_rule_disallowed_domain( $node, $attr_name, $attr_spec_rule ) ) {
					$score += 1;
				}
			}

			/**
			 * If the attribute's value exists and does not match the regex specified
			 * by the rule's value, then pass.
			 */
			if ( isset( $attr_spec_rule[AMP_Rule_Spec::BLACKLISTED_VALUE_REGEX] ) ) {
				if ( AMP_Rule_Spec::PASS == $this->check_attr_spec_rule_blacklisted_value_regex( $node, $attr_name, $attr_spec_rule ) ) {
					$score += 1;
				}
			}
		}

		return $score;
	}

	/**
	 * Remove attributes from $node that are not listed in $allowed_attrs.
	 *
	 * @param DOMNode $node
	 * @param array[] $attr_spec_list
	 */
	private function sanitize_disallowed_attributes_in_node( $node, $attr_spec_list ) {

		if ( ! $node instanceof DOMElement ) {
			/**
			 * If $node is only a DOMNode and not a DOMElement we can't
			 * remove an attribute from it anyway.  So bail out now.
			 */
			return;
		}

		/**
		 * @note We can't remove attributes inside the 'foreach' loop without
		 *	     breaking the iteration. So we keep track of the attributes to
		 *       remove in the first loop, then remove them in the second loop.
		 */
		$attrs_to_remove = array();
		foreach ( $node->attributes as $attr_name => $attr_node ) {
			if ( ! $this->is_amp_allowed_attribute( $attr_name, $attr_spec_list ) ) {
				/**
				 * This attribute is not allowed for this node; plan to remove it.
				 */
				$attrs_to_remove[] = $attr_name;
			}
		}

		if ( ! empty( $attrs_to_remove ) ) {
			/**
			 * Ensure we are not removing attributes listed as an alternate
			 * or allowed attributes, e.g. 'srcset' is an alternate for 'src'.
			 */
			foreach ( $attr_spec_list as $attr_name => $attr_spec_rule_value ) {
				if ( isset( $attr_spec_rule_value[AMP_Rule_Spec::ALTERNATIVE_NAMES] ) ) {
					foreach ( $attr_spec_rule_value[AMP_Rule_Spec::ALTERNATIVE_NAMES] as $alternative_name ) {
						$alt_name_keys = array_keys( $attrs_to_remove, $alternative_name, true );
						if ( ! empty( $alt_name_keys ) ) {
							unset( $attrs_to_remove[ $alt_name_keys[0] ] );
						}
					}
				}
			}

			/**
			 * Remove the disallowed attributes
			 */
			foreach ( $attrs_to_remove as $attr_name ) {
				$node->removeAttribute( $attr_name );
			}
		}
	}

	/**
	 * Remove invalid AMP attributes values from $node that have been implicitly disallowed.
	 *
	 * Allowed values are found $this->globally_allowed_attributes and in parameter $attr_spec_list
	 *
	 * @param DOMNode $node
	 * @param array[][] $attr_spec_list
	 */
	private function sanitize_disallowed_attribute_values_in_node( $node, $attr_spec_list ) {

		if ( ! $node instanceof DOMElement ) {
			/**
			 * If $node is only a DOMNode and not a DOMElement we can't
			 * remove an attribute from it anyway.  So bail out now.
			 */
			return;
		}

		$this->_sanitize_disallowed_attribute_values_in_node( $node, $this->globally_allowed_attributes );
		if ( ! empty( $attr_spec_list ) ) {
			$this->_sanitize_disallowed_attribute_values_in_node( $node, $attr_spec_list );
		}
	}

	/**
	 * Remove attributes values from $node that have been disallowed by AMP.
	 *
	 * @see $this->sanitize_disallowed_attribute_values_in_node() which delegates to this method
	 *
	 * @param DOMElement $node
	 * @param array[][] $attr_spec_list
	 */
	private function _sanitize_disallowed_attribute_values_in_node( $node, $attr_spec_list ) {
		$attrs_to_remove = array();

		foreach( $attr_spec_list as $attr_name => $attr_val ) {
			if ( isset( $attr_spec_list[ $attr_name ][AMP_Rule_Spec::ALTERNATIVE_NAMES] ) ) {
				foreach( $attr_spec_list[ $attr_name ][AMP_Rule_Spec::ALTERNATIVE_NAMES] as $attr_alt_name ) {
					$attr_spec_list[ $attr_alt_name ] = $attr_spec_list[ $attr_name ];
				}
			}
		}

		foreach( $node->attributes as $attr_name => $attr_node ) {

			if ( ! isset( $attr_spec_list[$attr_name] ) ) {
				continue;
			}

			$should_remove_node = false;
			$attr_spec_rule = $attr_spec_list[ $attr_name ];

			if ( isset( $attr_spec_rule[AMP_Rule_Spec::VALUE] ) &&
			     AMP_Rule_Spec::FAIL == $this->check_attr_spec_rule_value( $node, $attr_name, $attr_spec_rule ) ) {
				$should_remove_node = true;
			} elseif ( isset( $attr_spec_rule[AMP_Rule_Spec::VALUE_CASEI] ) &&
			           AMP_Rule_Spec::FAIL == $this->check_attr_spec_rule_value_casei( $node, $attr_name, $attr_spec_rule ) ) {
				$should_remove_node = true;
			} elseif ( isset( $attr_spec_rule[AMP_Rule_Spec::VALUE_REGEX] ) &&
			           AMP_Rule_Spec::FAIL == $this->check_attr_spec_rule_value_regex( $node, $attr_name, $attr_spec_rule ) ) {
				$should_remove_node = true;
			} elseif ( isset( $attr_spec_rule[AMP_Rule_Spec::VALUE_REGEX_CASEI] ) &&
			           AMP_Rule_Spec::FAIL == $this->check_attr_spec_rule_value_regex_casei( $node, $attr_name, $attr_spec_rule ) ) {
				$should_remove_node = true;
			} elseif ( isset( $attr_spec_rule[AMP_Rule_Spec::ALLOWED_PROTOCOL] ) &&
			           AMP_Rule_Spec::FAIL == $this->check_attr_spec_rule_allowed_protocol( $node, $attr_name, $attr_spec_rule ) ) {
				$should_remove_node = true;
			} elseif ( isset( $attr_spec_rule[AMP_Rule_Spec::ALLOW_RELATIVE] ) &&
			           AMP_Rule_Spec::FAIL == $this->check_attr_spec_rule_disallowed_relative( $node, $attr_name, $attr_spec_rule ) ) {
				$should_remove_node = true;
			} elseif ( isset( $attr_spec_rule[AMP_Rule_Spec::ALLOW_EMPTY] ) &&
			           AMP_Rule_Spec::FAIL == $this->check_attr_spec_rule_disallowed_empty( $node, $attr_name, $attr_spec_rule ) ) {
				$should_remove_node = true;
			} elseif ( isset( $attr_spec_rule[AMP_Rule_Spec::DISALLOWED_DOMAIN] ) &&
			           AMP_Rule_Spec::FAIL == $this->check_attr_spec_rule_disallowed_domain( $node, $attr_name, $attr_spec_rule ) ) {
				$should_remove_node = true;
			} elseif ( isset( $attr_spec_rule[AMP_Rule_Spec::BLACKLISTED_VALUE_REGEX] ) &&
			           AMP_Rule_Spec::FAIL == $this->check_attr_spec_rule_blacklisted_value_regex( $node, $attr_name, $attr_spec_rule ) ) {
				$should_remove_node = true;
			}

			if ( $should_remove_node ) {
				$is_mandatory =
					isset( $attr_spec_rule[ AMP_Rule_Spec::MANDATORY ] )
						? (bool) $attr_spec_rule[ AMP_Rule_Spec::MANDATORY ]
						: false;

				if ( $is_mandatory ) {
					$this->remove_node( $node );
					return;
				}

				$attrs_to_remove[] = $attr_name;
			}
		}

		/**
		 * Remove the disallowed values
		 */
		foreach ( $attrs_to_remove as $attr_name ) {
			if ( isset( $attr_spec_list[$attr_name][AMP_Rule_Spec::ALLOW_EMPTY] ) &&
			     ( true == $attr_spec_list[$attr_name][AMP_Rule_Spec::ALLOW_EMPTY] ) ) {
				$attr = $node->attributes;
				$attr[ $attr_name ]->value = '';
			} else {
				$node->removeAttribute( $attr_name );
			}
		}
	}

	/**
	 * Check if attribute is mandatory determine whether it exists in $node.
	 *
	 * When checking for the given attribute it also checks valid alternates.
	 *
	 * @param DOMElement $node
	 * @param string $attr_name
	 * @param array[] $attr_spec_rule
	 *
	 * @return string:
	 *      - AMP_Rule_Spec::PASS - $attr_name is mandatory and it exists
	 *      - AMP_Rule_Spec::FAIL - $attr_name is mandatory, but doesn't exist
	 *      - AMP_Rule_Spec::NOT_APPLICABLE - $attr_name is not mandatory
	 */
	private function check_attr_spec_rule_mandatory( $node, $attr_name, $attr_spec_rule ) {
		if ( isset( $attr_spec_rule[AMP_Rule_Spec::MANDATORY] ) &&
		     ( true == $attr_spec_rule[AMP_Rule_Spec::MANDATORY] ) ) {
			if ( $node->hasAttribute( $attr_name ) ) {
				return AMP_Rule_Spec::PASS;
			} else {
				// check if an alternative name list is specified
				if ( isset( $attr_spec_rule[AMP_Rule_Spec::ALTERNATIVE_NAMES] ) ) {
					foreach ( $attr_spec_rule[AMP_Rule_Spec::ALTERNATIVE_NAMES] as $alt_name ) {
						if ( $node->hasAttribute( $alt_name ) ) {
							return AMP_Rule_Spec::PASS;
						}
					}
				}
				return AMP_Rule_Spec::FAIL;
			}
		}
		return AMP_Rule_Spec::NOT_APPLICABLE;
	}

	/**
	 * Check if attribute has a value rule determine if its value is valid.
	 *
	 * Checks for value validity by matches against valid values.
	 *
	 * @param DOMElement $node
	 * @param string $attr_name
	 * @param array[] $attr_spec_rule
	 *
	 * @return string:
	 * 	    - AMP_Rule_Spec::PASS - $attr_name has a value that matches the rule.
	 * 	    - AMP_Rule_Spec::FAIL - $attr_name has a value that does *not* match rule.
	 * 	    - AMP_Rule_Spec::NOT_APPLICABLE - $attr_name does not exist or there
	 * 	                                  	  is no rule for this attribute.
	 */
	private function check_attr_spec_rule_value( $node, $attr_name, $attr_spec_rule ) {
		if ( isset( $attr_spec_rule[AMP_Rule_Spec::VALUE] ) ) {
			if ( $node->hasAttribute( $attr_name ) ) {
				if ( $node->getAttribute( $attr_name ) === $attr_spec_rule[AMP_Rule_Spec::VALUE] ) {
					return AMP_Rule_Spec::PASS;
				} else {
					return AMP_Rule_Spec::FAIL;
				}
			} elseif ( isset( $attr_spec_rule[AMP_Rule_Spec::ALTERNATIVE_NAMES] ) ) {
				foreach ( $attr_spec_rule[AMP_Rule_Spec::ALTERNATIVE_NAMES] as $alternative_name ) {
					if ( $node->hasAttribute( $alternative_name ) ) {
						if ( $node->getAttribute( $alternative_name ) === $attr_spec_rule[AMP_Rule_Spec::VALUE] ) {
							return AMP_Rule_Spec::PASS;
						} else {
							return AMP_Rule_Spec::FAIL;
						}
					}
				}
			}
		}
		return AMP_Rule_Spec::NOT_APPLICABLE;
	}

	/**
	 * Check if attribute has a value rule determine if its value matches ignoring case.
	 *
	 * @param DOMElement $node
	 * @param string $attr_name
	 * @param array[]|string[] $attr_spec_rule
	 *
	 * @return string:
	 * 	    - AMP_Rule_Spec::PASS - $attr_name has a value that matches the rule.
	 * 	    - AMP_Rule_Spec::FAIL - $attr_name has a value that does *not* match rule.
	 * 	    - AMP_Rule_Spec::NOT_APPLICABLE - $attr_name does not exist or there
	 * 		                                  is no rule for this attribute.
	 */
	private function check_attr_spec_rule_value_casei( $node, $attr_name, $attr_spec_rule ) {
		/**
		 * Check 'value_casei' - case insensitive
		 */
		if ( isset( $attr_spec_rule[AMP_Rule_Spec::VALUE_CASEI] ) ) {
			$rule_value = strtolower( $attr_spec_rule[AMP_Rule_Spec::VALUE_CASEI] );
			if ( $node->hasAttribute( $attr_name ) ) {
				$attr_value = strtolower( $node->getAttribute( $attr_name ) );
				if ( $attr_value == $rule_value ) {
					return AMP_Rule_Spec::PASS;
				} else {
					return AMP_Rule_Spec::FAIL;
				}
			} elseif ( isset( $attr_spec_rule[AMP_Rule_Spec::ALTERNATIVE_NAMES] ) ) {
				foreach ( $attr_spec_rule[AMP_Rule_Spec::ALTERNATIVE_NAMES] as $alternative_name ) {
					if ( $node->hasAttribute( $alternative_name ) ) {
						$attr_value = strtolower( $node->getAttribute( $alternative_name ) );
						if ( $attr_value == $rule_value ) {
							return AMP_Rule_Spec::PASS;
						} else {
							return AMP_Rule_Spec::FAIL;
						}
					}
				}
			}
		}
		return AMP_Rule_Spec::NOT_APPLICABLE;
	}

	/**
	 * Check if attribute has a regex value rule determine if it matches.
	 *
	 * @param DOMElement $node
	 * @param string $attr_name
	 * @param array[]|string[] $attr_spec_rule
	 *
	 * @return string:
	 * 	    - AMP_Rule_Spec::PASS - $attr_name has a value that matches the rule.
	 * 	    - AMP_Rule_Spec::FAIL - $attr_name has a value that does *not* match rule.
	 * 	    - AMP_Rule_Spec::NOT_APPLICABLE - $attr_name does not exist or there
	 * 	    	                              is no rule for this attribute.
	 */
	private function check_attr_spec_rule_value_regex( $node, $attr_name, $attr_spec_rule ) {
		/**
		 * check 'value_regex' - case sensitive regex match
		 */
		if ( isset( $attr_spec_rule[AMP_Rule_Spec::VALUE_REGEX] ) && $node->hasAttribute( $attr_name ) ) {
			$rule_value = $attr_spec_rule[AMP_Rule_Spec::VALUE_REGEX];
			/**
			 * @note The regex pattern has '^' and '$' though they are not in the AMP spec. 
			 *       Leaving them out would allow both '_blank' and 'yyy_blankzzz' to be 
			 *       matched by a regex rule of '(_blank|_self|_top)'. As the AMP JS validator 
			 *       only accepts '_blank' we leave it this way for now.
			 */
			if ( preg_match( '@^' . $rule_value . '$@u', $node->getAttribute( $attr_name ) ) ) {
				return AMP_Rule_Spec::PASS;
			} else {
				return AMP_Rule_Spec::FAIL;
			}
		}
		return AMP_Rule_Spec::NOT_APPLICABLE;
	}

	/**
	 * Check if attribute has a case-insensitive regex value rule determine if it matches.
	 *
	 * @param DOMElement $node
	 * @param string $attr_name
	 * @param array[]|string[] $attr_spec_rule
	 *
	 * @return string:
	 * 	    - AMP_Rule_Spec::PASS - $attr_name has a value that matches the rule.
	 * 	    - AMP_Rule_Spec::FAIL - $attr_name has a value that does *not* match rule.
	 * 	    - AMP_Rule_Spec::NOT_APPLICABLE - $attr_name does not exist or there
	 * 	                                      is no rule for this attribute.
	 */
	private function check_attr_spec_rule_value_regex_casei( $node, $attr_name, $attr_spec_rule ) {
		/**
		 * Check 'value_regex_casei' - case insensitive regex match
		 */
		if ( isset( $attr_spec_rule[AMP_Rule_Spec::VALUE_REGEX_CASEI] ) && $node->hasAttribute( $attr_name ) ) {
			$rule_value = $attr_spec_rule[AMP_Rule_Spec::VALUE_REGEX_CASEI];
			/**
			 * See note above regarding the '^' and '$' that are added here.
			 */
			if ( preg_match('/^' . $rule_value . '$/ui', $node->getAttribute( $attr_name ) ) ) {
				return AMP_Rule_Spec::PASS;
			} else {
				return AMP_Rule_Spec::FAIL;
			}
		}
		return AMP_Rule_Spec::NOT_APPLICABLE;
	}

	/**
	 * Check if attribute has a protocol value rule determine if it matches.
	 *
	 * @param DOMElement $node
	 * @param string $attr_name
	 * @param array[]|string[] $attr_spec_rule
	 *
	 * @return string:
	 * 	    - AMP_Rule_Spec::PASS - $attr_name has a value that matches the rule.
	 * 	    - AMP_Rule_Spec::FAIL - $attr_name has a value that does *not* match rule.
	 * 	    - AMP_Rule_Spec::NOT_APPLICABLE - $attr_name does not exist or there
	 * 		                                  is no rule for this attribute.
	 */
	private function check_attr_spec_rule_allowed_protocol( $node, $attr_name, $attr_spec_rule ) {
		if ( isset( $attr_spec_rule[AMP_Rule_Spec::ALLOWED_PROTOCOL] ) ) {
			if ( $node->hasAttribute( $attr_name ) ) {
				$attr_value = $node->getAttribute( $attr_name );
				$attr_value = preg_replace( '/\s*,\s*/', ',', $attr_value );
				$urls_to_test = explode( ',', $attr_value );
				foreach ( $urls_to_test as $url ) {
					/**
					 * This seems to be an acceptable check since the AMP validator
					 * will allow a URL with no protocol to pass validation.
					 */
					if ( $url_scheme = AMP_WP_Utils::parse_url( $url, PHP_URL_SCHEME ) ) {
						if ( ! in_array( strtolower( $url_scheme ), $attr_spec_rule[AMP_Rule_Spec::ALLOWED_PROTOCOL] ) ) {
							return AMP_Rule_Spec::FAIL;
						}
					}
				}
				return AMP_Rule_Spec::PASS;
			} elseif ( isset( $attr_spec_rule[AMP_Rule_Spec::ALTERNATIVE_NAMES] ) ) {
				foreach ( $attr_spec_rule[AMP_Rule_Spec::ALTERNATIVE_NAMES] as $alternative_name ) {
					if ( $node->hasAttribute( $alternative_name ) ) {
						$attr_value = $node->getAttribute( $alternative_name );
						$attr_value = preg_replace( '/\s*,\s*/', ',', $attr_value );
						$urls_to_test = explode( ',', $attr_value );
						foreach ( $urls_to_test as $url ) {
							/**
							 * This seems to be an acceptable check since the AMP validator
							 * 	will allow a URL with no protocol to pass validation.
							 */
							if ( $url_scheme = AMP_WP_Utils::parse_url( $url, PHP_URL_SCHEME ) ) {
								if ( ! in_array( strtolower( $url_scheme ), $attr_spec_rule[AMP_Rule_Spec::ALLOWED_PROTOCOL] ) ) {
									return AMP_Rule_Spec::FAIL;
								}
							}
						}
						return AMP_Rule_Spec::PASS;
					}
				}
			}
		}
		return AMP_Rule_Spec::NOT_APPLICABLE;
	}

	/**
	 * Check if attribute has disallowed relative value rule determine if disallowed relative value matches.
	 *
	 * @param DOMElement $node
	 * @param string $attr_name
	 * @param array[]|string[] $attr_spec_rule
	 *
	 * @return string:
	 * 	    - AMP_Rule_Spec::PASS - $attr_name has a value that matches the rule.
	 * 	    - AMP_Rule_Spec::FAIL - $attr_name has a value that does *not* match rule.
	 * 	    - AMP_Rule_Spec::NOT_APPLICABLE - $attr_name does not exist or there
	 * 	    	                              is no rule for this attribute.
	 */
	private function check_attr_spec_rule_disallowed_relative( $node, $attr_name, $attr_spec_rule ) {
		if ( isset( $attr_spec_rule[AMP_Rule_Spec::ALLOW_RELATIVE] ) &&
		     ( false == $attr_spec_rule[AMP_Rule_Spec::ALLOW_RELATIVE] ) ) {
			if ( $node->hasAttribute( $attr_name ) ) {
				$attr_value = $node->getAttribute( $attr_name );
				$attr_value = preg_replace( '/\s*,\s*/', ',', $attr_value );
				$urls_to_test = explode( ',', $attr_value );
				foreach ( $urls_to_test as $url ) {
					$parsed_url = AMP_WP_Utils::parse_url( $url );
					/**
					 *  The JS AMP validator seems to consider 'relative' to mean
					 * 	*protocol* relative, not *host* relative for this rule. So,
					 * 	a url with an empty 'scheme' is considered "relative" by AMP.
					 * 	ie. '//domain.com/path' and '/path' should both be considered
					 * 	relative for purposes of AMP validation.
					 */
					if ( empty( $parsed_url['scheme'] ) ) {
						return AMP_Rule_Spec::FAIL;
					}
				}
				return AMP_Rule_Spec::PASS;
			} elseif ( isset( $attr_spec_rule[AMP_Rule_Spec::ALTERNATIVE_NAMES] ) ) {
				foreach ( $attr_spec_rule[AMP_Rule_Spec::ALTERNATIVE_NAMES] as $alternative_name ) {
					if ( $node->hasAttribute( $alternative_name ) ) {
						$attr_value = $node->getAttribute( $alternative_name );
						$attr_value = preg_replace( '/\s*,\s*/', ',', $attr_value );
						$urls_to_test = explode( ',', $attr_value );
						foreach ( $urls_to_test as $url ) {
							$parsed_url = AMP_WP_Utils::parse_url( $url );
							if ( empty( $parsed_url['scheme'] ) ) {
								return AMP_Rule_Spec::FAIL;
							}
						}
					}
				}
				return AMP_Rule_Spec::PASS;
			}
		}
		return AMP_Rule_Spec::NOT_APPLICABLE;
	}

	/**
	 * Check if attribute has disallowed empty value rule determine if value is empty.
	 *
	 * @param DOMElement $node
	 * @param string $attr_name
	 * @param array[]|string[] $attr_spec_rule
	 *
	 * @return string:
	 * 	    - AMP_Rule_Spec::PASS - $attr_name has a value that matches the rule.
	 * 	    - AMP_Rule_Spec::FAIL - $attr_name has a value that does *not* match rule.
	 * 	    - AMP_Rule_Spec::NOT_APPLICABLE - $attr_name does not exist or there
	 * 	    	                              is no rule for this attribute.
	 */
	private function check_attr_spec_rule_disallowed_empty( $node, $attr_name, $attr_spec_rule ) {
		if ( isset( $attr_spec_rule[AMP_Rule_Spec::ALLOW_EMPTY] ) &&
		     ( false == $attr_spec_rule[AMP_Rule_Spec::ALLOW_EMPTY] ) &&
		     $node->hasAttribute( $attr_name ) ) {
			$attr_value = $node->getAttribute( $attr_name );
			if ( empty( $attr_value ) ) {
				return AMP_Rule_Spec::FAIL;
			}
			return AMP_Rule_Spec::PASS;
		}
		return AMP_Rule_Spec::NOT_APPLICABLE;
	}

	/**
	 * Check if attribute has disallowed domain value rule determine if value matches.
	 *
	 * @param DOMElement $node
	 * @param string $attr_name
	 * @param array[]|string[] $attr_spec_rule
	 *
	 * @return string:
	 * 	    - AMP_Rule_Spec::PASS - $attr_name has a value that matches the rule.
	 * 	    - AMP_Rule_Spec::FAIL - $attr_name has a value that does *not* match rule.
	 * 	    - AMP_Rule_Spec::NOT_APPLICABLE - $attr_name does not exist or there
	 * 	    	                              is no rule for this attribute.
	 */
	private function check_attr_spec_rule_disallowed_domain( $node, $attr_name, $attr_spec_rule ) {
		if ( isset( $attr_spec_rule[AMP_Rule_Spec::DISALLOWED_DOMAIN] ) &&
		     $node->hasAttribute( $attr_name ) ) {
			$attr_value = $node->getAttribute( $attr_name );
			$url_domain = AMP_WP_Utils::parse_url( $attr_value, PHP_URL_HOST );
			if ( ! empty( $url_domain ) ) {
				foreach ( $attr_spec_rule[AMP_Rule_Spec::DISALLOWED_DOMAIN] as $disallowed_domain ) {
					if ( strtolower( $url_domain ) == strtolower( $disallowed_domain ) ) {
						/**
						 * Found a disallowed domain, fail validation.
						 */
						return AMP_Rule_Spec::FAIL;
					}
				}
				return AMP_Rule_Spec::PASS;
			}
		}
		return AMP_Rule_Spec::NOT_APPLICABLE;
	}

	/**
	 * Check if attribute has blacklisted value via regex match determine if value matches.
	 *
	 * @since 0.5
	 *
	 * @param DOMElement $node
	 * @param string $attr_name
	 * @param array[]|string[] $attr_spec_rule
	 *
	 * @return string:
	 * 	    - AMP_Rule_Spec::PASS - $attr_name has a value that matches the rule.
	 * 	    - AMP_Rule_Spec::FAIL - $attr_name has a value that does *not* match rule.
	 * 	    - AMP_Rule_Spec::NOT_APPLICABLE - $attr_name does not exist or there
	 * 	    	                              is no rule for this attribute.
	 */
	private function check_attr_spec_rule_blacklisted_value_regex( $node, $attr_name, $attr_spec_rule ) {
		if ( isset( $attr_spec_rule[AMP_Rule_Spec::BLACKLISTED_VALUE_REGEX] ) ) {
			$pattern = '/' . $attr_spec_rule[AMP_Rule_Spec::BLACKLISTED_VALUE_REGEX] . '/u';
			if ( $node->hasAttribute( $attr_name ) ) {
				$attr_value = $node->getAttribute( $attr_name );
				if ( preg_match( $pattern, $attr_value ) ) {
					return AMP_Rule_Spec::FAIL;
				} else {
					return AMP_Rule_Spec::PASS;
				}
			} elseif ( isset( $attr_spec_rule[AMP_Rule_Spec::ALTERNATIVE_NAMES] ) ) {
				foreach( $attr_spec_rule[AMP_Rule_Spec::ALTERNATIVE_NAMES] as $alternative_name ) {
					if ( $node->hasAttribute( $alternative_name ) ) {
						$attr_value = $node->getAttribute( $alternative_name );
						if ( preg_match( $pattern, $attr_value ) ) {
							return AMP_Rule_Spec::FAIL;
						} else {
							return AMP_Rule_Spec::PASS;
						}
					}
				}
			}
		}
		return AMP_Rule_Spec::NOT_APPLICABLE;
	}

	/**
	 * Determine if the supplied attribute name is allowed for AMP.
	 *
	 * @since 0.5
	 *
	 * @param string $attr_name
	 * @param array[]|string[] $attr_spec_list
	 * @return bool Return true if attribute name is valid for this attr_spec_list, false otherwise.
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
	 * Determine if the supplied $node's HTML tag is allowed for AMP.
	 *
	 * @since 0.5
	 *
	 * @param DOMNode $node
	 * @return bool Return true if the specified node's name is an AMP allowed tag, false otherwise.
	 */
	private function is_amp_allowed_tag( $node ) {
		if ( ! $node instanceof DOMElement ) {
			return false;
		}
		/**
		 * Return true if node is an allowed tags or is a text or comment node.
		 */
		return ( ( XML_TEXT_NODE == $node->nodeType ) ||
		         isset( $this->allowed_tags[ $node->nodeName ] ) ||
		         ( XML_COMMENT_NODE == $node->nodeType ) ||
		         ( XML_CDATA_SECTION_NODE == $node->nodeType ) );
	}

	/**
	 * Determine if the supplied $node has a parent with the specified tag name.
	 *
	 * @since 0.5
	 *
	 * @param DOMNode $node
	 * @param string $parent_tag_name
	 * @return bool Return true if given node has direct parent with the given name, false otherwise.
	 */
	private function has_parent( $node, $parent_tag_name ) {
		if ( $node && $node->parentNode && ( $node->parentNode->nodeName == $parent_tag_name ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Determine if the supplied $node has an ancestor with the specified tag name.
	 *
	 * @since 0.5
	 *
	 * @param DOMNode $node
	 * @param string $ancestor_tag_name
	 * @return bool Return true if given node has any ancestor with the give name, false otherwise.
	 */
	private function has_ancestor( $node, $ancestor_tag_name ) {
		if ( $this->get_ancestor_with_tag_name( $node, $ancestor_tag_name ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Get the first ancestor node matching the specified tag name for the supplied $node.
	 *
	 * @since 0.5
	 *
	 * @param DOMNode $node
	 * @param string $ancestor_tag_name
	 * @return DOMNode|null Returns an ancestor node for the name specified, or null if not found.
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
	 * Replaces the given node with it's child nodes, if any
	 *
	 * Also adds them to the stack for processing by the sanitize() function.
	 *
	 * @since 0.3.3
	 *
	 * @param DOMNode $node
	 */
	private function replace_node_with_children( $node ) {

		if ( ! $node->hasChildNodes() || ! $node->parentNode ) {
			/**
			 * If node has no children or no parent, just remove the node.
			 */
			$this->remove_node( $node );

		} else {
			/**
			 * If node has children, replace it with them and push children onto stack
			 *
			 * Create a DOM fragment to hold the children
			 */
			$fragment = $this->dom->createDocumentFragment();

			/**
			 * Add all children to fragment/stack
			 */
			$child = $node->firstChild;
			while( $child ) {
				$fragment->appendChild( $child );
				$this->stack[] = $child;
				$child = $node->firstChild;
			}

			/**
			 * Replace node with fragment
			 */
			$node->parentNode->replaceChild( $fragment, $node );

		}
	}

	/**
	 * Removes a node from its parent node.
	 *
	 * If removing the node makes the parent node empty, then it will remove the parent
	 * too. It will Continue until a non-empty parent or the 'body' element is reached.
	 *
	 * @since 0.5
	 *
	 * @param DOMNode $node
	 */
	private function remove_node( $node ) {
		/**
		 * @var DOMNode $parent
		 */
		$parent = $node->parentNode;
		if ( $node && $parent ) {
			$parent->removeChild( $node );
		}
		while( $parent && ! $parent->hasChildNodes() && 'body' !== $parent->nodeName ) {
			$node = $parent;
			$parent = $parent->parentNode;
			if ( $parent ) {
				$parent->removeChild( $node );
			}
		}
	}
}

