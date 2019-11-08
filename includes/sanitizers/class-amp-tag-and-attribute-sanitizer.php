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
	protected $rev_alternate_attr_name_lookup = [];

	/**
	 * Mapping of JSON-serialized tag spec to the number of instances encountered in the document.
	 *
	 * @var array
	 */
	protected $visited_unique_tag_specs = [];

	/**
	 * Default args.
	 *
	 * @since 0.5
	 *
	 * @var array
	 */
	protected $DEFAULT_ARGS = [];

	/**
	 * AMP script components that are discovered being required through sanitization.
	 *
	 * @var string[]
	 */
	protected $script_components = [];

	/**
	 * Keep track of nodes that should not be replaced to prevent duplicated validation errors since sanitization is rejected.
	 *
	 * @var array
	 */
	protected $should_not_replace_nodes = [];

	/**
	 * Keep track of the elements that are currently open.
	 *
	 * This is used to determine whether a node exists inside of a given element tree, speeding up has_ancestor checks
	 * as well as disabling attribute validation inside of templates.
	 *
	 * @see \AMP_Tag_And_Attribute_Sanitizer::has_ancestor()
	 * @since 1.3
	 * @var array
	 */
	protected $open_elements = [];

	/**
	 * AMP_Tag_And_Attribute_Sanitizer constructor.
	 *
	 * @since 0.5
	 *
	 * @param DOMDocument $dom  DOM.
	 * @param array       $args Args.
	 */
	public function __construct( $dom, $args = [] ) {
		$this->DEFAULT_ARGS = [
			'amp_allowed_tags'                => AMP_Allowed_Tags_Generated::get_allowed_tags(),
			'amp_globally_allowed_attributes' => AMP_Allowed_Tags_Generated::get_allowed_attributes(),
			'amp_layout_allowed_attributes'   => AMP_Allowed_Tags_Generated::get_layout_attributes(),
		];

		parent::__construct( $dom, $args );

		// @todo AMP dev mode should eventually be used instead of allow_dirty_styles.
		if ( ! empty( $this->args['allow_dirty_styles'] ) ) {

			// Allow style attribute on all elements.
			$this->args['amp_globally_allowed_attributes']['style'] = [];

			// Allow style elements.
			$this->args['amp_allowed_tags']['style'][] = [
				'attr_spec_list' => [
					'type' => [
						'value_casei' => 'text/css',
					],
				],
				'cdata'          => [],
				'tag_spec'       => [
					'spec_name' => 'style for Customizer preview',
				],
			];

			// Allow stylesheet links.
			$this->args['amp_allowed_tags']['link'][] = [
				'attr_spec_list' => [
					'async'       => [],
					'crossorigin' => [],
					'href'        => [
						'mandatory' => true,
					],
					'integrity'   => [],
					'media'       => [],
					'rel'         => [
						'dispatch_key' => 2,
						'mandatory'    => true,
						'value_casei'  => 'stylesheet',
					],
					'type'        => [
						'value_casei' => 'text/css',
					],
				],
				'tag_spec'       => [
					'spec_name' => 'link rel=stylesheet for Customizer preview', // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
				],
			];
		}

		// @todo AMP dev mode should eventually be used instead of allow_dirty_scripts.
		// Allow scripts if requested.
		if ( ! empty( $this->args['allow_dirty_scripts'] ) ) {
			$this->args['amp_allowed_tags']['script'][] = [
				'attr_spec_list' => [
					'type'  => [],
					'src'   => [],
					'async' => [],
					'defer' => [],
				],
				'cdata'          => [],
				'tag_spec'       => [
					'spec_name' => 'scripts for Customizer preview',
				],
			];
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

			unset( $rule_spec );
		}

		foreach ( $this->allowed_tags as &$tag_specs ) {
			foreach ( $tag_specs as &$tag_spec ) {
				if ( isset( $tag_spec[ AMP_Rule_Spec::ATTR_SPEC_LIST ] ) ) {
					$tag_spec[ AMP_Rule_Spec::ATTR_SPEC_LIST ] = $this->process_alternate_names( $tag_spec[ AMP_Rule_Spec::ATTR_SPEC_LIST ] );
				}
			}

			unset( $tag_spec );
		}

		unset( $tag_specs );

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
		$result = $this->sanitize_element( $this->root_element );
		if ( is_array( $result ) ) {
			$this->script_components = $result;
		}
	}

	/**
	 * Sanitize element.
	 *
	 * Walk the DOM tree with depth first search (DFS) with post order traversal (LRN).
	 *
	 * @param DOMElement $element Element.
	 * @return string[]|null Required component scripts from sanitizing an element tree, or null if the element was removed.
	 */
	private function sanitize_element( DOMElement $element ) {
		if ( ! isset( $this->open_elements[ $element->nodeName ] ) ) {
			$this->open_elements[ $element->nodeName ] = 0;
		}
		$this->open_elements[ $element->nodeName ]++;

		$script_components = [];

		// First recurse into children to sanitize descendants.
		// The check for $element->parentNode at each iteration is to make sure an invalid child didn't bubble up removed
		// ancestor nodes in AMP_Tag_And_Attribute_Sanitizer::remove_node().
		$this_child = $element->firstChild;
		while ( $this_child && $element->parentNode ) {
			$next_child = $this_child->nextSibling;
			if ( $this_child instanceof DOMElement ) {
				$result = $this->sanitize_element( $this_child );
				if ( is_array( $result ) ) {
					$script_components = array_merge(
						$script_components,
						$result
					);
				}
			} elseif ( $this_child instanceof DOMProcessingInstruction ) {
				$this->remove_invalid_child( $this_child, [ 'code' => 'invalid_processing_instruction' ] );
			}
			$this_child = $next_child;
		}

		// If the element is still in the tree, process it.
		// The element can currently be removed from the tree when processing children via AMP_Tag_And_Attribute_Sanitizer::remove_node().
		$was_removed = false;
		if ( $element->parentNode ) {
			$result = $this->process_node( $element );
			if ( is_array( $result ) ) {
				$script_components = array_merge( $script_components, $result );
			} else {
				$was_removed = true;
			}
		}

		$this->open_elements[ $element->nodeName ]--;

		if ( $was_removed ) {
			return null;
		}

		return $script_components;
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
	private function get_rule_spec_list_to_validate( DOMElement $node, $rule_spec ) {

		// Expand extension_spec into a set of attr_spec_list.
		if ( isset( $rule_spec[ AMP_Rule_Spec::TAG_SPEC ]['extension_spec'] ) ) {
			$extension_spec = $rule_spec[ AMP_Rule_Spec::TAG_SPEC ]['extension_spec'];
			$custom_attr    = 'amp-mustache' === $extension_spec['name'] ? 'custom-template' : 'custom-element';

			$rule_spec[ AMP_Rule_Spec::ATTR_SPEC_LIST ][ $custom_attr ] = [
				AMP_Rule_Spec::VALUE     => $extension_spec['name'],
				AMP_Rule_Spec::MANDATORY => true,
			];

			$versions = array_unique(
				array_merge(
					isset( $extension_spec['allowed_versions'] ) ? $extension_spec['allowed_versions'] : [],
					isset( $extension_spec['version'] ) ? $extension_spec['version'] : []
				)
			);

			$rule_spec[ AMP_Rule_Spec::ATTR_SPEC_LIST ]['src'] = [
				AMP_Rule_Spec::VALUE_REGEX => implode(
					'',
					[
						'^',
						preg_quote( 'https://cdn.ampproject.org/v0/' . $extension_spec['name'] . '-' ), // phpcs:ignore WordPress.PHP.PregQuoteDelimiter.Missing
						'(' . implode( '|', $versions ) . ')',
						'\.js$',
					]
				),
			];
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
	 * @param DOMElement $node Node.
	 * @return string[]|null Required scripts, or null if the element was removed.
	 */
	private function process_node( DOMElement $node ) {

		// Remove nodes with tags that have not been whitelisted.
		if ( ! $this->is_amp_allowed_tag( $node ) ) {

			// If it's not an allowed tag, replace the node with it's children.
			$this->replace_node_with_children( $node );

			// Return early since this node no longer exists.
			return null;
		}

		/*
		 * Compile a list of rule_specs to validate for this node
		 * based on tag name of the node.
		 */
		$rule_spec_list_to_validate = [];
		$rule_spec_list             = [];
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
			return null;
		}

		// The remaining validations all have to do with attributes.
		$attr_spec_list = [];
		$tag_spec       = [];
		$cdata          = [];

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
			$attr_spec_scores = [];
			foreach ( $rule_spec_list_to_validate as $spec_id => $rule_spec ) {
				$attr_spec_scores[ $spec_id ] = $this->validate_attr_spec_list_for_node( $node, $rule_spec[ AMP_Rule_Spec::ATTR_SPEC_LIST ] );
			}

			// Remove all spec lists that didn't match.
			$attr_spec_scores = array_filter( $attr_spec_scores );

			// If no attribute spec lists match, then the element must be removed.
			if ( empty( $attr_spec_scores ) ) {
				$this->remove_node( $node );
				return null;
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
			return null;
		}

		// Remove element if it has illegal CDATA.
		if ( ! empty( $cdata ) && $node instanceof DOMElement ) {
			$validity = $this->validate_cdata_for_node( $node, $cdata );
			if ( is_wp_error( $validity ) ) {
				$this->remove_node( $node );
				return null;
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

		// Enforce unique constraint.
		if ( ! empty( $tag_spec['unique'] ) ) {
			$removed      = false;
			$tag_spec_key = wp_json_encode( $tag_spec );
			if ( ! empty( $this->visited_unique_tag_specs[ $node->nodeName ][ $tag_spec_key ] ) ) {
				$removed = $this->remove_invalid_child(
					$node,
					[ 'code' => 'duplicate_element' ]
				);
			}
			$this->visited_unique_tag_specs[ $node->nodeName ][ $tag_spec_key ] = true;
			if ( $removed ) {
				return null;
			}
		}

		// Identify any remaining disallowed attributes.
		$disallowed_attributes = $this->get_disallowed_attributes_in_node( $node, $merged_attr_spec_list );

		// Identify attribute values that don't conform to the attr_spec.
		$disallowed_attributes = $this->sanitize_disallowed_attribute_values_in_node( $node, $merged_attr_spec_list, $disallowed_attributes );

		// If $disallowed_attributes is false then the entire element should be removed.
		if ( false === $disallowed_attributes ) {
			$this->remove_node( $node );
			return null;
		}

		// Remove all invalid attributes.
		if ( ! empty( $disallowed_attributes ) ) {
			/*
			 * Capture all element attributes up front so that differing validation errors result when
			 * one invalid attribute is accepted but the others are still rejected.
			 */
			$validation_error = [
				'element_attributes' => [],
			];
			foreach ( $node->attributes as $attribute ) {
				$validation_error['element_attributes'][ $attribute->nodeName ] = $attribute->nodeValue;
			}
			$removed_attributes = [];
			foreach ( $disallowed_attributes as $disallowed_attribute ) {
				if ( $this->remove_invalid_attribute( $node, $disallowed_attribute, $validation_error ) ) {
					$removed_attributes[] = $disallowed_attribute;
				}
			}

			/*
			 * Only run cleanup after the fact to prevent a scenario where invalid markup is kept and so the attribute
			 * is actually not removed. This prevents a "DOMException: Not Found Error" from happening when calling
			 * remove_invalid_attribute() since clean_up_after_attribute_removal() can end up removing invalid link
			 * attributes (like 'target') when there is an invalid 'href' attribute, but if the 'target' attribute is
			 * itself invalid, then if clean_up_after_attribute_removal() is called inside of remove_invalid_attribute()
			 * it can cause a subsequent invocation of remove_invalid_attribute() to try to remove an invalid
			 * attribute that has already been removed from the DOM.
			 */
			foreach ( $removed_attributes as $removed_attribute ) {
				$this->clean_up_after_attribute_removal( $node, $removed_attribute );
			}
		}

		// Add required AMP component scripts.
		$script_components = [];
		if ( ! empty( $tag_spec['also_requires_tag_warning'] ) ) {
			$script_components[] = strtok( $tag_spec['also_requires_tag_warning'][0], ' ' );
		}
		if ( ! empty( $tag_spec['requires_extension'] ) ) {
			$script_components = array_merge( $script_components, $tag_spec['requires_extension'] );
		}

		// Add required AMP components for attributes.
		foreach ( $node->attributes as $attribute ) {
			if ( isset( $merged_attr_spec_list[ $attribute->nodeName ]['requires_extension'] ) ) {
				$script_components = array_merge(
					$script_components,
					$merged_attr_spec_list[ $attribute->nodeName ]['requires_extension']
				);
			}
		}

		// Manually add components for attributes; this is hard-coded because attributes do not have requires_extension like tags do. See <https://github.com/ampproject/amp-wp/issues/1808>.
		if ( $node->hasAttribute( 'lightbox' ) ) {
			$script_components[] = 'amp-lightbox-gallery';
		}

		// Check if element needs amp-bind component.
		if ( $node instanceof DOMElement && ! in_array( 'amp-bind', $this->script_components, true ) ) {
			foreach ( $node->attributes as $name => $value ) {
				if ( AMP_DOM_Utils::AMP_BIND_DATA_ATTR_PREFIX === substr( $name, 0, 14 ) ) {
					$script_components[] = 'amp-bind';
					break;
				}
			}
		}

		return $script_components;
	}

	/**
	 * Whether a node is missing a mandatory attribute.
	 *
	 * @param array      $attr_spec The attribute specification.
	 * @param DOMElement $node      The DOMElement of the node to check.
	 * @return boolean $is_missing boolean Whether a required attribute is missing.
	 */
	public function is_missing_mandatory_attribute( $attr_spec, DOMElement $node ) {
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
	private function validate_cdata_for_node( DOMElement $element, $cdata_spec ) {
		if (
			isset( $cdata_spec['max_bytes'] ) && strlen( $element->textContent ) > $cdata_spec['max_bytes']
			&&
			// Skip the <style amp-custom> tag, as we want to display it even with an excessive size if it passed the style sanitizer.
			// This would mean that AMP was disabled to not break the styling.
			! ( 'style' === $element->nodeName && $element->hasAttribute( 'amp-custom' ) )
		) {
			return new WP_Error( 'excessive_bytes' );
		}
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
	 * @param DOMElement $node     The node to validate.
	 * @param array      $tag_spec The specification.
	 * @return boolean $valid Whether the node's placement is valid.
	 */
	private function validate_tag_spec_for_node( DOMElement $node, $tag_spec ) {

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

		if ( ! empty( $tag_spec[ AMP_Rule_Spec::DESCENDANT_TAG_LIST ] ) ) {
			$allowed_tags = AMP_Allowed_Tags_Generated::get_descendant_tag_list( $tag_spec[ AMP_Rule_Spec::DESCENDANT_TAG_LIST ] );
			if ( ! empty( $allowed_tags ) ) {
				$this->remove_disallowed_descendants( $node, $allowed_tags );
			}
		}

		return ! ( ! empty( $tag_spec[ AMP_Rule_Spec::CHILD_TAGS ] ) && ! $this->check_valid_children( $node, $tag_spec[ AMP_Rule_Spec::CHILD_TAGS ] ) );
	}

	/**
	 * Checks to see if a spec is potentially valid.
	 *
	 * Checks the given node based on the attributes present in the node.
	 *
	 * @note This can be a very expensive function. Use it sparingly.
	 *
	 * @param DOMElement $node           Node.
	 * @param array[]    $attr_spec_list Attribute Spec list.
	 *
	 * @return float Number of times the attribute spec list matched. If there was a mismatch, then 0 is returned. 0.5 is returned if there is an implicit match.
	 */
	private function validate_attr_spec_list_for_node( DOMElement $node, $attr_spec_list ) {
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
	 * @param DOMElement $node           Node.
	 * @param array[]    $attr_spec_list Attribute spec list.
	 * @return DOMAttr[] Attributes to remove.
	 */
	private function get_disallowed_attributes_in_node( DOMElement $node, $attr_spec_list ) {
		/*
		 * We can't remove attributes inside the 'foreach' loop without
		 * breaking the iteration. So we keep track of the attributes to
		 * remove in the first loop, then remove them in the second loop.
		 */
		$attrs_to_remove = [];
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
	 * @param DOMElement $node                       Node.
	 * @param array[][]  $attr_spec_list             Attribute spec list.
	 * @param DOMAttr[]  $attributes_pending_removal Attributes pending removal.
	 * @return DOMAttr[]|false Attributes to remove, or false if the element itself should be removed.
	 */
	private function sanitize_disallowed_attribute_values_in_node( DOMElement $node, $attr_spec_list, $attributes_pending_removal ) {
		$attrs_to_remove = [];

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

			// Check the context to see if we are currently within a template tag.
			// If this is the case and the attribute value contains a template placeholder, we skip sanitization.
			if ( ! empty( $this->open_elements['template'] ) && preg_match( '/{{.*?}}/', $attr_node->nodeValue ) ) {
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
			if ( isset( $attr_spec_list[ $attr_node->nodeName ][ AMP_Rule_Spec::VALUE_URL ] ) &&
				'href' === $attr_node->nodeName ) {
				$attributes_pending_removal[] = $attr_node;
			} elseif ( isset( $attr_spec_list[ $attr_node->nodeName ][ AMP_Rule_Spec::VALUE_URL ][ AMP_Rule_Spec::ALLOW_EMPTY ] ) &&
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
	private function check_attr_spec_rule_mandatory( DOMElement $node, $attr_name, $attr_spec_rule ) {
		if ( isset( $attr_spec_rule[ AMP_Rule_Spec::MANDATORY ] ) && $attr_spec_rule[ AMP_Rule_Spec::MANDATORY ] ) {
			if ( $node->hasAttribute( $attr_name ) ) {
				return AMP_Rule_Spec::PASS;
			}

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
	private function check_attr_spec_rule_value( DOMElement $node, $attr_name, $attr_spec_rule ) {
		if ( isset( $attr_spec_rule[ AMP_Rule_Spec::VALUE ] ) ) {
			if ( $node->hasAttribute( $attr_name ) ) {
				if ( $this->check_matching_attribute_value( $attr_name, $node->getAttribute( $attr_name ), $attr_spec_rule[ AMP_Rule_Spec::VALUE ] ) ) {
					return AMP_Rule_Spec::PASS;
				}

				return AMP_Rule_Spec::FAIL;
			}

			if ( isset( $attr_spec_rule[ AMP_Rule_Spec::ALTERNATIVE_NAMES ] ) ) {
				foreach ( $attr_spec_rule[ AMP_Rule_Spec::ALTERNATIVE_NAMES ] as $alternative_name ) {
					if ( $node->hasAttribute( $alternative_name ) ) {
						if ( $this->check_matching_attribute_value( $attr_name, $node->getAttribute( $alternative_name ), $attr_spec_rule[ AMP_Rule_Spec::VALUE ] ) ) {
							return AMP_Rule_Spec::PASS;
						}

						return AMP_Rule_Spec::FAIL;
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
	private function check_attr_spec_rule_value_casei( DOMElement $node, $attr_name, $attr_spec_rule ) {
		if ( ! isset( $attr_spec_rule[ AMP_Rule_Spec::VALUE_CASEI ] ) ) {
			return AMP_Rule_Spec::NOT_APPLICABLE;
		}

		$result      = AMP_Rule_Spec::NOT_APPLICABLE;
		$rule_values = (array) $attr_spec_rule[ AMP_Rule_Spec::VALUE_CASEI ];
		foreach ( $rule_values as $rule_value ) {
			$rule_value = strtolower( $rule_value );
			if ( $node->hasAttribute( $attr_name ) ) {
				$attr_value = strtolower( $node->getAttribute( $attr_name ) );
				if ( $attr_value === $rule_value ) {
					return AMP_Rule_Spec::PASS;
				}

				$result = AMP_Rule_Spec::FAIL;
			} elseif ( isset( $attr_spec_rule[ AMP_Rule_Spec::ALTERNATIVE_NAMES ] ) ) {
				foreach ( $attr_spec_rule[ AMP_Rule_Spec::ALTERNATIVE_NAMES ] as $alternative_name ) {
					if ( $node->hasAttribute( $alternative_name ) ) {
						$attr_value = strtolower( $node->getAttribute( $alternative_name ) );
						if ( $attr_value === $rule_value ) {
							return AMP_Rule_Spec::PASS;
						}

						$result = AMP_Rule_Spec::FAIL;
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
	private function check_attr_spec_rule_value_regex( DOMElement $node, $attr_name, $attr_spec_rule ) {
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
			}

			return AMP_Rule_Spec::FAIL;
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
	private function check_attr_spec_rule_value_regex_casei( DOMElement $node, $attr_name, $attr_spec_rule ) {
		// Check 'value_regex_casei' - case insensitive regex match.
		if ( isset( $attr_spec_rule[ AMP_Rule_Spec::VALUE_REGEX_CASEI ] ) && $node->hasAttribute( $attr_name ) ) {
			$rule_value = $attr_spec_rule[ AMP_Rule_Spec::VALUE_REGEX_CASEI ];
			$rule_value = str_replace( '/', '\\/', $rule_value );

			// See note above regarding the '^' and '$' that are added here.
			if ( preg_match( '/^(' . $rule_value . ')$/ui', $node->getAttribute( $attr_name ) ) ) {
				return AMP_Rule_Spec::PASS;
			}

			return AMP_Rule_Spec::FAIL;
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
	private function check_attr_spec_rule_valid_url( DOMElement $node, $attr_name, $attr_spec_rule ) {
		if ( isset( $attr_spec_rule[ AMP_Rule_Spec::VALUE_URL ] ) && $node->hasAttribute( $attr_name ) ) {
			foreach ( $this->extract_attribute_urls( $node->getAttributeNode( $attr_name ) ) as $url ) {
				$url = urldecode( $url );

				// Check whether the URL is parseable.
				$parts = wp_parse_url( $url );
				if ( false === $parts ) {
					return AMP_Rule_Spec::FAIL;
				}

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
				if ( $host && preg_match( '/[!"#$%&\'()*+,\/:;<=>?@[\]^`{|}~\s]/', $host ) ) {
					return AMP_Rule_Spec::FAIL;
				}
			}

			return AMP_Rule_Spec::PASS;
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
	private function check_attr_spec_rule_allowed_protocol( DOMElement $node, $attr_name, $attr_spec_rule ) {
		if ( isset( $attr_spec_rule[ AMP_Rule_Spec::VALUE_URL ][ AMP_Rule_Spec::ALLOWED_PROTOCOL ] ) ) {
			if ( $node->hasAttribute( $attr_name ) ) {
				foreach ( $this->extract_attribute_urls( $node->getAttributeNode( $attr_name ) ) as $url ) {
					$url_scheme = $this->parse_protocol( $url );
					if ( isset( $url_scheme ) && ! in_array( strtolower( $url_scheme ), $attr_spec_rule[ AMP_Rule_Spec::VALUE_URL ][ AMP_Rule_Spec::ALLOWED_PROTOCOL ], true ) ) {
						return AMP_Rule_Spec::FAIL;
					}
				}
				return AMP_Rule_Spec::PASS;
			}

			if ( isset( $attr_spec_rule[ AMP_Rule_Spec::ALTERNATIVE_NAMES ] ) ) {
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
	private function extract_attribute_urls( DOMAttr $attribute_node, $spec_attr_name = null ) {
		/*
		 * Handle the srcset special case where the attribute value can contain multiple parts, each in the format `URL [WIDTH] [PIXEL_DENSITY]`.
		 * So we split the srcset attribute value by commas and then return the first token of each item, omitting width descriptor and pixel density descriptor.
		 * This splitting cannot be done for other URLs because it a comma can appear in a URL itself generally, but the syntax can break in srcset,
		 * unless the commas are URL-encoded.
		 */
		if ( 'srcset' === $attribute_node->nodeName || 'srcset' === $spec_attr_name ) {
			return array_filter(
				array_map(
					static function ( $srcset_part ) {
						// Remove descriptors for width and pixel density.
						return preg_replace( '/\s.*$/', '', trim( $srcset_part ) );
					},
					preg_split( '/\s*,\s*/', $attribute_node->nodeValue )
				)
			);
		}

		return [ $attribute_node->nodeValue ];
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
	private function check_attr_spec_rule_disallowed_relative( DOMElement $node, $attr_name, $attr_spec_rule ) {
		if ( isset( $attr_spec_rule[ AMP_Rule_Spec::VALUE_URL ][ AMP_Rule_Spec::ALLOW_RELATIVE ] ) && ! $attr_spec_rule[ AMP_Rule_Spec::VALUE_URL ][ AMP_Rule_Spec::ALLOW_RELATIVE ] ) {
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
			}

			if ( isset( $attr_spec_rule[ AMP_Rule_Spec::ALTERNATIVE_NAMES ] ) ) {
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
	private function check_attr_spec_rule_disallowed_empty( DOMElement $node, $attr_name, $attr_spec_rule ) {
		if ( isset( $attr_spec_rule[ AMP_Rule_Spec::VALUE_URL ][ AMP_Rule_Spec::ALLOW_EMPTY ] ) && ! $attr_spec_rule[ AMP_Rule_Spec::VALUE_URL ][ AMP_Rule_Spec::ALLOW_EMPTY ] && $node->hasAttribute( $attr_name ) ) {
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
	private function check_attr_spec_rule_disallowed_domain( DOMElement $node, $attr_name, $attr_spec_rule ) {
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
	private function check_attr_spec_rule_blacklisted_value_regex( DOMElement $node, $attr_name, $attr_spec_rule ) {
		if ( isset( $attr_spec_rule[ AMP_Rule_Spec::BLACKLISTED_VALUE_REGEX ] ) ) {
			$pattern = '/' . $attr_spec_rule[ AMP_Rule_Spec::BLACKLISTED_VALUE_REGEX ] . '/u';
			if ( $node->hasAttribute( $attr_name ) ) {
				$attr_value = $node->getAttribute( $attr_name );
				if ( preg_match( $pattern, $attr_value ) ) {
					return AMP_Rule_Spec::FAIL;
				}

				return AMP_Rule_Spec::PASS;
			}
			if ( isset( $attr_spec_rule[ AMP_Rule_Spec::ALTERNATIVE_NAMES ] ) ) {
				foreach ( $attr_spec_rule[ AMP_Rule_Spec::ALTERNATIVE_NAMES ] as $alternative_name ) {
					if ( $node->hasAttribute( $alternative_name ) ) {
						$attr_value = $node->getAttribute( $alternative_name );
						if ( preg_match( $pattern, $attr_value ) ) {
							return AMP_Rule_Spec::FAIL;
						}

						return AMP_Rule_Spec::PASS;
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
	private function check_attr_spec_rule_value_properties( DOMElement $node, $attr_name, $attr_spec_rule ) {
		if ( isset( $attr_spec_rule[ AMP_Rule_Spec::VALUE_PROPERTIES ] ) && $node->hasAttribute( $attr_name ) ) {
			$properties = [];
			foreach ( explode( ',', $node->getAttribute( $attr_name ) ) as $pair ) {
				$pair_parts = explode( '=', $pair, 2 );
				if ( 2 !== count( $pair_parts ) ) {
					return 0;
				}
				$properties[ strtolower( trim( $pair_parts[0] ) ) ] = trim( $pair_parts[1] );
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
					$prop_value     = (float) $prop_value;
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
	private function is_amp_allowed_attribute( DOMAttr $attr_node, $attr_spec_list ) {
		$attr_name = $attr_node->nodeName;
		if (
			isset( $attr_spec_list[ $attr_name ] )
			||
			( 'data-' === substr( $attr_name, 0, 5 ) && AMP_DOM_Utils::AMP_BIND_DATA_ATTR_PREFIX !== substr( $attr_name, 0, 14 ) )
			||
			// Allow the 'amp' or '⚡' attribute in <html>, like <html ⚡>.
			( 'html' === $attr_node->parentNode->nodeName && in_array( $attr_node->nodeName, [ 'amp', '⚡' ], true ) )
		) {
			return true;
		}

		$is_allowed_alt_name_attr = isset( $this->rev_alternate_attr_name_lookup[ $attr_name ], $attr_spec_list[ $this->rev_alternate_attr_name_lookup[ $attr_name ] ] );
		if ( $is_allowed_alt_name_attr ) {
			return true;
		}

		/*
		 * Handle special case for reference points which do not have to be direct children.
		 * This is noted as a special case in the AMP validator spec for amp-selector, so that is why it is
		 * a special case here in this method. It is also implemented in this way for the sake of efficiency
		 * to prevent having to waste time in process_node() merging attribute lists. For more on amp-selector's
		 * unique reference point, see:
		 * https://github.com/ampproject/amphtml/blob/1526498116488/extensions/amp-selector/validator-amp-selector.protoascii#L81-L91
		 */
		$descendant_reference_points = [
			'amp-selector'         => AMP_Allowed_Tags_Generated::get_reference_point_spec( 'AMP-SELECTOR option' ),
			'amp-story-grid-layer' => AMP_Allowed_Tags_Generated::get_reference_point_spec( 'AMP-STORY-GRID-LAYER default' ), // @todo Consider the more restrictive 'AMP-STORY-GRID-LAYER animate-in'.
		];
		foreach ( $descendant_reference_points as $ancestor_name => $reference_point_spec ) {
			if ( isset( $reference_point_spec[ AMP_Rule_Spec::ATTR_SPEC_LIST ][ $attr_name ] ) ) {
				$parent = $attr_node->parentNode;
				while ( $parent ) {
					if ( $ancestor_name === $parent->nodeName ) {
						return true;
					}
					$parent = $parent->parentNode;
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
	 * @param DOMElement $node Node.
	 * @return bool Return true if the specified node's name is an AMP allowed tag, false otherwise.
	 */
	private function is_amp_allowed_tag( DOMElement $node ) {
		return isset( $this->allowed_tags[ $node->nodeName ] );
	}

	/**
	 * Determine if the supplied $node has a parent with the specified spec name.
	 *
	 * @since 0.5
	 *
	 * @todo It would be more robust if the the actual tag spec were looked up and then matched against the parent, but this is currently overkill.
	 *
	 * @param DOMElement $node             Node.
	 * @param string     $parent_spec_name Parent spec name, for example 'body' or 'form [method=post]'.
	 * @return bool Return true if given node has direct parent with the given name, false otherwise.
	 */
	private function has_parent( DOMElement $node, $parent_spec_name ) {
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
	 * @param DOMElement $node              Node.
	 * @param string     $ancestor_tag_spec_name Ancestor tag spec name. This looks somewhat like a CSS selector, e.g. 'form div [submitting][template]'.
	 * @return bool Return true if given node has any ancestor with the give name, false otherwise.
	 */
	private function has_ancestor( DOMElement $node, $ancestor_tag_spec_name ) {
		if ( $this->get_ancestor_with_matching_spec_name( $node, $ancestor_tag_spec_name ) ) {
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
		static $parsed_specs = [];
		if ( isset( $parsed_specs[ $spec_name ] ) ) {
			return $parsed_specs[ $spec_name ];
		}

		$attributes = [];

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
	 * Loop through node's descendants and remove the ones that are not whitelisted.
	 *
	 * @param DOMElement $node                Node.
	 * @param array      $allowed_descendants List of allowed descendant tags.
	 */
	private function remove_disallowed_descendants( DOMElement $node, $allowed_descendants ) {
		if ( ! $node->hasChildNodes() ) {
			return;
		}

		$child_elements = [];
		for ( $i = 0; $i < $node->childNodes->length; $i++ ) {
			$child = $node->childNodes->item( $i );
			if ( $child instanceof DOMElement ) {
				$child_elements[] = $child;
			}
		}

		foreach ( $child_elements as $child_element ) {
			if ( ! in_array( $child_element->nodeName, $allowed_descendants, true ) ) {
				$this->remove_invalid_child( $child_element );
			} else {
				$this->remove_disallowed_descendants( $child_element, $allowed_descendants );
			}
		}
	}

	/**
	 * Check whether the node validates the constraints for children.
	 *
	 * @param DOMElement $node Node.
	 * @param array      $child_tags {
	 *     List of allowed child tag.
	 *
	 *     @type array $first_child_tag_name_oneof   List of tag names that are allowed as the first element child.
	 *     @type array $child_tag_name_oneof         List of tag names that are allowed as children.
	 *     @type int   $mandatory_num_child_tags     Mandatory number of child tags.
	 *     @type int   $mandatory_min_num_child_tags Mandatory minimum number of child tags.
	 * }
	 * @return bool Whether the element satisfies the requirements, or else it should be removed.
	 */
	private function check_valid_children( DOMElement $node, $child_tags ) {
		$child_elements = [];
		for ( $i = 0; $i < $node->childNodes->length; $i++ ) {
			$child = $node->childNodes->item( $i );
			if ( $child instanceof DOMElement ) {
				$child_elements[] = $child;
			}
		}

		// If the first element is not of the required type, invalidate the entire element.
		if ( isset( $child_tags['first_child_tag_name_oneof'] ) && ! empty( $child_elements[0] ) && ! in_array( $child_elements[0]->nodeName, $child_tags['first_child_tag_name_oneof'], true ) ) {
			return false;
		}

		// Verify that all of the child are among the set of allowed elements.
		if ( isset( $child_tags['child_tag_name_oneof'] ) ) {
			foreach ( $child_elements as $child_element ) {
				if ( ! in_array( $child_element->nodeName, $child_tags['child_tag_name_oneof'], true ) ) {
					return false;
				}
			}
		}

		// If there aren't the exact number of elements, then mark this $node as being invalid.
		if ( isset( $child_tags['mandatory_num_child_tags'] ) ) {
			return count( $child_elements ) === $child_tags['mandatory_num_child_tags'];
		}

		// If there aren't enough elements, then mark this $node as being invalid.
		if ( isset( $child_tags['mandatory_min_num_child_tags'] ) ) {
			return count( $child_elements ) >= $child_tags['mandatory_min_num_child_tags'];
		}

		return true;
	}

	/**
	 * Get the first ancestor node matching the specified tag name for the supplied $node.
	 *
	 * @since 0.5
	 * @todo It would be more robust if the the actual tag spec were looked up and then matched for each ancestor, but this is currently overkill.
	 *
	 * @param DOMElement $node               Node.
	 * @param string     $ancestor_spec_name Ancestor spec name, e.g. 'body' or 'form [method=post]'.
	 * @return DOMNode|null Returns an ancestor node for the name specified, or null if not found.
	 */
	private function get_ancestor_with_matching_spec_name( DOMElement $node, $ancestor_spec_name ) {
		$parsed_spec_name = $this->parse_tag_and_attributes_from_spec_name( $ancestor_spec_name );

		// Do quick check to see if the the ancestor element is even open.
		// Note first isset check is for the sake of \AMP_Tag_And_Attribute_Sanitizer_Attr_Spec_Rules_Test::test_get_ancestor_with_matching_spec_name().
		if ( isset( $this->open_element['html'] ) && empty( $this->open_elements[ $parsed_spec_name['tag_name'] ] ) ) {
			return null;
		}

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
	 * @see https://github.com/ampproject/amp-wp/issues/1100
	 *
	 * @param DOMElement $node Node.
	 */
	private function replace_node_with_children( DOMElement $node ) {

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
			$child = $node->firstChild;
		}

		// Prevent double-reporting nodes that are rejected for sanitization.
		if ( isset( $this->should_not_replace_nodes[ $node->nodeName ] ) && in_array( $node, $this->should_not_replace_nodes[ $node->nodeName ], true ) ) {
			return;
		}

		// Replace node with fragment.
		$should_replace = $this->should_sanitize_validation_error( [], compact( 'node' ) );
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
	 * @param DOMElement $node Node.
	 */
	private function remove_node( DOMElement $node ) {
		/**
		 * Parent.
		 *
		 * @var DOMNode $parent
		 */
		$parent = $node->parentNode;
		if ( $node && $parent ) {
			$this->remove_invalid_child( $node );
		}

		// @todo Does this parent removal even make sense anymore?
		while ( $parent && ! $parent->hasChildNodes() && ! $parent->hasAttributes() && $this->root_element !== $parent ) {
			$node   = $parent;
			$parent = $parent->parentNode;
			if ( $parent ) {
				$parent->removeChild( $node );
			}
		}
	}
}
