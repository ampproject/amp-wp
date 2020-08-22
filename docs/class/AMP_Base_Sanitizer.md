## Class `AMP_Base_Sanitizer`

Class AMP_Base_Sanitizer

### Methods

* [`__construct`](../method/AMP_Base_Sanitizer/__construct.md) - AMP_Base_Sanitizer constructor.
* [`add_buffering_hooks`](../method/AMP_Base_Sanitizer/add_buffering_hooks.md) - Add filters to manipulate output during output buffering before the DOM is constructed.
* [`get_selector_conversion_mapping`](../method/AMP_Base_Sanitizer/get_selector_conversion_mapping.md) - Get mapping of HTML selectors to the AMP component selectors which they may be converted into.
* [`init`](../method/AMP_Base_Sanitizer/init.md) - Run logic before any sanitizers are run.
* [`sanitize`](../method/AMP_Base_Sanitizer/sanitize.md) - Sanitize the HTML contained in the DOMDocument received by the constructor
* [`get_scripts`](../method/AMP_Base_Sanitizer/get_scripts.md) - Return array of values that would be valid as an HTML `script` element.
* [`get_styles`](../method/AMP_Base_Sanitizer/get_styles.md) - Return array of values that would be valid as an HTML `style` attribute.
* [`get_stylesheets`](../method/AMP_Base_Sanitizer/get_stylesheets.md) - Get stylesheets.
* [`get_body_node`](../method/AMP_Base_Sanitizer/get_body_node.md) - Get HTML body as DOMElement from Dom\Document received by the constructor.
* [`sanitize_dimension`](../method/AMP_Base_Sanitizer/sanitize_dimension.md) - Sanitizes a CSS dimension specifier while being sensitive to dimension context.
* [`is_empty_attribute_value`](../method/AMP_Base_Sanitizer/is_empty_attribute_value.md) - Determine if an attribute value is empty.
* [`set_layout`](../method/AMP_Base_Sanitizer/set_layout.md) - Sets the layout, and possibly the &#039;height&#039; and &#039;width&#039; attributes.
* [`add_or_append_attribute`](../method/AMP_Base_Sanitizer/add_or_append_attribute.md) - Adds or appends key and value to list of attributes
* [`maybe_enforce_https_src`](../method/AMP_Base_Sanitizer/maybe_enforce_https_src.md) - Decide if we should remove a src attribute if https is required.
* [`is_document_in_dev_mode`](../method/AMP_Base_Sanitizer/is_document_in_dev_mode.md) - Check whether the document of a given node is in dev mode.
* [`has_dev_mode_exemption`](../method/AMP_Base_Sanitizer/has_dev_mode_exemption.md) - Check whether a node is exempt from validation during dev mode.
* [`is_exempt_from_validation`](../method/AMP_Base_Sanitizer/is_exempt_from_validation.md) - Check whether a certain node should be exempt from validation.
* [`remove_invalid_child`](../method/AMP_Base_Sanitizer/remove_invalid_child.md) - Removes an invalid child of a node.
* [`remove_invalid_attribute`](../method/AMP_Base_Sanitizer/remove_invalid_attribute.md) - Removes an invalid attribute of a node.
* [`should_sanitize_validation_error`](../method/AMP_Base_Sanitizer/should_sanitize_validation_error.md) - Check whether or not sanitization should occur in response to validation error.
* [`prepare_validation_error`](../method/AMP_Base_Sanitizer/prepare_validation_error.md) - Prepare validation error.
* [`clean_up_after_attribute_removal`](../method/AMP_Base_Sanitizer/clean_up_after_attribute_removal.md) - Cleans up artifacts after the removal of an attribute node.
* [`get_data_amp_attributes`](../method/AMP_Base_Sanitizer/get_data_amp_attributes.md) - Get data-amp-* values from the parent node &#039;figure&#039; added by editor block.
* [`filter_data_amp_attributes`](../method/AMP_Base_Sanitizer/filter_data_amp_attributes.md) - Set AMP attributes.
* [`filter_attachment_layout_attributes`](../method/AMP_Base_Sanitizer/filter_attachment_layout_attributes.md) - Set attributes to node&#039;s parent element according to layout.
* [`parse_style_string`](../method/AMP_Base_Sanitizer/parse_style_string.md) - Parse a style string into an associative array of style attributes.
* [`reassemble_style_string`](../method/AMP_Base_Sanitizer/reassemble_style_string.md) - Reassemble a style string that can be used in a &#039;style&#039; attribute.
* [`get_validate_response_data`](../method/AMP_Base_Sanitizer/get_validate_response_data.md) - Get data that is returned in validate responses.
