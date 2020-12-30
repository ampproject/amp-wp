## Methods

* [`AMP_Base_Embed_Handler::__construct()`](AMP_Base_Embed_Handler/__construct.md) - Constructor.
* [`AMP_Base_Embed_Handler::get_child_elements()`](AMP_Base_Embed_Handler/get_child_elements.md) - Get all child elements of the specified element.
* [`AMP_Base_Embed_Handler::get_scripts()`](AMP_Base_Embed_Handler/get_scripts.md) - Get mapping of AMP component names to AMP script URLs.
* [`AMP_Base_Embed_Handler::match_element_attributes()`](AMP_Base_Embed_Handler/match_element_attributes.md) - Get regex pattern for matching HTML attributes from a given tag name.
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
