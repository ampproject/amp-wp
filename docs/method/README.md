## Methods

* [`AMP_Base_Embed_Handler::__construct()`](AMP_Base_Embed_Handler/__construct.md) - Constructor.
* [`AMP_Base_Embed_Handler::create_overflow_button_element()`](AMP_Base_Embed_Handler/create_overflow_button_element.md) - Create overflow button element.
* [`AMP_Base_Embed_Handler::create_overflow_button_markup()`](AMP_Base_Embed_Handler/create_overflow_button_markup.md) - Create overflow button markup.
* [`AMP_Base_Embed_Handler::get_child_elements()`](AMP_Base_Embed_Handler/get_child_elements.md) - Get all child elements of the specified element.
* [`AMP_Base_Embed_Handler::get_scripts()`](AMP_Base_Embed_Handler/get_scripts.md) - Get mapping of AMP component names to AMP script URLs.
* [`AMP_Base_Embed_Handler::match_element_attributes()`](AMP_Base_Embed_Handler/match_element_attributes.md) - Get regex pattern for matching HTML attributes from a given tag name.
* [`AMP_Base_Embed_Handler::maybe_remove_script_sibling()`](AMP_Base_Embed_Handler/maybe_remove_script_sibling.md) - Removes the node&#039;s nearest `&lt;script&gt;` sibling with a `src` attribute containing the base `src` URL provided.
* [`AMP_Base_Embed_Handler::register_embed()`](AMP_Base_Embed_Handler/register_embed.md) - Registers embed.
* [`AMP_Base_Embed_Handler::unregister_embed()`](AMP_Base_Embed_Handler/unregister_embed.md) - Unregisters embed.
* [`AMP_Base_Embed_Handler::unwrap_p_element()`](AMP_Base_Embed_Handler/unwrap_p_element.md) - Replace an element&#039;s parent with itself if the parent is a &lt;p&gt; tag which has no attributes and has no other children.
* [`AMP_Base_Sanitizer::__construct()`](AMP_Base_Sanitizer/__construct.md) - AMP_Base_Sanitizer constructor.
* [`AMP_Base_Sanitizer::add_buffering_hooks()`](AMP_Base_Sanitizer/add_buffering_hooks.md) - Add filters to manipulate output during output buffering before the DOM is constructed.
* [`AMP_Base_Sanitizer::add_or_append_attribute()`](AMP_Base_Sanitizer/add_or_append_attribute.md) - Adds or appends key and value to list of attributes
* [`AMP_Base_Sanitizer::clean_up_after_attribute_removal()`](AMP_Base_Sanitizer/clean_up_after_attribute_removal.md) - Cleans up artifacts after the removal of an attribute node.
* [`AMP_Base_Sanitizer::filter_attachment_layout_attributes()`](AMP_Base_Sanitizer/filter_attachment_layout_attributes.md) - Set attributes to node&#039;s parent element according to layout.
* [`AMP_Base_Sanitizer::filter_data_amp_attributes()`](AMP_Base_Sanitizer/filter_data_amp_attributes.md) - Set AMP attributes.
* ~~[`AMP_Base_Sanitizer::get_body_node()`](AMP_Base_Sanitizer/get_body_node.md) - Get HTML body as DOMElement from Dom\Document received by the constructor.~~
* [`AMP_Base_Sanitizer::get_data_amp_attributes()`](AMP_Base_Sanitizer/get_data_amp_attributes.md) - Get data-amp-* values from the parent node &#039;figure&#039; added by editor block.
* [`AMP_Base_Sanitizer::get_scripts()`](AMP_Base_Sanitizer/get_scripts.md) - Return array of values that would be valid as an HTML `script` element.
* [`AMP_Base_Sanitizer::get_selector_conversion_mapping()`](AMP_Base_Sanitizer/get_selector_conversion_mapping.md) - Get mapping of HTML selectors to the AMP component selectors which they may be converted into.
* [`AMP_Base_Sanitizer::get_stylesheets()`](AMP_Base_Sanitizer/get_stylesheets.md) - Get stylesheets.
* [`AMP_Base_Sanitizer::get_validate_response_data()`](AMP_Base_Sanitizer/get_validate_response_data.md) - Get data that is returned in validate responses.
* ~~[`AMP_Base_Sanitizer::has_dev_mode_exemption()`](AMP_Base_Sanitizer/has_dev_mode_exemption.md) - Check whether a node is exempt from validation during dev mode.~~
* [`AMP_Base_Sanitizer::init()`](AMP_Base_Sanitizer/init.md) - Run logic before any sanitizers are run.
* ~~[`AMP_Base_Sanitizer::is_document_in_dev_mode()`](AMP_Base_Sanitizer/is_document_in_dev_mode.md) - Check whether the document of a given node is in dev mode.~~
* [`AMP_Base_Sanitizer::is_empty_attribute_value()`](AMP_Base_Sanitizer/is_empty_attribute_value.md) - Determine if an attribute value is empty.
* ~~[`AMP_Base_Sanitizer::is_exempt_from_validation()`](AMP_Base_Sanitizer/is_exempt_from_validation.md) - Check whether a certain node should be exempt from validation.~~
* [`AMP_Base_Sanitizer::maybe_enforce_https_src()`](AMP_Base_Sanitizer/maybe_enforce_https_src.md) - Decide if we should remove a src attribute if https is required.
* [`AMP_Base_Sanitizer::parse_style_string()`](AMP_Base_Sanitizer/parse_style_string.md) - Parse a style string into an associative array of style attributes.
* [`AMP_Base_Sanitizer::prepare_validation_error()`](AMP_Base_Sanitizer/prepare_validation_error.md) - Prepare validation error.
* [`AMP_Base_Sanitizer::reassemble_style_string()`](AMP_Base_Sanitizer/reassemble_style_string.md) - Reassemble a style string that can be used in a &#039;style&#039; attribute.
* [`AMP_Base_Sanitizer::remove_invalid_attribute()`](AMP_Base_Sanitizer/remove_invalid_attribute.md) - Removes an invalid attribute of a node.
* [`AMP_Base_Sanitizer::remove_invalid_child()`](AMP_Base_Sanitizer/remove_invalid_child.md) - Removes an invalid child of a node.
* [`AMP_Base_Sanitizer::sanitize()`](AMP_Base_Sanitizer/sanitize.md) - Sanitize the HTML contained in the DOMDocument received by the constructor
* [`AMP_Base_Sanitizer::sanitize_dimension()`](AMP_Base_Sanitizer/sanitize_dimension.md) - Sanitizes a CSS dimension specifier while being sensitive to dimension context.
* [`AMP_Base_Sanitizer::set_layout()`](AMP_Base_Sanitizer/set_layout.md) - Sets the layout, and possibly the &#039;height&#039; and &#039;width&#039; attributes.
* [`AMP_Base_Sanitizer::should_sanitize_validation_error()`](AMP_Base_Sanitizer/should_sanitize_validation_error.md) - Check whether or not sanitization should occur in response to validation error.
* [`AMP_Base_Sanitizer::update_args()`](AMP_Base_Sanitizer/update_args.md) - Update args.
* [`AMP_DOM_Utils::add_amp_action()`](AMP_DOM_Utils/add_amp_action.md) - Register an AMP action to an event on a given element.
* [`AMP_DOM_Utils::add_attributes_to_node()`](AMP_DOM_Utils/add_attributes_to_node.md) - Add one or more HTML element attributes to a node&#039;s DOMElement.
* [`AMP_DOM_Utils::copy_attributes()`](AMP_DOM_Utils/copy_attributes.md) - Copy one or more attributes from one element to the other.
* [`AMP_DOM_Utils::create_node()`](AMP_DOM_Utils/create_node.md) - Create a new node w/attributes (a DOMElement) and add to the passed Dom\Document.
* [`AMP_DOM_Utils::get_content_from_dom()`](AMP_DOM_Utils/get_content_from_dom.md) - Return valid HTML *body* content extracted from the Dom\Document passed as a parameter.
* ~~[`AMP_DOM_Utils::get_content_from_dom_node()`](AMP_DOM_Utils/get_content_from_dom_node.md) - Return valid HTML content extracted from the DOMNode passed as a parameter.~~
* ~~[`AMP_DOM_Utils::get_dom()`](AMP_DOM_Utils/get_dom.md) - Return a valid Dom\Document representing HTML document passed as a parameter.~~
* [`AMP_DOM_Utils::get_dom_from_content()`](AMP_DOM_Utils/get_dom_from_content.md) - Return a valid Dom\Document representing arbitrary HTML content passed as a parameter.
* ~~[`AMP_DOM_Utils::get_element_id()`](AMP_DOM_Utils/get_element_id.md) - Get the ID for an element.~~
* [`AMP_DOM_Utils::get_node_attributes_as_assoc_array()`](AMP_DOM_Utils/get_node_attributes_as_assoc_array.md) - Extract a DOMElement node&#039;s HTML element attributes and return as an array.
* [`AMP_DOM_Utils::has_class()`](AMP_DOM_Utils/has_class.md) - Check whether a given element has a specific class.
* [`AMP_DOM_Utils::is_node_empty()`](AMP_DOM_Utils/is_node_empty.md) - Determines if a DOMElement&#039;s node is empty or not.
* ~~[`AMP_DOM_Utils::is_valid_head_node()`](AMP_DOM_Utils/is_valid_head_node.md) - Determine whether a node can be in the head.~~
* [`AMP_DOM_Utils::merge_amp_actions()`](AMP_DOM_Utils/merge_amp_actions.md) - Merge two sets of AMP events &amp; actions.
* [`PairedUrl::add_path_suffix()`](PairedUrl/add_path_suffix.md) - Get paired AMP URL using a endpoint suffix.
* [`PairedUrl::add_query_var()`](PairedUrl/add_query_var.md) - Get paired AMP URL using query var (`?amp=1`).
* [`PairedUrl::has_path_suffix()`](PairedUrl/has_path_suffix.md) - Determine whether the given URL has the endpoint suffix.
* [`PairedUrl::has_query_var()`](PairedUrl/has_query_var.md) - Determine whether the given URL has the query var.
* [`PairedUrl::remove_path_suffix()`](PairedUrl/remove_path_suffix.md) - Strip paired endpoint suffix.
* [`PairedUrl::remove_query_var()`](PairedUrl/remove_query_var.md) - Strip paired query var.
* [`PairedUrlStructure::__construct()`](PairedUrlStructure/__construct.md) - PairedUrlStructure constructor.
* [`PairedUrlStructure::add_endpoint()`](PairedUrlStructure/add_endpoint.md) - Turn a given URL into a paired AMP URL.
* [`PairedUrlStructure::has_endpoint()`](PairedUrlStructure/has_endpoint.md) - Determine a given URL is for a paired AMP request.
* [`PairedUrlStructure::remove_endpoint()`](PairedUrlStructure/remove_endpoint.md) - Remove the paired AMP endpoint from a given URL.
