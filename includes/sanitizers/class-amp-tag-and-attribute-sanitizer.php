<?php
/**
 * Class AMP_Tag_And_Attribute_Sanitizer
 *
 * @package AMP
 */

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
 *     - `ChildTagSpec`       - Places restrictions on the number and type of child tags.
 *     - `if_value_regex`     - if one attribute value matches, this places a restriction
 *                              on another attribute/value.
 *     - `mandatory_oneof`    - Within the context of the tag, exactly one of the attributes
 *                              must be present.
 */
class AMP_Tag_And_Attribute_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Allowed tags.
	 *
	 * @since 0.5
	 *
	 * @var string[]
	 */
	protected $allowed_tags;

	/**
	 * Globally-allowed attributes.
	 *
	 * @since 0.5
	 *
	 * @var array[][]
	 */
	protected $globally_allowed_attributes;

	/**
	 * Layout-allowed attributes.
	 *
	 * @since 0.5
	 *
	 * @var string[]
	 */
	protected $layout_allowed_attributes;

	/**
	 * Mapping of alternative names back to their primary names.
	 *
	 * @since 0.7
	 * @var array
	 */
	protected $rev_alternate_attr_name_lookup = array();

	/**
	 * Stack.
	 *
	 * @since 0.5
	 *
	 * @var DOMElement[]
	 */
	private $stack = array();

	/**
	 * Default args.
	 *
	 * @since 0.5
	 *
	 * @var array
	 */
	protected $DEFAULT_ARGS = array();

	/**
	 * AMP script components that are discovered being required through sanitization.
	 *
	 * @var string[]
	 */
	protected $script_components = array();

	/**
	 * Keep track of nodes that should not be replaced to prevent duplicated validation errors since sanitization is rejected.
	 *
	 * @var array
	 */
	protected $should_not_replace_nodes = array();

	/**
	 * AMP_Tag_And_Attribute_Sanitizer constructor.
	 *
	 * @since 0.5
	 *
	 * @param DOMDocument $dom  DOM.
	 * @param array       $args Args.
	 */
	public function __construct( $dom, $args = array() ) {
		$this->DEFAULT_ARGS = array(
			'amp_allowed_tags'                => AMP_Allowed_Tags_Generated::get_allowed_tags(),
			'amp_globally_allowed_attributes' => AMP_Allowed_Tags_Generated::get_allowed_attributes(),
			'amp_layout_allowed_attributes'   => AMP_Allowed_Tags_Generated::get_layout_attributes(),
			'amp_bind_placeholder_prefix'     => AMP_DOM_Utils::get_amp_bind_placeholder_prefix(),
		);

		parent::__construct( $dom, $args );

		if ( ! empty( $this->args['allow_dirty_styles'] ) ) {

			// Allow style attribute on all elements.
			$this->args['amp_globally_allowed_attributes']['style'] = array();

			// Allow style elements.
			$this->args['amp_allowed_tags']['style'][] = array(
				'attr_spec_list' => array(
					'type' => array(
						'value_casei' => 'text/css',
					),
				),
				'cdata'          => array(),
				'tag_spec'       => array(
					'spec_name' => 'style for Customizer preview',
				),
			);

			// Allow stylesheet links.
			$this->args['amp_allowed_tags']['link'][] = array(
				'attr_spec_list' => array(
					'async'       => array(),
					'crossorigin' => array(),
					'href'        => array(
						'mandatory' => true,
					),
					'integrity'   => array(),
					'media'       => array(),
					'rel'         => array(
						'dispatch_key' => 2,
						'mandatory'    => true,
						'value_casei'  => 'stylesheet',
					),
					'type'        => array(
						'value_casei' => 'text/css',
					),
				),
				'tag_spec'       => array(
					'spec_name' => 'link rel=stylesheet for Customizer preview', // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
				),
			);
		}

		// Allow scripts if requested.
		if ( ! empty( $this->args['allow_dirty_scripts'] ) ) {
			$this->args['amp_allowed_tags']['script'][] = array(
				'attr_spec_list' => array(
					'type'  => array(),
					'src'   => array(),
					'async' => array(),
					'defer' => array(),
				),
				'cdata'          => array(),
				'tag_spec'       => array(
					'spec_name' => 'scripts for Customizer preview',
				),
			);
		}

		// Prepare whitelists.
		$this->allowed_tags = $this->args['amp_allowed_tags'];
		foreach ( AMP_Rule_Spec::$additional_allowed_tags as $tag_name => $tag_rule_spec ) {
			$this->allowed_tags[ $tag_name ][] = $tag_rule_spec;
		}

		// @todo Do the same for body when !use_document_element?
		if ( ! empty( $this->args['use_document_element'] ) ) {
			foreach ( $this->allowed_tags['html'] as &$rule_spec ) {
				unset( $rule_spec[ AMP_Rule_Spec::TAG_SPEC ][ AMP_Rule_Spec::MANDATORY_PARENT ] );
			}
		}

		foreach ( $this->allowed_tags as &$tag_specs ) {
			foreach ( $tag_specs as &$tag_spec ) {
				if ( isset( $tag_spec[ AMP_Rule_Spec::ATTR_SPEC_LIST ] ) ) {
					$tag_spec[ AMP_Rule_Spec::ATTR_SPEC_LIST ] = $this->process_alternate_names( $tag_spec[ AMP_Rule_Spec::ATTR_SPEC_LIST ] );
				}
			}
		}
		$this->globally_allowed_attributes = $this->process_alternate_names( $this->args['amp_globally_allowed_attributes'] );
		$this->layout_allowed_attributes   = $this->process_alternate_names( $this->args['amp_layout_allowed_attributes'] );
	}

	/**
	 * Return array of values that would be valid as an HTML `script` element.
	 *
	 * Array keys are AMP element names and array values are their respective
	 * Javascript URLs from https://cdn.ampproject.org
	 *
	 * @since 0.7
	 * @see amp_register_default_scripts()
	 *
	 * @return array() Returns component name as array key and true as value (or JavaScript URL string),
	 *                  respectively. When true then the default component script URL will be used.
	 *                  Will return an empty array if sanitization has yet to be run
	 *                  or if it did not find any HTML elements to convert to AMP equivalents.
	 */
	public function get_scripts() {
		return array_fill_keys( array_unique( $this->script_components ), true );
	}

	/**
	 * Process alternative names in attribute spec list.
	 *
	 * @since 0.7
	 *
	 * @param array $attr_spec_list Attribute spec list.
	 * @return array Modified attribute spec list.
	 */
	private function process_alternate_names( $attr_spec_list ) {
		foreach ( $attr_spec_list as $attr_name => &$attr_spec ) {
			if ( '[' === $attr_name[0] ) {
				$placeholder_attr_name = $this->args['amp_bind_placeholder_prefix'] . trim( $attr_name, '[]' );
				if ( ! isset( $attr_spec[ AMP_Rule_Spec::ALTERNATIVE_NAMES ] ) ) {
					$attr_spec[ AMP_Rule_Spec::ALTERNATIVE_NAMES ] = array();
				}
				$attr_spec[ AMP_Rule_Spec::ALTERNATIVE_NAMES ][] = $placeholder_attr_name;
			}

			// Save all alternative names in lookup to improve performance.
			if ( isset( $attr_spec[ AMP_Rule_Spec::ALTERNATIVE_NAMES ] ) ) {
				foreach ( $attr_spec[ AMP_Rule_Spec::ALTERNATIVE_NAMES ] as $alternative_name ) {
					$this->rev_alternate_attr_name_lookup[ $alternative_name ] = $attr_name;
				}
			}
		}
		return $attr_spec_list;
	}

	/**
	 * Sanitize the elements from the HTML contained in this instance's DOMDocument.
	 *
	 * @since 0.5
	 */
	public function sanitize() {

		// Add root of content to the stack.
		$this->stack[] = $this->root_element;

		/**
		 * This loop traverses through the DOM tree iteratively.
		 */
		while ( ! empty( $this->stack ) ) {

			// Get the next node to process.
			$node = array_shift( $this->stack );

			/**
			 * Process this node.
			 */
			$this->process_node( $node );

			/*
			 * Push child nodes onto the stack, if any exist.
			 * if node was removed, then it's parentNode value is null.
			 */
			if ( $node->parentNode ) {
				$child = $node->firstChild;
				while ( $child ) {
					$this->stack[] = $child;
					$child         = $child->nextSibling;
				}
			}
		}
	}

	/**
	 * Augment rule spec for validation.
	 *
	 * @since 1.0
	 *
	 * @param DOMElement $node      Node.
	 * @param array      $rule_spec Rule spec.
	 * @return array Augmented rule spec.
	 */
	private function get_rule_spec_list_to_validate( $node, $rule_spec ) {

		// Expand extension_spec into a set of attr_spec_list.
		if ( isset( $rule_spec[ AMP_Rule_Spec::TAG_SPEC ]['extension_spec'] ) ) {
			$extension_spec = $rule_spec[ AMP_Rule_Spec::TAG_SPEC ]['extension_spec'];
			$custom_attr    = 'amp-mustache' === $extension_spec['name'] ? 'custom-template' : 'custom-element';

			$rule_spec[ AMP_Rule_Spec::ATTR_SPEC_LIST ][ $custom_attr ] = array(
				AMP_Rule_Spec::VALUE     => $extension_spec['name'],
				AMP_Rule_Spec::MANDATORY => true,
			);

			$versions = array_unique( array_merge(
				isset( $extension_spec['allowed_versions'] ) ? $extension_spec['allowed_versions'] : array(),
				isset( $extension_spec['version'] ) ? $extension_spec['version'] : array()
			) );

			$rule_spec[ AMP_Rule_Spec::ATTR_SPEC_LIST ]['src'] = array(
				AMP_Rule_Spec::VALUE_REGEX => implode( '', array(
					'^',
					preg_quote( 'https://cdn.ampproject.org/v0/' . $extension_spec['name'] . '-' ),
					'(' . implode( '|', $versions ) . ')',
					'\.js$',
				) ),
			);
		}

		// Augment the attribute list according to the parent's reference points, if it has them.
		if ( ! empty( $node->parentNode ) && isset( $this->allowed_tags[ $node->parentNode->nodeName ] ) ) {
			foreach ( $this->allowed_tags[ $node->parentNode->nodeName ] as $parent_rule_spec ) {
				if ( empty( $parent_rule_spec[ AMP_Rule_Spec::TAG_SPEC ]['reference_points'] ) ) {
					continue;
				}
				foreach ( $parent_rule_spec[ AMP_Rule_Spec::TAG_SPEC ]['reference_points'] as $reference_point_spec_name => $reference_point_spec_instance_attrs ) {
					$reference_point = AMP_Allowed_Tags_Generated::get_reference_point_spec( $reference_point_spec_name );
					if ( empty( $reference_point[ AMP_Rule_Spec::ATTR_SPEC_LIST ] ) ) {
						/*
						 * See special case for amp-selector in AMP_Tag_And_Attribute_Sanitizer::is_amp_allowed_attribute()
						 * where its reference point applies to any descendant elements, not just direct children.
						 */
						continue;
					}
					foreach ( $reference_point[ AMP_Rule_Spec::ATTR_SPEC_LIST ] as $attr_name => $reference_point_spec_attr ) {
						$reference_point_spec_attr = array_merge(
							$reference_point_spec_attr,
							$reference_point_spec_instance_attrs
						);

						/*
						 * Ignore mandatory constraint for now since this would end up causing other sibling children
						 * getting removed due to missing a mandatory attribute. To sanitize this it would require
						 * higher-level processing to look at an element's surrounding context, similar to how the
						 * sanitizer does not yet handle the mandatory_oneof constraint.
						 */
						unset( $reference_point_spec_attr['mandatory'] );

						$rule_spec[ AMP_Rule_Spec::ATTR_SPEC_LIST ][ $attr_name ] = $reference_point_spec_attr;
					}
				}
			}
		}
		return $rule_spec;
	}

	/**
	 * Process a node by checking if an element and its attributes are valid, and removing them when invalid.
	 *
	 * Attributes which are not valid are removed. Elements which are not allowed are also removed,
	 * including elements which miss mandatory attributes.
	 *
	 * @param DOMNode $node Node.
	 */
	private function process_node( $node ) {

		// Don't process text or comment nodes.
		if ( XML_TEXT_NODE === $node->nodeType || XML_COMMENT_NODE === $node->nodeType || XML_CDATA_SECTION_NODE === $node->nodeType ) {
			return;
		}

		// Remove nodes with tags that have not been whitelisted.
		if ( ! $this->is_amp_allowed_tag( $node ) ) {

			// If it's not an allowed tag, replace the node with it's children.
			$this->replace_node_with_children( $node );

			// Return early since this node no longer exists.
			return;
		}

		/**
		 * Node is now an element.
		 *
		 * @var DOMElement $node
		 */

		/*
		 * Compile a list of rule_specs to validate for this node
		 * based on tag name of the node.
		 */
		$rule_spec_list_to_validate = array();
		$rule_spec_list             = array();
		if ( isset( $this->allowed_tags[ $node->nodeName ] ) ) {
			$rule_spec_list = $this->allowed_tags[ $node->nodeName ];
		}
		foreach ( $rule_spec_list as $id => $rule_spec ) {
			if ( $this->validate_tag_spec_for_node( $node, $rule_spec[ AMP_Rule_Spec::TAG_SPEC ] ) ) {
				$rule_spec_list_to_validate[ $id ] = $this->get_rule_spec_list_to_validate( $node, $rule_spec );
			}
		}

		// If no valid rule_specs exist, then remove this node and return.
		if ( empty( $rule_spec_list_to_validate ) ) {
			$this->remove_node( $node );
			return;
		}

		// The remaining validations all have to do with attributes.
		$attr_spec_list = array();
		$tag_spec       = array();
		$cdata          = array();

		/*
		 * If we have exactly one rule_spec, use it's attr_spec_list
		 * to validate the node's attributes.
		 */
		if ( 1 === count( $rule_spec_list_to_validate ) ) {
			$rule_spec      = array_pop( $rule_spec_list_to_validate );
			$attr_spec_list = $rule_spec[ AMP_Rule_Spec::ATTR_SPEC_LIST ];
			$tag_spec       = $rule_spec[ AMP_Rule_Spec::TAG_SPEC ];
			if ( isset( $rule_spec[ AMP_Rule_Spec::CDATA ] ) ) {
				$cdata = $rule_spec[ AMP_Rule_Spec::CDATA ];
			}
		} else {
			/*
			 * If there is more than one valid rule_spec for this node,
			 * then try to deduce which one is intended by inspecting
			 * the node's attributes.
			 */

			/*
			 * Get a score from each attr_spec_list by seeing how many
			 * attributes and values match the node.
			 */
			$attr_spec_scores = array();
			foreach ( $rule_spec_list_to_validate as $spec_id => $rule_spec ) {
				$attr_spec_scores[ $spec_id ] = $this->validate_attr_spec_list_for_node( $node, $rule_spec[ AMP_Rule_Spec::ATTR_SPEC_LIST ] );
			}

			// Remove all spec lists that didn't match.
			$attr_spec_scores = array_filter( $attr_spec_scores );

			// If no attribute spec lists match, then the element must be removed.
			if ( empty( $attr_spec_scores ) ) {
				$this->remove_node( $node );
				return;
			}

			// Get the key(s) to the highest score(s).
			$spec_ids_sorted = array_keys( $attr_spec_scores, max( $attr_spec_scores ), true );

			// If there is exactly one attr_spec with a max score, use that one.
			if ( 1 === count( $spec_ids_sorted ) ) {
				$attr_spec_list = $rule_spec_list_to_validate[ $spec_ids_sorted[0] ][ AMP_Rule_Spec::ATTR_SPEC_LIST ];
				$tag_spec       = $rule_spec_list_to_validate[ $spec_ids_sorted[0] ][ AMP_Rule_Spec::TAG_SPEC ];
				if ( isset( $rule_spec_list_to_validate[ $spec_ids_sorted[0] ][ AMP_Rule_Spec::CDATA ] ) ) {
					$cdata = $rule_spec_list_to_validate[ $spec_ids_sorted[0] ][ AMP_Rule_Spec::CDATA ];
				}
			} else {
				// This should not happen very often, but...
				// If we're here, then we're not sure which spec should
				// be used. Let's use the top scoring ones.
				foreach ( $spec_ids_sorted as $id ) {
					$spec_list = isset( $rule_spec_list_to_validate[ $id ][ AMP_Rule_Spec::ATTR_SPEC_LIST ] ) ? $rule_spec_list_to_validate[ $id ][ AMP_Rule_Spec::ATTR_SPEC_LIST ] : null;
					if ( ! $this->is_missing_mandatory_attribute( $spec_list, $node ) ) {
						$attr_spec_list = array_merge( $attr_spec_list, $spec_list );
						$tag_spec       = array_merge(
							$tag_spec,
							$rule_spec_list_to_validate[ $id ][ AMP_Rule_Spec::TAG_SPEC ]
						);
						if ( isset( $rule_spec_list_to_validate[ $id ][ AMP_Rule_Spec::CDATA ] ) ) {
							$cdata = array_merge( $cdata, $rule_spec_list_to_validate[ $id ][ AMP_Rule_Spec::CDATA ] );
						}
					}
				}
				$first_spec = reset( $rule_spec_list_to_validate );
				if ( empty( $attr_spec_list ) && isset( $first_spec[ AMP_Rule_Spec::ATTR_SPEC_LIST ] ) ) {
					$attr_spec_list = $first_spec[ AMP_Rule_Spec::ATTR_SPEC_LIST ];
				}
			}
		}

		if ( ! empty( $attr_spec_list ) && $this->is_missing_mandatory_attribute( $attr_spec_list, $node ) ) {
			$this->remove_node( $node );
			return;
		}

		// Remove element if it has illegal CDATA.
		if ( ! empty( $cdata ) && $node instanceof DOMElement ) {
			$validity = $this->validate_cdata_for_node( $node, $cdata );
			if ( is_wp_error( $validity ) ) {
				$this->remove_node( $node );
				return;
			}
		}

		$merged_attr_spec_list = array_merge(
			$this->globally_allowed_attributes,
			$attr_spec_list
		);

		// Amend spec list with layout.
		if ( isset( $tag_spec['amp_layout'] ) ) {
			$merged_attr_spec_list = array_merge( $merged_attr_spec_list, $this->layout_allowed_attributes );

			if ( isset( $tag_spec['amp_layout']['supported_layouts'] ) ) {
				$layouts = wp_array_slice_assoc( AMP_Rule_Spec::$layout_enum, $tag_spec['amp_layout']['supported_layouts'] );

				$merged_attr_spec_list['layout'][ AMP_Rule_Spec::VALUE_REGEX_CASEI ] = '(' . implode( '|', $layouts ) . ')';
			}
		}

		// Identify any remaining disallowed attributes.
		$disallowed_attributes = $this->get_disallowed_attributes_in_node( $node, $merged_attr_spec_list );

		// Identify attribute values that don't conform to the attr_spec.
		$disallowed_attributes = $this->sanitize_disallowed_attribute_values_in_node( $node, $merged_attr_spec_list, $disallowed_attributes );

		// If $disallowed_attributes is false then the entire element should be removed.
		if ( false === $disallowed_attributes ) {
			$this->remove_node( $node );
			return;
		}

		// Remove all invalid attributes.
		if ( ! empty( $disallowed_attributes ) ) {
			/*
			 * Capture all element attributes up front so that differing validation errors result when
			 * one invalid attribute is accepted but the others are still rejected.
			 */
			$validation_error = array(
				'element_attributes' => array(),
			);
			foreach ( $node->attributes as $attribute ) {
				$validation_error['element_attributes'][ $attribute->nodeName ] = $attribute->nodeValue;
			}
			foreach ( $disallowed_attributes as $disallowed_attribute ) {
				$this->remove_invalid_attribute( $node, $disallowed_attribute, $validation_error );
			}
		}

		// Add required AMP component scripts if the element is still in the document.
		if ( $node->parentNode ) {
			if ( ! empty( $tag_spec['also_requires_tag_warning'] ) ) {
				$this->script_components[] = strtok( $tag_spec['also_requires_tag_warning'][0], ' ' );
			}
			if ( ! empty( $tag_spec['requires_extension'] ) ) {
				$this->script_components = array_merge( $this->script_components, $tag_spec['requires_extension'] );
			}

			// Check if element needs amp-bind component.
			if ( $node instanceof DOMElement && ! in_array( 'amp-bind', $this->script_components, true ) ) {
				foreach ( $node->attributes as $name => $value ) {
					$is_bind_attribute = (
						'[' === $name[0]
						||
						( isset( $this->rev_alternate_attr_name_lookup[ $name ] ) && '[' === $this->rev_alternate_attr_name_lookup[ $name ][0] )
					);
					if ( $is_bind_attribute ) {
						$this->script_components[] = 'amp-bind';
						break;
					}
				}
			}
		}
	}

	/**
	 * Whether a node is missing a mandatory attribute.
	 *
	 * @param array      $attr_spec The attribute specification.
	 * @param DOMElement $node      The DOMElement of the node to check.
	 * @return boolean $is_missing boolean Whether a required attribute is missing.
	 */
	public function is_missing_mandatory_attribute( $attr_spec, $node ) {
		if ( ! is_array( $attr_spec ) ) {
			return false;
		}
		foreach ( $attr_spec as $attr_name => $attr_spec_rule_value ) {
			if ( '\u' === substr( $attr_name, 0, 2 ) ) {
				$attr_name = html_entity_decode( '&#x' . substr( $attr_name, 2 ) . ';' ); // Probably ⚡.
			}
			$is_mandatory     = isset( $attr_spec_rule_value[ AMP_Rule_Spec::MANDATORY ] ) ? ( true === $attr_spec_rule_value[ AMP_Rule_Spec::MANDATORY ] ) : false;
			$attribute_exists = false;
			if ( method_exists( $node, 'hasAttribute' ) ) {
				$attribute_exists = $node->hasAttribute( $attr_name );
				if ( ! $attribute_exists && ! empty( $attr_spec_rule_value[ AMP_Rule_Spec::ALTERNATIVE_NAMES ] ) ) {
					foreach ( $attr_spec_rule_value[ AMP_Rule_Spec::ALTERNATIVE_NAMES ] as $alternative_attr_name ) {
						if ( $node->hasAttribute( $alternative_attr_name ) ) {
							$attribute_exists = true;
							break;
						}
					}
				}
			}
			if ( $is_mandatory && ! $attribute_exists ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Validate element for its CDATA.
	 *
	 * @since 0.7
	 *
	 * @param DOMElement $element    Element.
	 * @param array      $cdata_spec CDATA.
	 * @return true|WP_Error True when valid or error when invalid.
	 */
	private function validate_cdata_for_node( $element, $cdata_spec ) {
		if ( isset( $cdata_spec['blacklisted_cdata_regex'] ) ) {
			if ( preg_match( '@' . $cdata_spec['blacklisted_cdata_regex']['regex'] . '@u', $element->textContent ) ) {
				return new WP_Error( $cdata_spec['blacklisted_cdata_regex']['error_message'] );
			}
		} elseif ( isset( $cdata_spec['cdata_regex'] ) ) {
			$delimiter = false === strpos( $cdata_spec['cdata_regex'], '@' ) ? '@' : '#';
			if ( ! preg_match( $delimiter . $cdata_spec['cdata_regex'] . $delimiter . 'u', $element->textContent ) ) {
				return new WP_Error( 'cdata_regex' );
			}
		}
		return true;
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
	 * @param DOMNode $node The node to validate.
	 * @param array   $tag_spec The specification.
	 * @return boolean $valid Whether the node's placement is valid.
	 */
	private function validate_tag_spec_for_node( $node, $tag_spec ) {

		if ( ! empty( $tag_spec[ AMP_Rule_Spec::MANDATORY_PARENT ] ) && ! $this->has_parent( $node, $tag_spec[ AMP_Rule_Spec::MANDATORY_PARENT ] ) ) {
			return false;
		}

		// Extension scripts must be in the head.
		if ( isset( $tag_spec['extension_spec'] ) && ! $this->has_parent( $node, 'head' ) ) {
			return false;
		}

		if ( ! empty( $tag_spec[ AMP_Rule_Spec::DISALLOWED_ANCESTOR ] ) ) {
			foreach ( $tag_spec[ AMP_Rule_Spec::DISALLOWED_ANCESTOR ] as $disallowed_ancestor_node_name ) {
				if ( $this->has_ancestor( $node, $disallowed_ancestor_node_name ) ) {
					return false;
				}
			}
		}

		if ( ! empty( $tag_spec[ AMP_Rule_Spec::MANDATORY_ANCESTOR ] ) && ! $this->has_ancestor( $node, $tag_spec[ AMP_Rule_Spec::MANDATORY_ANCESTOR ] ) ) {
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
	 * @param DOMNode $node           Node.
	 * @param array[] $attr_spec_list Attribute Spec list.
	 *
	 * @return float Number of times the attribute spec list matched. If there was a mismatch, then 0 is returned. 0.5 is returned if there is an implicit match.
	 */
	private function validate_attr_spec_list_for_node( $node, $attr_spec_list ) {
		/*
		 * If node has no attributes there is no point in continuing, but if none of attributes
		 * in the spec list are mandatory, then we give this a score.
		 */
		if ( ! $node->hasAttributes() ) {
			foreach ( $attr_spec_list as $attr_name => $attr_spec_rule ) {
				if ( isset( $attr_spec_rule[ AMP_Rule_Spec::MANDATORY ] ) ) {
					return 0;
				}
			}
			return 0.5;
		}

		if ( ! $node instanceof DOMElement ) {
			/*
			 * A DOMNode is not valid for checks so might
			 * as well bail here is not an DOMElement.
			 */
			return 0;
		}

		foreach ( $node->attributes as $attr_name => $attr_node ) {
			if ( ! isset( $attr_spec_list[ $attr_name ][ AMP_Rule_Spec::ALTERNATIVE_NAMES ] ) ) {
				continue;
			}
			foreach ( $attr_spec_list[ $attr_name ][ AMP_Rule_Spec::ALTERNATIVE_NAMES ] as $attr_alt_name ) {
				$attr_spec_list[ $attr_alt_name ] = $attr_spec_list[ $attr_name ];
			}
		}

		$score = 0;

		/*
		 * Keep track of how many of the attribute spec rules are mandatory,
		 * because if none are mandatory, then we will let this rule have a
		 * score since all the invalid attributes can just be removed.
		 */
		$mandatory_count = 0;

		/*
		 * Iterate through each attribute rule in this attr spec list and run
		 * the series of tests. Each filter is given a `$node`, an `$attr_name`,
		 * and an `$attr_spec_rule`. If the `$attr_spec_rule` seems to be valid
		 * for the given node, then the filter should increment the score by one.
		 */
		foreach ( $attr_spec_list as $attr_name => $attr_spec_rule ) {

			// If attr spec rule is empty, then it allows anything.
			if ( empty( $attr_spec_rule ) && $node->hasAttribute( $attr_name ) ) {
				$score++;
				continue;
			}

			// If a mandatory attribute is required, and attribute exists, pass.
			if ( isset( $attr_spec_rule[ AMP_Rule_Spec::MANDATORY ] ) ) {
				$mandatory_count++;
				$result = $this->check_attr_spec_rule_mandatory( $node, $attr_name, $attr_spec_rule );
				if ( AMP_Rule_Spec::PASS === $result ) {
					$score++;
				} elseif ( AMP_Rule_Spec::FAIL === $result ) {
					return 0;
				}
			}

			/*
			 * Check 'value' - case sensitive
			 * Given attribute's value must exactly equal value of the rule to pass.
			 */
			if ( isset( $attr_spec_rule[ AMP_Rule_Spec::VALUE ] ) ) {
				$result = $this->check_attr_spec_rule_value( $node, $attr_name, $attr_spec_rule );
				if ( AMP_Rule_Spec::PASS === $result ) {
					$score++;
				} elseif ( AMP_Rule_Spec::FAIL === $result ) {
					return 0;
				}
			}

			/*
			 * Check 'value_regex' - case sensitive regex match
			 * Given attribute's value must be a case insensitive match to regex pattern
			 * specified by the value of rule to pass.
			 */
			if ( isset( $attr_spec_rule[ AMP_Rule_Spec::VALUE_REGEX ] ) ) {
				$result = $this->check_attr_spec_rule_value_regex( $node, $attr_name, $attr_spec_rule );
				if ( AMP_Rule_Spec::PASS === $result ) {
					$score++;
				} elseif ( AMP_Rule_Spec::FAIL === $result ) {
					return 0;
				}
			}

			/*
			 * Check 'value_casei' - case insensitive
			 * Given attribute's value must be a case insensitive match to the value of
			 * the rule to pass.
			 */
			if ( isset( $attr_spec_rule[ AMP_Rule_Spec::VALUE_CASEI ] ) ) {
				$result = $this->check_attr_spec_rule_value_casei( $node, $attr_name, $attr_spec_rule );
				if ( AMP_Rule_Spec::PASS === $result ) {
					$score++;
				} elseif ( AMP_Rule_Spec::FAIL === $result ) {
					return 0;
				}
			}

			/*
			 * Check 'value_regex_casei' - case insensitive regex match
			 * Given attribute's value must be a case insensitive match to the regex
			 * pattern specified by the value of the rule to pass.
			 */
			if ( isset( $attr_spec_rule[ AMP_Rule_Spec::VALUE_REGEX_CASEI ] ) ) {
				$result = $this->check_attr_spec_rule_value_regex_casei( $node, $attr_name, $attr_spec_rule );
				if ( AMP_Rule_Spec::PASS === $result ) {
					$score++;
				} elseif ( AMP_Rule_Spec::FAIL === $result ) {
					return 0;
				}
			}

			/*
			 * If given attribute's value is a URL with a protocol, the protocol must
			 * be in the array specified by the rule's value to pass.
			 */
			if ( isset( $attr_spec_rule[ AMP_Rule_Spec::VALUE_URL ][ AMP_Rule_Spec::ALLOWED_PROTOCOL ] ) ) {
				$result = $this->check_attr_spec_rule_allowed_protocol( $node, $attr_name, $attr_spec_rule );
				if ( AMP_Rule_Spec::PASS === $result ) {
					$score++;
				} elseif ( AMP_Rule_Spec::FAIL === $result ) {
					return 0;
				}
			}

			/*
			 * If given attribute's value is a URL with a host, the host must
			 * be valid
			 */
			if ( isset( $attr_spec_rule[ AMP_Rule_Spec::VALUE_URL ] ) ) {
				$result = $this->check_attr_spec_rule_valid_url( $node, $attr_name, $attr_spec_rule );
				if ( AMP_Rule_Spec::PASS === $result ) {
					$score++;
				} elseif ( AMP_Rule_Spec::FAIL === $result ) {
					return 0;
				}
			}

			/*
			 * If the given attribute's value is *not* a relative path, and the rule's
			 * value is `false`, then pass.
			 */
			if ( isset( $attr_spec_rule[ AMP_Rule_Spec::VALUE_URL ][ AMP_Rule_Spec::ALLOW_RELATIVE ] ) ) {
				$result = $this->check_attr_spec_rule_disallowed_relative( $node, $attr_name, $attr_spec_rule );
				if ( AMP_Rule_Spec::PASS === $result ) {
					$score++;
				} elseif ( AMP_Rule_Spec::FAIL === $result ) {
					return 0;
				}
			}

			/*
			 * If the given attribute's value exists, is non-empty and the rule's value
			 * is false, then pass.
			 */
			if ( isset( $attr_spec_rule[ AMP_Rule_Spec::VALUE_URL ][ AMP_Rule_Spec::ALLOW_EMPTY ] ) ) {
				$result = $this->check_attr_spec_rule_disallowed_empty( $node, $attr_name, $attr_spec_rule );
				if ( AMP_Rule_Spec::PASS === $result ) {
					$score++;
				} elseif ( AMP_Rule_Spec::FAIL === $result ) {
					return 0;
				}
			}

			/*
			 * If the given attribute's value is a URL and does not match any of the list
			 * of domains in the value of the rule, then pass.
			 */
			if ( isset( $attr_spec_rule[ AMP_Rule_Spec::DISALLOWED_DOMAIN ] ) ) {
				$result = $this->check_attr_spec_rule_disallowed_domain( $node, $attr_name, $attr_spec_rule );
				if ( AMP_Rule_Spec::PASS === $result ) {
					$score++;
				} elseif ( AMP_Rule_Spec::FAIL === $result ) {
					return 0;
				}
			}

			/*
			 * If the attribute's value exists and does not match the regex specified
			 * by the rule's value, then pass.
			 */
			if ( isset( $attr_spec_rule[ AMP_Rule_Spec::BLACKLISTED_VALUE_REGEX ] ) ) {
				$result = $this->check_attr_spec_rule_blacklisted_value_regex( $node, $attr_name, $attr_spec_rule );
				if ( AMP_Rule_Spec::PASS === $result ) {
					$score++;
				} elseif ( AMP_Rule_Spec::FAIL === $result ) {
					return 0;
				}
			}

			// If the attribute's value exists and it matches the value properties spec.
			if ( isset( $attr_spec_rule[ AMP_Rule_Spec::VALUE_PROPERTIES ] ) && $node->hasAttribute( $attr_name ) ) {
				$result = $this->check_attr_spec_rule_value_properties( $node, $attr_name, $attr_spec_rule );
				if ( AMP_Rule_Spec::PASS === $result ) {
					$score++;
				} elseif ( AMP_Rule_Spec::FAIL === $result ) {
					return 0;
				}
			}
		}

		// Give the spec a score if it doesn't have any mandatory attributes.
		if ( 0 === $mandatory_count && 0 === $score ) {
			$score = 0.5;
		}

		return $score;
	}

	/**
	 * Remove attributes from $node that are not listed in $allowed_attrs.
	 *
	 * @param DOMNode $node           Node.
	 * @param array[] $attr_spec_list Attribute spec list.
	 * @return DOMAttr[] Attributes to remove.
	 */
	private function get_disallowed_attributes_in_node( $node, $attr_spec_list ) {

		if ( ! $node instanceof DOMElement ) {
			/**
			 * If $node is only a DOMNode and not a DOMElement we can't
			 * remove an attribute from it anyway.  So bail out now.
			 */
			return array();
		}

		/*
		 * We can't remove attributes inside the 'foreach' loop without
		 * breaking the iteration. So we keep track of the attributes to
		 * remove in the first loop, then remove them in the second loop.
		 */
		$attrs_to_remove = array();
		foreach ( $node->attributes as $attr_name => $attr_node ) {
			if ( ! $this->is_amp_allowed_attribute( $attr_node, $attr_spec_list ) ) {
				$attrs_to_remove[] = $attr_node;
			}
		}

		return $attrs_to_remove;
	}

	/**
	 * Remove invalid AMP attributes values from $node that have been implicitly disallowed.
	 *
	 * Allowed values are found $this->globally_allowed_attributes and in parameter $attr_spec_list
	 *
	 * @param DOMNode   $node                       Node.
	 * @param array[][] $attr_spec_list             Attribute spec list.
	 * @param DOMAttr[] $attributes_pending_removal Attributes pending removal.
	 * @return DOMAttr[]|false Attributes to remove, or false if the element itself should be removed.
	 */
	private function sanitize_disallowed_attribute_values_in_node( $node, $attr_spec_list, $attributes_pending_removal ) {

		if ( ! $node instanceof DOMElement ) {
			/*
			 * If $node is only a DOMNode and not a DOMElement we can't
			 * remove an attribute from it anyway.  So bail out now.
			 */
			return $attributes_pending_removal;
		}

		return $this->delegated_sanitize_disallowed_attribute_values_in_node(
			$node,
			array_merge(
				$this->globally_allowed_attributes,
				$attr_spec_list
			),
			$attributes_pending_removal
		);
	}

	/**
	 * Remove attributes values from $node that have been disallowed by AMP.
	 *
	 * @see $this->sanitize_disallowed_attribute_values_in_node() which delegates to this method
	 *
	 * @param DOMElement $node                       Node.
	 * @param array[][]  $attr_spec_list             Attribute spec list.
	 * @param DOMAttr[]  $attributes_pending_removal Attributes pending removal.
	 * @return DOMAttr[]|false Attributes to remove, or false if the element itself should be removed.
	 */
	private function delegated_sanitize_disallowed_attribute_values_in_node( $node, $attr_spec_list, $attributes_pending_removal ) {
		$attrs_to_remove = array();

		foreach ( $attr_spec_list as $attr_name => $attr_val ) {
			if ( isset( $attr_spec_list[ $attr_name ][ AMP_Rule_Spec::ALTERNATIVE_NAMES ] ) ) {
				foreach ( $attr_spec_list[ $attr_name ][ AMP_Rule_Spec::ALTERNATIVE_NAMES ] as $attr_alt_name ) {
					$attr_spec_list[ $attr_alt_name ] = $attr_spec_list[ $attr_name ];
				}
			}
		}

		foreach ( $node->attributes as $attr_name => $attr_node ) {

			if ( ! isset( $attr_spec_list[ $attr_name ] ) || in_array( $attr_node, $attributes_pending_removal, true ) ) {
				continue;
			}

			$should_remove_node = false;
			$attr_spec_rule     = $attr_spec_list[ $attr_name ];

			if ( isset( $attr_spec_rule[ AMP_Rule_Spec::VALUE ] ) &&
				AMP_Rule_Spec::FAIL === $this->check_attr_spec_rule_value( $node, $attr_name, $attr_spec_rule ) ) {
				$should_remove_node = true;
			} elseif ( isset( $attr_spec_rule[ AMP_Rule_Spec::VALUE_CASEI ] ) &&
				AMP_Rule_Spec::FAIL === $this->check_attr_spec_rule_value_casei( $node, $attr_name, $attr_spec_rule ) ) {
				$should_remove_node = true;
			} elseif ( isset( $attr_spec_rule[ AMP_Rule_Spec::VALUE_REGEX ] ) &&
				AMP_Rule_Spec::FAIL === $this->check_attr_spec_rule_value_regex( $node, $attr_name, $attr_spec_rule ) ) {
				$should_remove_node = true;
			} elseif ( isset( $attr_spec_rule[ AMP_Rule_Spec::VALUE_REGEX_CASEI ] ) &&
				AMP_Rule_Spec::FAIL === $this->check_attr_spec_rule_value_regex_casei( $node, $attr_name, $attr_spec_rule ) ) {
				$should_remove_node = true;
			} elseif ( isset( $attr_spec_rule[ AMP_Rule_Spec::VALUE_URL ][ AMP_Rule_Spec::ALLOWED_PROTOCOL ] ) &&
				AMP_Rule_Spec::FAIL === $this->check_attr_spec_rule_allowed_protocol( $node, $attr_name, $attr_spec_rule ) ) {
				$should_remove_node = true;
			} elseif ( isset( $attr_spec_rule[ AMP_Rule_Spec::VALUE_URL ] ) &&
				AMP_Rule_Spec::FAIL === $this->check_attr_spec_rule_valid_url( $node, $attr_name, $attr_spec_rule ) ) {
				$should_remove_node = true;
			} elseif ( isset( $attr_spec_rule[ AMP_Rule_Spec::VALUE_URL ][ AMP_Rule_Spec::ALLOW_RELATIVE ] ) &&
				AMP_Rule_Spec::FAIL === $this->check_attr_spec_rule_disallowed_relative( $node, $attr_name, $attr_spec_rule ) ) {
				$should_remove_node = true;
			} elseif ( isset( $attr_spec_rule[ AMP_Rule_Spec::VALUE_URL ][ AMP_Rule_Spec::ALLOW_EMPTY ] ) &&
				AMP_Rule_Spec::FAIL === $this->check_attr_spec_rule_disallowed_empty( $node, $attr_name, $attr_spec_rule ) ) {
				$should_remove_node = true;
			} elseif ( isset( $attr_spec_rule[ AMP_Rule_Spec::DISALLOWED_DOMAIN ] ) &&
				AMP_Rule_Spec::FAIL === $this->check_attr_spec_rule_disallowed_domain( $node, $attr_name, $attr_spec_rule ) ) {
				$should_remove_node = true;
			} elseif ( isset( $attr_spec_rule[ AMP_Rule_Spec::BLACKLISTED_VALUE_REGEX ] ) &&
				AMP_Rule_Spec::FAIL === $this->check_attr_spec_rule_blacklisted_value_regex( $node, $attr_name, $attr_spec_rule ) ) {
				$should_remove_node = true;
			}

			if ( $should_remove_node ) {
				$is_mandatory =
					isset( $attr_spec_rule[ AMP_Rule_Spec::MANDATORY ] )
						? (bool) $attr_spec_rule[ AMP_Rule_Spec::MANDATORY ]
						: false;

				if ( $is_mandatory ) {
					$this->remove_node( $node );
					return false;
				}

				$attrs_to_remove[] = $attr_node;
			}
		}

		// Remove the disallowed values.
		foreach ( $attrs_to_remove as $attr_node ) {
			if ( isset( $attr_spec_list[ $attr_node->nodeName ][ AMP_Rule_Spec::VALUE_URL ][ AMP_Rule_Spec::ALLOW_EMPTY ] ) &&
				( true === $attr_spec_list[ $attr_node->nodeName ][ AMP_Rule_Spec::VALUE_URL ][ AMP_Rule_Spec::ALLOW_EMPTY ] ) ) {
				$attr_node->nodeValue = '';
			} else {
				$attributes_pending_removal[] = $attr_node;
			}
		}

		return $attributes_pending_removal;
	}

	/**
	 * Check if attribute is mandatory determine whether it exists in $node.
	 *
	 * When checking for the given attribute it also checks valid alternates.
	 *
	 * @param DOMElement $node           Node.
	 * @param string     $attr_name      Attribute name.
	 * @param array[]    $attr_spec_rule Attribute spec rule.
	 *
	 * @return string:
	 *      - AMP_Rule_Spec::PASS - $attr_name is mandatory and it exists
	 *      - AMP_Rule_Spec::FAIL - $attr_name is mandatory, but doesn't exist
	 *      - AMP_Rule_Spec::NOT_APPLICABLE - $attr_name is not mandatory
	 */
	private function check_attr_spec_rule_mandatory( $node, $attr_name, $attr_spec_rule ) {
		if ( isset( $attr_spec_rule[ AMP_Rule_Spec::MANDATORY ] ) && ( $attr_spec_rule[ AMP_Rule_Spec::MANDATORY ] ) ) {
			if ( $node->hasAttribute( $attr_name ) ) {
				return AMP_Rule_Spec::PASS;
			} else {
				// Check if an alternative name list is specified.
				if ( isset( $attr_spec_rule[ AMP_Rule_Spec::ALTERNATIVE_NAMES ] ) ) {
					foreach ( $attr_spec_rule[ AMP_Rule_Spec::ALTERNATIVE_NAMES ] as $alt_name ) {
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
	 * @param DOMElement $node           Node.
	 * @param string     $attr_name      Attribute name.
	 * @param array      $attr_spec_rule Attribute spec rule.
	 *
	 * @return string:
	 *      - AMP_Rule_Spec::PASS - $attr_name has a value that matches the rule.
	 *      - AMP_Rule_Spec::FAIL - $attr_name has a value that does *not* match rule.
	 *      - AMP_Rule_Spec::NOT_APPLICABLE - $attr_name does not exist or there
	 *                                        is no rule for this attribute.
	 */
	private function check_attr_spec_rule_value( $node, $attr_name, $attr_spec_rule ) {
		if ( isset( $attr_spec_rule[ AMP_Rule_Spec::VALUE ] ) ) {
			if ( $node->hasAttribute( $attr_name ) ) {
				if ( $this->check_matching_attribute_value( $attr_name, $node->getAttribute( $attr_name ), $attr_spec_rule[ AMP_Rule_Spec::VALUE ] ) ) {
					return AMP_Rule_Spec::PASS;
				} else {
					return AMP_Rule_Spec::FAIL;
				}
			} elseif ( isset( $attr_spec_rule[ AMP_Rule_Spec::ALTERNATIVE_NAMES ] ) ) {
				foreach ( $attr_spec_rule[ AMP_Rule_Spec::ALTERNATIVE_NAMES ] as $alternative_name ) {
					if ( $node->hasAttribute( $alternative_name ) ) {
						if ( $this->check_matching_attribute_value( $attr_name, $node->getAttribute( $alternative_name ), $attr_spec_rule[ AMP_Rule_Spec::VALUE ] ) ) {
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
	 * Check that an attribute's value matches is given spec value.
	 *
	 * This takes into account boolean attributes where value can match name (e.g. selected="selected").
	 *
	 * @since 0.7.0
	 * @since 1.0.0 The spec value is now an array.
	 *
	 * @param string       $attr_name   Attribute name.
	 * @param string       $attr_value  Attribute value.
	 * @param string|array $spec_values Attribute spec value(s).
	 * @return bool Is value valid.
	 */
	private function check_matching_attribute_value( $attr_name, $attr_value, $spec_values ) {
		if ( is_string( $spec_values ) ) {
			$spec_values = (array) $spec_values;
		}
		foreach ( $spec_values as $spec_value ) {
			if ( $spec_value === $attr_value ) {
				return true;
			}

			// Check for boolean attribute.
			$is_bool_match = (
				'' === $spec_value
				&&
				in_array( $attr_name, AMP_Rule_Spec::$boolean_attributes, true )
				&&
				strtolower( $attr_value ) === strtolower( $attr_name )
			);
			if ( $is_bool_match ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Check if attribute has a value rule determine if its value matches ignoring case.
	 *
	 * @param DOMElement   $node           Node.
	 * @param string       $attr_name      Attribute name.
	 * @param array|string $attr_spec_rule Attribute spec rule.
	 *
	 * @return string:
	 *      - AMP_Rule_Spec::PASS - $attr_name has a value that matches the rule.
	 *      - AMP_Rule_Spec::FAIL - $attr_name has a value that does *not* match rule.
	 *      - AMP_Rule_Spec::NOT_APPLICABLE - $attr_name does not exist or there
	 *                                        is no rule for this attribute.
	 */
	private function check_attr_spec_rule_value_casei( $node, $attr_name, $attr_spec_rule ) {
		if ( ! isset( $attr_spec_rule[ AMP_Rule_Spec::VALUE_CASEI ] ) ) {
			return AMP_Rule_Spec::NOT_APPLICABLE;
		}

		$result      = AMP_Rule_Spec::NOT_APPLICABLE;
		$rule_values = (array) $attr_spec_rule[ AMP_Rule_Spec::VALUE_CASEI ];
		foreach ( $rule_values as $rule_value ) {
			$rule_value = strtolower( $rule_value );
			if ( $node->hasAttribute( $attr_name ) ) {
				$attr_value = strtolower( $node->getAttribute( $attr_name ) );
				if ( $attr_value === (string) $rule_value ) {
					return AMP_Rule_Spec::PASS;
				} else {
					$result = AMP_Rule_Spec::FAIL;
				}
			} elseif ( isset( $attr_spec_rule[ AMP_Rule_Spec::ALTERNATIVE_NAMES ] ) ) {
				foreach ( $attr_spec_rule[ AMP_Rule_Spec::ALTERNATIVE_NAMES ] as $alternative_name ) {
					if ( $node->hasAttribute( $alternative_name ) ) {
						$attr_value = strtolower( $node->getAttribute( $alternative_name ) );
						if ( $attr_value === (string) $rule_value ) {
							return AMP_Rule_Spec::PASS;
						} else {
							$result = AMP_Rule_Spec::FAIL;
						}
					}
				}
			}
		}
		return $result;
	}

	/**
	 * Check if attribute has a regex value rule determine if it matches.
	 *
	 * @param DOMElement       $node           Node.
	 * @param string           $attr_name      Attribute name.
	 * @param array[]|string[] $attr_spec_rule Attribute spec rule.
	 *
	 * @return string:
	 *      - AMP_Rule_Spec::PASS - $attr_name has a value that matches the rule.
	 *      - AMP_Rule_Spec::FAIL - $attr_name has a value that does *not* match rule.
	 *      - AMP_Rule_Spec::NOT_APPLICABLE - $attr_name does not exist or there
	 *                                        is no rule for this attribute.
	 */
	private function check_attr_spec_rule_value_regex( $node, $attr_name, $attr_spec_rule ) {
		// Check 'value_regex' - case sensitive regex match.
		if ( isset( $attr_spec_rule[ AMP_Rule_Spec::VALUE_REGEX ] ) && $node->hasAttribute( $attr_name ) ) {
			$rule_value = $attr_spec_rule[ AMP_Rule_Spec::VALUE_REGEX ];
			$rule_value = str_replace( '/', '\\/', $rule_value );

			/*
			 * The regex pattern has '^' and '$' though they are not in the AMP spec.
			 * Leaving them out would allow both '_blank' and 'yyy_blankzzz' to be
			 * matched by a regex rule of '(_blank|_self|_top)'. As the AMP JS validator
			 * only accepts '_blank' we leave it this way for now.
			 */
			if ( preg_match( '/^(' . $rule_value . ')$/u', $node->getAttribute( $attr_name ) ) ) {
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
	 * @param DOMElement       $node           Node.
	 * @param string           $attr_name      Attribute name.
	 * @param array[]|string[] $attr_spec_rule Attribute spec rule.
	 *
	 * @return string:
	 *      - AMP_Rule_Spec::PASS - $attr_name has a value that matches the rule.
	 *      - AMP_Rule_Spec::FAIL - $attr_name has a value that does *not* match rule.
	 *      - AMP_Rule_Spec::NOT_APPLICABLE - $attr_name does not exist or there
	 *                                        is no rule for this attribute.
	 */
	private function check_attr_spec_rule_value_regex_casei( $node, $attr_name, $attr_spec_rule ) {
		/**
		 * Check 'value_regex_casei' - case insensitive regex match
		 */
		if ( isset( $attr_spec_rule[ AMP_Rule_Spec::VALUE_REGEX_CASEI ] ) && $node->hasAttribute( $attr_name ) ) {
			$rule_value = $attr_spec_rule[ AMP_Rule_Spec::VALUE_REGEX_CASEI ];
			$rule_value = str_replace( '/', '\\/', $rule_value );

			// See note above regarding the '^' and '$' that are added here.
			if ( preg_match( '/^(' . $rule_value . ')$/ui', $node->getAttribute( $attr_name ) ) ) {
				return AMP_Rule_Spec::PASS;
			} else {
				return AMP_Rule_Spec::FAIL;
			}
		}
		return AMP_Rule_Spec::NOT_APPLICABLE;
	}

	/**
	 * Check if attribute has a valid host value
	 *
	 * @since 0.7
	 *
	 * @param DOMElement       $node           Node.
	 * @param string           $attr_name      Attribute name.
	 * @param array[]|string[] $attr_spec_rule Attribute spec rule.
	 *
	 * @return string:
	 *      - AMP_Rule_Spec::PASS - $attr_name has a value that matches the rule.
	 *      - AMP_Rule_Spec::FAIL - $attr_name has a value that does *not* match rule.
	 *      - AMP_Rule_Spec::NOT_APPLICABLE - $attr_name does not exist or there
	 *                                        is no rule for this attribute.
	 */
	private function check_attr_spec_rule_valid_url( $node, $attr_name, $attr_spec_rule ) {
		if ( isset( $attr_spec_rule[ AMP_Rule_Spec::VALUE_URL ] ) ) {
			if ( $node->hasAttribute( $attr_name ) ) {
				foreach ( $this->extract_attribute_urls( $node->getAttributeNode( $attr_name ) ) as $url ) {
					$url = urldecode( $url );

					// Check if the protocol contains invalid chars (protocolCharIsValid: https://github.com/ampproject/amphtml/blob/af1e3a550feeafd732226202b8d1f26dcefefa18/validator/engine/parse-url.js#L31-L39).
					$protocol = $this->parse_protocol( $url );
					if ( isset( $protocol ) ) {
						if ( ! preg_match( '/^[a-zA-Z0-9\+-]+/i', $protocol ) ) {
							return AMP_Rule_Spec::FAIL;
						}
						$url = substr( $url, strlen( $protocol ) + 1 );
					}

					// Check if the host contains invalid chars (hostCharIsValid: https://github.com/ampproject/amphtml/blob/af1e3a550feeafd732226202b8d1f26dcefefa18/validator/engine/parse-url.js#L62-L103).
					$host = wp_parse_url( $url, PHP_URL_HOST );
					if ( $host && preg_match( '/[!"#$%&\'()*+,\/:;<=>?@[\]^`{|}~\s]/i', $host ) ) {
						return AMP_Rule_Spec::FAIL;
					}
				}

				return AMP_Rule_Spec::PASS;
			}
		}

		return AMP_Rule_Spec::NOT_APPLICABLE;
	}

	/**
	 * Parse protocol from URL.
	 *
	 * This may not be a valid protocol (scheme), but it will be where the protocol should be in the URL.
	 *
	 * @link https://github.com/ampproject/amphtml/blob/af1e3a550feeafd732226202b8d1f26dcefefa18/validator/engine/parse-url.js#L235-L282
	 * @param string $url URL.
	 * @return string|null Protocol without colon if matched. Otherwise null.
	 */
	private function parse_protocol( $url ) {
		if ( preg_match( '#^[^/]+(?=:)#', $url, $matches ) ) {
			return $matches[0];
		}
		return null;
	}

	/**
	 * Check if attribute has a protocol value rule determine if it matches.
	 *
	 * @param DOMElement       $node           Node.
	 * @param string           $attr_name      Attribute name.
	 * @param array[]|string[] $attr_spec_rule Attribute spec rule.
	 *
	 * @return string:
	 *      - AMP_Rule_Spec::PASS - $attr_name has a value that matches the rule.
	 *      - AMP_Rule_Spec::FAIL - $attr_name has a value that does *not* match rule.
	 *      - AMP_Rule_Spec::NOT_APPLICABLE - $attr_name does not exist or there
	 *                                        is no rule for this attribute.
	 */
	private function check_attr_spec_rule_allowed_protocol( $node, $attr_name, $attr_spec_rule ) {
		if ( isset( $attr_spec_rule[ AMP_Rule_Spec::VALUE_URL ][ AMP_Rule_Spec::ALLOWED_PROTOCOL ] ) ) {
			if ( $node->hasAttribute( $attr_name ) ) {
				foreach ( $this->extract_attribute_urls( $node->getAttributeNode( $attr_name ) ) as $url ) {
					$url_scheme = $this->parse_protocol( $url );
					if ( isset( $url_scheme ) && ! in_array( strtolower( $url_scheme ), $attr_spec_rule[ AMP_Rule_Spec::VALUE_URL ][ AMP_Rule_Spec::ALLOWED_PROTOCOL ], true ) ) {
						return AMP_Rule_Spec::FAIL;
					}
				}
				return AMP_Rule_Spec::PASS;
			} elseif ( isset( $attr_spec_rule[ AMP_Rule_Spec::ALTERNATIVE_NAMES ] ) ) {
				foreach ( $attr_spec_rule[ AMP_Rule_Spec::ALTERNATIVE_NAMES ] as $alternative_name ) {
					if ( $node->hasAttribute( $alternative_name ) ) {
						foreach ( $this->extract_attribute_urls( $node->getAttributeNode( $alternative_name ), $attr_name ) as $url ) {
							$url_scheme = $this->parse_protocol( $url );
							if ( isset( $url_scheme ) && ! in_array( strtolower( $url_scheme ), $attr_spec_rule[ AMP_Rule_Spec::VALUE_URL ][ AMP_Rule_Spec::ALLOWED_PROTOCOL ], true ) ) {
								return AMP_Rule_Spec::FAIL;
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
	 * Extract URLs from attribute.
	 *
	 * @param DOMAttr     $attribute_node Attribute node.
	 * @param string|null $spec_attr_name Non-alternative attribute name for the spec.
	 * @return string[] List of URLs.
	 */
	private function extract_attribute_urls( $attribute_node, $spec_attr_name = null ) {
		/*
		 * Handle the srcset special case where the attribute value can contain multiple parts, each in the format `URL [WIDTH] [PIXEL_DENSITY]`.
		 * So we split the srcset attribute value by commas and then return the first token of each item, omitting width descriptor and pixel density descriptor.
		 * This splitting cannot be done for other URLs because it a comma can appear in a URL itself generally, but the syntax can break in srcset,
		 * unless the commas are URL-encoded.
		 */
		if ( 'srcset' === $attribute_node->nodeName || 'srcset' === $spec_attr_name ) {
			return array_filter( array_map(
				function ( $srcset_part ) {
					// Remove descriptors for width and pixel density.
					return preg_replace( '/\s.*$/', '', trim( $srcset_part ) );
				},
				preg_split( '/\s*,\s*/', $attribute_node->nodeValue )
			) );
		} else {
			return array( $attribute_node->nodeValue );
		}
	}

	/**
	 * Check if attribute has disallowed relative URL value according to rule spec.
	 *
	 * @param DOMElement       $node           Node.
	 * @param string           $attr_name      Attribute name.
	 * @param array[]|string[] $attr_spec_rule Attribute spec rule.
	 *
	 * @return string:
	 *      - AMP_Rule_Spec::PASS - $attr_name has a value that matches the rule.
	 *      - AMP_Rule_Spec::FAIL - $attr_name has a value that does *not* match rule.
	 *      - AMP_Rule_Spec::NOT_APPLICABLE - $attr_name does not exist or there
	 *                                        is no rule for this attribute.
	 */
	private function check_attr_spec_rule_disallowed_relative( $node, $attr_name, $attr_spec_rule ) {
		if ( isset( $attr_spec_rule[ AMP_Rule_Spec::VALUE_URL ][ AMP_Rule_Spec::ALLOW_RELATIVE ] ) && ! ( $attr_spec_rule[ AMP_Rule_Spec::VALUE_URL ][ AMP_Rule_Spec::ALLOW_RELATIVE ] ) ) {
			if ( $node->hasAttribute( $attr_name ) ) {
				foreach ( $this->extract_attribute_urls( $node->getAttributeNode( $attr_name ) ) as $url ) {
					$parsed_url = wp_parse_url( $url );

					/*
					 *  The JS AMP validator seems to consider 'relative' to mean
					 *  *protocol* relative, not *host* relative for this rule. So,
					 *  a url with an empty 'scheme' is considered "relative" by AMP.
					 *  ie. '//domain.com/path' and '/path' should both be considered
					 *  relative for purposes of AMP validation.
					 */
					if ( empty( $parsed_url['scheme'] ) ) {
						return AMP_Rule_Spec::FAIL;
					}
				}
				return AMP_Rule_Spec::PASS;
			} elseif ( isset( $attr_spec_rule[ AMP_Rule_Spec::ALTERNATIVE_NAMES ] ) ) {
				foreach ( $attr_spec_rule[ AMP_Rule_Spec::ALTERNATIVE_NAMES ] as $alternative_name ) {
					if ( $node->hasAttribute( $alternative_name ) ) {
						foreach ( $this->extract_attribute_urls( $node->getAttributeNode( $alternative_name ), $attr_name ) as $url ) {
							$parsed_url = wp_parse_url( $url );
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
	 * @param DOMElement       $node           Node.
	 * @param string           $attr_name      Attribute name.
	 * @param array[]|string[] $attr_spec_rule Attribute spec rule.
	 *
	 * @return string:
	 *      - AMP_Rule_Spec::PASS - $attr_name has a value that matches the rule.
	 *      - AMP_Rule_Spec::FAIL - $attr_name has a value that does *not* match rule.
	 *      - AMP_Rule_Spec::NOT_APPLICABLE - $attr_name does not exist or there
	 *                                        is no rule for this attribute.
	 */
	private function check_attr_spec_rule_disallowed_empty( $node, $attr_name, $attr_spec_rule ) {
		if ( isset( $attr_spec_rule[ AMP_Rule_Spec::VALUE_URL ][ AMP_Rule_Spec::ALLOW_EMPTY ] ) && ! ( $attr_spec_rule[ AMP_Rule_Spec::VALUE_URL ][ AMP_Rule_Spec::ALLOW_EMPTY ] ) && $node->hasAttribute( $attr_name ) ) {
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
	 * @param DOMElement       $node           Node.
	 * @param string           $attr_name      Attribute name.
	 * @param array[]|string[] $attr_spec_rule Attribute spec rule.
	 *
	 * @return string:
	 *      - AMP_Rule_Spec::PASS - $attr_name has a value that matches the rule.
	 *      - AMP_Rule_Spec::FAIL - $attr_name has a value that does *not* match rule.
	 *      - AMP_Rule_Spec::NOT_APPLICABLE - $attr_name does not exist or there
	 *                                        is no rule for this attribute.
	 */
	private function check_attr_spec_rule_disallowed_domain( $node, $attr_name, $attr_spec_rule ) {
		if ( isset( $attr_spec_rule[ AMP_Rule_Spec::DISALLOWED_DOMAIN ] ) && $node->hasAttribute( $attr_name ) ) {
			$attr_value = $node->getAttribute( $attr_name );
			$url_domain = wp_parse_url( $attr_value, PHP_URL_HOST );
			if ( ! empty( $url_domain ) ) {
				foreach ( $attr_spec_rule[ AMP_Rule_Spec::DISALLOWED_DOMAIN ] as $disallowed_domain ) {
					if ( strtolower( $url_domain ) === strtolower( $disallowed_domain ) ) {

						// Found a disallowed domain, fail validation.
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
	 * @param DOMElement       $node           Node.
	 * @param string           $attr_name      Attribute name.
	 * @param array[]|string[] $attr_spec_rule Attribute spec rule.
	 *
	 * @return string:
	 *      - AMP_Rule_Spec::PASS - $attr_name has a value that matches the rule.
	 *      - AMP_Rule_Spec::FAIL - $attr_name has a value that does *not* match rule.
	 *      - AMP_Rule_Spec::NOT_APPLICABLE - $attr_name does not exist or there
	 *                                        is no rule for this attribute.
	 */
	private function check_attr_spec_rule_blacklisted_value_regex( $node, $attr_name, $attr_spec_rule ) {
		if ( isset( $attr_spec_rule[ AMP_Rule_Spec::BLACKLISTED_VALUE_REGEX ] ) ) {
			$pattern = '/' . $attr_spec_rule[ AMP_Rule_Spec::BLACKLISTED_VALUE_REGEX ] . '/u';
			if ( $node->hasAttribute( $attr_name ) ) {
				$attr_value = $node->getAttribute( $attr_name );
				if ( preg_match( $pattern, $attr_value ) ) {
					return AMP_Rule_Spec::FAIL;
				} else {
					return AMP_Rule_Spec::PASS;
				}
			} elseif ( isset( $attr_spec_rule[ AMP_Rule_Spec::ALTERNATIVE_NAMES ] ) ) {
				foreach ( $attr_spec_rule[ AMP_Rule_Spec::ALTERNATIVE_NAMES ] as $alternative_name ) {
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
	 * Check if attribute has valid properties.
	 *
	 * @since 0.7
	 *
	 * @param DOMElement       $node           Node.
	 * @param string           $attr_name      Attribute name.
	 * @param array[]|string[] $attr_spec_rule Attribute spec rule.
	 *
	 * @return string:
	 *      - AMP_Rule_Spec::PASS - $attr_name has a value that matches the rule.
	 *      - AMP_Rule_Spec::FAIL - $attr_name has a value that does *not* match rule.
	 *      - AMP_Rule_Spec::NOT_APPLICABLE - $attr_name does not exist or there
	 *                                        is no rule for this attribute.
	 */
	private function check_attr_spec_rule_value_properties( $node, $attr_name, $attr_spec_rule ) {
		if ( isset( $attr_spec_rule[ AMP_Rule_Spec::VALUE_PROPERTIES ] ) && $node->hasAttribute( $attr_name ) ) {
			$properties = array();
			foreach ( explode( ',', $node->getAttribute( $attr_name ) ) as $pair ) {
				$pair_parts = explode( '=', $pair, 2 );
				if ( 2 !== count( $pair_parts ) ) {
					return 0;
				}
				$properties[ trim( strtolower( $pair_parts[0] ) ) ] = trim( $pair_parts[1] );
			}

			// Fail if there are unrecognized properties.
			if ( count( array_diff( array_keys( $properties ), array_keys( $attr_spec_rule[ AMP_Rule_Spec::VALUE_PROPERTIES ] ) ) ) > 0 ) {
				return AMP_Rule_Spec::FAIL;
			}

			foreach ( $attr_spec_rule[ AMP_Rule_Spec::VALUE_PROPERTIES ] as $prop_name => $property_spec ) {

				// Mandatory property is missing.
				if ( ! empty( $property_spec['mandatory'] ) && ! isset( $properties[ $prop_name ] ) ) {
					return AMP_Rule_Spec::FAIL;
				}

				if ( ! isset( $properties[ $prop_name ] ) ) {
					continue;
				}

				$prop_value = $properties[ $prop_name ];

				// Required value is absent, so fail.
				$required_value = null;
				if ( isset( $property_spec['value'] ) ) {
					$required_value = $property_spec['value'];
				} elseif ( isset( $property_spec['value_double'] ) ) {
					$required_value = $property_spec['value_double'];
					$prop_value     = (double) $prop_value;
				}
				if ( isset( $required_value ) && $prop_value !== $required_value ) {
					return AMP_Rule_Spec::FAIL;
				}
			}
			return AMP_Rule_Spec::PASS;
		}
		return AMP_Rule_Spec::NOT_APPLICABLE;
	}

	/**
	 * Determine if the supplied attribute name is allowed for AMP.
	 *
	 * @since 0.5
	 *
	 * @param DOMAttr          $attr_node      Attribute node.
	 * @param array[]|string[] $attr_spec_list Attribute spec list.
	 * @return bool Return true if attribute name is valid for this attr_spec_list, false otherwise.
	 */
	private function is_amp_allowed_attribute( $attr_node, $attr_spec_list ) {
		$attr_name = $attr_node->nodeName;
		if (
			isset( $attr_spec_list[ $attr_name ] )
			||
			'data-' === substr( $attr_name, 0, 5 )
			||
			// Allow the 'amp' or '⚡' attribute in <html>, like <html ⚡>.
			( 'html' === $attr_node->parentNode->nodeName && in_array( $attr_node->nodeName, array( 'amp', '⚡' ), true ) )
		) {
			return true;
		}

		$is_allowed_alt_name_attr = (
			isset( $this->rev_alternate_attr_name_lookup[ $attr_name ] )
			&&
			isset( $attr_spec_list[ $this->rev_alternate_attr_name_lookup[ $attr_name ] ] )
		);
		if ( $is_allowed_alt_name_attr ) {
			return true;
		}

		/*
		 * Handle special case for amp-selector where its reference points do not have to be direct children.
		 * This is noted as a special case in the AMP validator spec for amp-selector, so that is why it is
		 * a special case here in this method. It is also implemented in this way for the sake of efficiency
		 * to prevent having to waste time in process_node() merging attribute lists. For more on amp-selector's
		 * unique reference point, see:
		 * https://github.com/ampproject/amphtml/blob/1526498116488/extensions/amp-selector/validator-amp-selector.protoascii#L81-L91
		 */
		$reference_point_spec = AMP_Allowed_Tags_Generated::get_reference_point_spec( 'AMP-SELECTOR option' );
		if ( isset( $reference_point_spec[ AMP_Rule_Spec::ATTR_SPEC_LIST ][ $attr_name ] ) ) {
			$parent = $attr_node->parentNode;
			while ( $parent ) {
				if ( 'amp-selector' === $parent->nodeName ) {
					return true;
				}
				$parent = $parent->parentNode;
			}
		}

		return false;
	}

	/**
	 * Determine if the supplied $node's HTML tag is allowed for AMP.
	 *
	 * @since 0.5
	 *
	 * @param DOMNode $node Node.
	 * @return bool Return true if the specified node's name is an AMP allowed tag, false otherwise.
	 */
	private function is_amp_allowed_tag( $node ) {
		if ( ! $node instanceof DOMElement ) {
			return false;
		}
		/**
		 * Return true if node is an allowed tags or is a text or comment node.
		 */
		return (
			( XML_TEXT_NODE === $node->nodeType ) ||
			isset( $this->allowed_tags[ $node->nodeName ] ) ||
			( XML_COMMENT_NODE === $node->nodeType ) ||
			( XML_CDATA_SECTION_NODE === $node->nodeType )
		);
	}

	/**
	 * Determine if the supplied $node has a parent with the specified spec name.
	 *
	 * @since 0.5
	 *
	 * @todo It would be more robust if the the actual tag spec were looked up and then matched against the parent, but this is currently overkill.
	 *
	 * @param DOMNode $node             Node.
	 * @param string  $parent_spec_name Parent spec name, for example 'body' or 'form [method=post]'.
	 * @return bool Return true if given node has direct parent with the given name, false otherwise.
	 */
	private function has_parent( $node, $parent_spec_name ) {
		if ( ! $node ) {
			return false;
		}
		$parsed_spec_name = $this->parse_tag_and_attributes_from_spec_name( $parent_spec_name );

		if ( ! $node->parentNode || $node->parentNode->nodeName !== $parsed_spec_name['tag_name'] ) {
			return false;
		}

		// Ensure attributes match; if not move up to the next node.
		foreach ( $parsed_spec_name['attributes'] as $attr_name => $attr_value ) {
			if ( $node instanceof DOMElement && strtolower( $node->getAttribute( $attr_name ) ) !== $attr_value ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Determine if the supplied $node has an ancestor with the specified tag name.
	 *
	 * @since 0.5
	 *
	 * @param DOMNode $node              Node.
	 * @param string  $ancestor_tag_name Ancestor tag name.
	 * @return bool Return true if given node has any ancestor with the give name, false otherwise.
	 */
	private function has_ancestor( $node, $ancestor_tag_name ) {
		if ( $this->get_ancestor_with_matching_spec_name( $node, $ancestor_tag_name ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Parse tag name and attributes from spec name.
	 *
	 * Given a spec name like 'form [method=post]', extract the tag name 'form' and the attributes.
	 *
	 * @todo This is admittedly rudimentary. It would be more robust to actually look up the tag spec by the name and obtain the required attributes from there, but this is not necessary yet.
	 *
	 * @param string $spec_name Spec name.
	 * @return array Tag name and attributes.
	 */
	private function parse_tag_and_attributes_from_spec_name( $spec_name ) {
		static $parsed_specs = array();
		if ( isset( $parsed_specs[ $spec_name ] ) ) {
			return $parsed_specs[ $spec_name ];
		}

		$attributes = array();

		/*
		 * This matches spec names like:
		 * - body
		 * - form [method=post]
		 * - form > div [submit-error][template]
		 */
		if ( preg_match( '/^(?P<ancestors>.+? )?(?P<tag_name>[a-z0-9_-]+?)( (?P<raw_attrs>\[.+?\]))?$/i', $spec_name, $matches ) ) {
			$tag_name = $matches['tag_name'];

			if ( isset( $matches['raw_attrs'] ) ) {
				$raw_attr_pairs = explode( '][', trim( $matches['raw_attrs'], '[]' ) );
				foreach ( $raw_attr_pairs as $raw_attr_pair ) {
					$raw_attr_pair = explode( '=', $raw_attr_pair );

					$attr_key = $raw_attr_pair[0];
					$attr_val = isset( $raw_attr_pair[1] ) ? $raw_attr_pair[1] : true;

					$attributes[ $attr_key ] = $attr_val;
				}
			}
		} else {
			$tag_name = strtok( $spec_name, ' ' ); // Fallback case.
		}

		$parsed_specs[ $spec_name ] = compact( 'tag_name', 'attributes' );
		return $parsed_specs[ $spec_name ];
	}

	/**
	 * Get the first ancestor node matching the specified tag name for the supplied $node.
	 *
	 * @since 0.5
	 * @todo It would be more robust if the the actual tag spec were looked up and then matched for each ancestor, but this is currently overkill.
	 *
	 * @param DOMNode $node               Node.
	 * @param string  $ancestor_spec_name Ancestor spec name, e.g. 'body' or 'form [method=post]'.
	 * @return DOMNode|null Returns an ancestor node for the name specified, or null if not found.
	 */
	private function get_ancestor_with_matching_spec_name( $node, $ancestor_spec_name ) {
		$parsed_spec_name = $this->parse_tag_and_attributes_from_spec_name( $ancestor_spec_name );

		while ( $node && $node->parentNode ) {
			$node = $node->parentNode;
			if ( $node->nodeName === $parsed_spec_name['tag_name'] ) {

				// Ensure attributes match; if not move up to the next node.
				foreach ( $parsed_spec_name['attributes'] as $attr_name => $attr_value ) {
					$match = (
						$node instanceof DOMElement
						&&
						true === $attr_value ? $node->hasAttribute( $attr_name ) : strtolower( $node->getAttribute( $attr_name ) ) === $attr_value
					);
					if ( ! $match ) {
						continue 2;
					}
				}

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
	 * @since 1.0 Fix silently removing unrecognized elements.
	 * @see https://github.com/Automattic/amp-wp/issues/1100
	 *
	 * @param DOMNode $node Node.
	 */
	private function replace_node_with_children( $node ) {

		// If node has no children or no parent, just remove the node.
		if ( ! $node->hasChildNodes() || ! $node->parentNode ) {
			$this->remove_node( $node );

			return;
		}

		/*
		 * If node has children, replace it with them and push children onto stack
		 *
		 * Create a DOM fragment to hold the children
		 */
		$fragment = $this->dom->createDocumentFragment();

		// Add all children to fragment/stack.
		$child = $node->firstChild;
		while ( $child ) {
			$fragment->appendChild( $child );
			$this->stack[] = $child;
			$child         = $node->firstChild;
		}

		// Prevent double-reporting nodes that are rejected for sanitization.
		if ( isset( $this->should_not_replace_nodes[ $node->nodeName ] ) && in_array( $node, $this->should_not_replace_nodes[ $node->nodeName ], true ) ) {
			return;
		}

		// Replace node with fragment.
		$should_replace = $this->should_sanitize_validation_error( array(), compact( 'node' ) );
		if ( $should_replace ) {
			$node->parentNode->replaceChild( $fragment, $node );
		} else {
			$this->should_not_replace_nodes[ $node->nodeName ][] = $node;
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
	 * @param DOMNode $node Node.
	 */
	private function remove_node( $node ) {
		/**
		 * Parent.
		 *
		 * @var DOMNode $parent
		 */
		$parent = $node->parentNode;
		if ( $node && $parent ) {
			$this->remove_invalid_child( $node );
		}
		while ( $parent && ! $parent->hasChildNodes() && $this->root_element !== $parent ) {
			$node   = $parent;
			$parent = $parent->parentNode;
			if ( $parent ) {
				$parent->removeChild( $node );
			}
		}
	}
}
