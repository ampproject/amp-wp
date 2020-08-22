## Class `AMP_Tag_And_Attribute_Sanitizer`

Strips the tags and attributes from the content that are not allowed by the AMP spec.

Allowed tags array is generated from this protocol buffer:
     https://github.com/ampproject/amphtml/blob/bd29b0eb1b28d900d4abed2c1883c6980f18db8e/validator/validator-main.protoascii     by the python script in amp-wp/bin/amp_wp_build.py. See the comment at the top     of that file for instructions to generate class-amp-allowed-tags-generated.php.

### Methods
<details>
<summary><code>__construct</code></summary>

```php
public __construct( $dom, $args = array() )
```

AMP_Tag_And_Attribute_Sanitizer constructor.


</details>
<details>
<summary><code>get_scripts</code></summary>

```php
public get_scripts()
```

Return array of values that would be valid as an HTML `script` element.

Array keys are AMP element names and array values are their respective Javascript URLs from https://cdn.ampproject.org


</details>
<details>
<summary><code>process_alternate_names</code></summary>

```php
private process_alternate_names( $attr_spec_list )
```

Process alternative names in attribute spec list.


</details>
<details>
<summary><code>sanitize</code></summary>

```php
public sanitize()
```

Sanitize the elements from the HTML contained in this instance&#039;s Dom\Document.


</details>
<details>
<summary><code>sanitize_element</code></summary>

```php
private sanitize_element( \DOMElement $element )
```

Sanitize element.

Walk the DOM tree with depth first search (DFS) with post order traversal (LRN).


</details>
<details>
<summary><code>get_rule_spec_list_to_validate</code></summary>

```php
private get_rule_spec_list_to_validate( \DOMElement $node, $rule_spec )
```

Augment rule spec for validation.


</details>
<details>
<summary><code>process_node</code></summary>

```php
private process_node( \DOMElement $node )
```

Process a node by checking if an element and its attributes are valid, and removing them when invalid.

Attributes which are not valid are removed. Elements which are not allowed are also removed, including elements which miss mandatory attributes.


</details>
<details>
<summary><code>is_missing_mandatory_attribute</code></summary>

```php
public is_missing_mandatory_attribute( $attr_spec, \DOMElement $node )
```

Whether a node is missing a mandatory attribute.


</details>
<details>
<summary><code>get_missing_mandatory_attributes</code></summary>

```php
private get_missing_mandatory_attributes( $attr_spec, \DOMElement $node )
```

Get list of mandatory missing mandatory attributes.


</details>
<details>
<summary><code>validate_cdata_for_node</code></summary>

```php
private validate_cdata_for_node( \DOMElement $element, $cdata_spec )
```

Validate element for its CDATA.


</details>
<details>
<summary><code>get_json_error_code</code></summary>

```php
private get_json_error_code( $json_last_error )
```

Gets the JSON error code for the last error.


</details>
<details>
<summary><code>validate_tag_spec_for_node</code></summary>

```php
private validate_tag_spec_for_node( \DOMElement $node, $tag_spec )
```

Determines is a node is currently valid per its tag specification.

Checks to see if a node&#039;s placement with the DOM is be valid for the given tag_spec. If there are restrictions placed on the type of node that can be an immediate parent or an ancestor of this node, then make sure those restrictions are met.
 This method has no side effects. It should not sanitize the DOM. It is purely to see if the spec matches.


</details>
<details>
<summary><code>validate_attr_spec_list_for_node</code></summary>

```php
private validate_attr_spec_list_for_node( \DOMElement $node, $attr_spec_list )
```

Checks to see if a spec is potentially valid.

Checks the given node based on the attributes present in the node. This does not check every possible constraint imposed by the validator spec. It only performs the checks that are used to narrow down which set of attribute specs is most aligned with the given node. As of AMPHTML v1910161528000, the frequency of attribute spec constraints looks as follows:
  433: value  400: mandatory  222: value_casei  147: disallowed_value_regex  115: value_regex  101: value_url   77: dispatch_key   17: value_regex_casei   15: requires_extension   12: alternative_names    2: value_properties
 The constraints that should be the most likely to differentiate one tag spec from another are:
 - value - mandatory - value_casei
 For example, there are two &lt;amp-carousel&gt; tag specs, one that has a mandatory lightbox attribute and another that lacks the lightbox attribute altogether. If an &lt;amp-carousel&gt; has the lightbox attribute, then we can rule out the tag spec without the lightbox attribute via the mandatory constraint.
 Additionally, there are multiple &lt;amp-date-picker&gt; tag specs, each which vary by the value of the &#039;type&#039; attribute. By validating the type &#039;value&#039; and &#039;value_casei&#039; constraints here, we can narrow down the tag specs that should then be used to later validate and sanitize the element (in the sanitize_disallowed_attribute_values_in_node method).


</details>
<details>
<summary><code>get_spec_name</code></summary>

```php
private get_spec_name( \DOMElement $element, $tag_spec )
```

Get spec name for a given tag spec.


</details>
<details>
<summary><code>sanitize_disallowed_attribute_values_in_node</code></summary>

```php
private sanitize_disallowed_attribute_values_in_node( \DOMElement $node, $attr_spec_list )
```

Remove invalid AMP attributes values from $node that have been implicitly disallowed.

Allowed values are found $this-&gt;globally_allowed_attributes and in parameter $attr_spec_list


</details>
<details>
<summary><code>is_valid_layout</code></summary>

```php
private is_valid_layout( $tag_spec, $node )
```

Check the validity of the layout attributes for the given element.

This involves checking the layout, width, height and sizes attributes with AMP specific logic.


</details>
<details>
<summary><code>is_inside_mustache_template</code></summary>

```php
private is_inside_mustache_template( \DOMElement $node )
```

Whether the node is inside a mustache template.


</details>
<details>
<summary><code>has_layout_attribute_with_mustache_variable</code></summary>

```php
private has_layout_attribute_with_mustache_variable( \DOMElement $node )
```

Whether the node has a layout attribute with variable syntax, like {{foo}}.

This is important for whether to validate the layout of the node. Similar to the validation logic in the AMP validator.


</details>
<details>
<summary><code>calculate_width</code></summary>

```php
private calculate_width( $amp_layout_spec, $input_layout, CssLength $input_width )
```

Calculate the effective width from the input layout and input width.

This involves considering that some elements, such as amp-audio and amp-pixel, have natural dimensions (browser or implementation-specific defaults for width / height).
 Adapted from the `CalculateWidth` method found in `validator.js` from the `ampproject/amphtml` project on GitHub.


</details>
<details>
<summary><code>calculate_height</code></summary>

```php
private calculate_height( $amp_layout_spec, $input_layout, CssLength $input_height )
```

Calculate the effective height from input layout and input height.

Adapted from the `CalculateHeight` method found in `validator.js` from the `ampproject/amphtml` project on GitHub.


</details>
<details>
<summary><code>calculate_layout</code></summary>

```php
private calculate_layout( $layout_attr, CssLength $width, CssLength $height, $sizes_attr, $heights_attr )
```

Calculate the layout.

This depends on the width / height calculation above. It happens last because web designers often make fixed-sized mocks first and then the layout determines how things will change for different viewports / devices / etc.
 Adapted from the `CalculateLayout` method found in `validator.js` from the `ampproject/amphtml` project on GitHub.


</details>
<details>
<summary><code>check_attr_spec_rule_mandatory</code></summary>

```php
private check_attr_spec_rule_mandatory( \DOMElement $node, $attr_name, $attr_spec_rule )
```

Check if attribute is mandatory determine whether it exists in $node.

When checking for the given attribute it also checks valid alternates.


</details>
<details>
<summary><code>get_element_attribute_intersection</code></summary>

```php
private get_element_attribute_intersection( \DOMElement $element, $attribute_names )
```

Get the intersection of the element attributes with the supplied attributes.


</details>
<details>
<summary><code>check_attr_spec_rule_value</code></summary>

```php
private check_attr_spec_rule_value( \DOMElement $node, $attr_name, $attr_spec_rule )
```

Check if attribute has a value rule determine if its value is valid.

Checks for value validity by matches against valid values.


</details>
<details>
<summary><code>check_matching_attribute_value</code></summary>

```php
private check_matching_attribute_value( $attr_name, $attr_value, $spec_values )
```

Check that an attribute&#039;s value matches is given spec value.

This takes into account boolean attributes where value can match name (e.g. selected=&quot;selected&quot;).


</details>
<details>
<summary><code>check_attr_spec_rule_value_casei</code></summary>

```php
private check_attr_spec_rule_value_casei( \DOMElement $node, $attr_name, $attr_spec_rule )
```

Check if attribute has a value rule determine if its value matches ignoring case.


</details>
<details>
<summary><code>check_attr_spec_rule_value_regex</code></summary>

```php
private check_attr_spec_rule_value_regex( \DOMElement $node, $attr_name, $attr_spec_rule )
```

Check if attribute has a regex value rule determine if it matches.


</details>
<details>
<summary><code>check_attr_spec_rule_value_regex_casei</code></summary>

```php
private check_attr_spec_rule_value_regex_casei( \DOMElement $node, $attr_name, $attr_spec_rule )
```

Check if attribute has a case-insensitive regex value rule determine if it matches.


</details>
<details>
<summary><code>check_attr_spec_rule_valid_url</code></summary>

```php
private check_attr_spec_rule_valid_url( \DOMElement $node, $attr_name, $attr_spec_rule )
```

Check if attribute has a valid host value


</details>
<details>
<summary><code>parse_protocol</code></summary>

```php
private parse_protocol( $url )
```

Parse protocol from URL.

This may not be a valid protocol (scheme), but it will be where the protocol should be in the URL.


</details>
<details>
<summary><code>normalize_url_from_attribute_value</code></summary>

```php
private normalize_url_from_attribute_value( $url )
```

Normalize a URL that appeared as a tag attribute.


</details>
<details>
<summary><code>check_attr_spec_rule_allowed_protocol</code></summary>

```php
private check_attr_spec_rule_allowed_protocol( \DOMElement $node, $attr_name, $attr_spec_rule )
```

Check if attribute has a protocol value rule determine if it matches.


</details>
<details>
<summary><code>extract_attribute_urls</code></summary>

```php
private extract_attribute_urls( \DOMAttr $attribute_node, $spec_attr_name = null )
```

Extract URLs from attribute.


</details>
<details>
<summary><code>check_attr_spec_rule_disallowed_relative</code></summary>

```php
private check_attr_spec_rule_disallowed_relative( \DOMElement $node, $attr_name, $attr_spec_rule )
```

Check if attribute has disallowed relative URL value according to rule spec.


</details>
<details>
<summary><code>check_attr_spec_rule_disallowed_empty</code></summary>

```php
private check_attr_spec_rule_disallowed_empty( \DOMElement $node, $attr_name, $attr_spec_rule )
```

Check if attribute has disallowed empty value rule determine if value is empty.


</details>
<details>
<summary><code>check_attr_spec_rule_disallowed_value_regex</code></summary>

```php
private check_attr_spec_rule_disallowed_value_regex( \DOMElement $node, $attr_name, $attr_spec_rule )
```

Check if attribute has disallowed value via regex match and determine if value matches.


</details>
<details>
<summary><code>parse_properties_attribute</code></summary>

```php
private parse_properties_attribute( $value )
```

Parse properties attribute (e.g. meta viewport).


</details>
<details>
<summary><code>serialize_properties_attribute</code></summary>

```php
private serialize_properties_attribute( $properties )
```

Serialize properties attribute (e.g. meta viewport).


</details>
<details>
<summary><code>check_attr_spec_rule_value_properties</code></summary>

```php
private check_attr_spec_rule_value_properties( \DOMElement $node, $attr_name, $attr_spec_rule )
```

Check if attribute has valid properties.


</details>
<details>
<summary><code>is_amp_allowed_attribute</code></summary>

```php
private is_amp_allowed_attribute( \DOMAttr $attr_node, $attr_spec_list )
```

Determine if the supplied attribute name is allowed for AMP.


</details>
<details>
<summary><code>is_amp_allowed_tag</code></summary>

```php
private is_amp_allowed_tag( \DOMElement $node )
```

Determine if the supplied $node&#039;s HTML tag is allowed for AMP.


</details>
<details>
<summary><code>has_parent</code></summary>

```php
private has_parent( \DOMElement $node, $parent_spec_name )
```

Determine if the supplied $node has a parent with the specified spec name.


</details>
<details>
<summary><code>has_ancestor</code></summary>

```php
private has_ancestor( \DOMElement $node, $ancestor_tag_spec_name )
```

Determine if the supplied $node has an ancestor with the specified tag name.


</details>
<details>
<summary><code>parse_tag_and_attributes_from_spec_name</code></summary>

```php
private parse_tag_and_attributes_from_spec_name( $spec_name )
```

Parse tag name and attributes from spec name.

Given a spec name like &#039;form [method=post]&#039;, extract the tag name &#039;form&#039; and the attributes.


</details>
<details>
<summary><code>remove_disallowed_descendants</code></summary>

```php
private remove_disallowed_descendants( \DOMElement $node, $allowed_descendants, $spec_name )
```

Loop through node&#039;s descendants and remove the ones that are not in the allowlist.


</details>
<details>
<summary><code>check_valid_children</code></summary>

```php
private check_valid_children( \DOMElement $node, $child_tags )
```

Check whether the node validates the constraints for children.


</details>
<details>
<summary><code>get_ancestor_with_matching_spec_name</code></summary>

```php
private get_ancestor_with_matching_spec_name( \DOMElement $node, $ancestor_spec_name )
```

Get the first ancestor node matching the specified tag name for the supplied $node.


</details>
<details>
<summary><code>replace_node_with_children</code></summary>

```php
private replace_node_with_children( \DOMElement $node )
```

Replaces the given node with it&#039;s child nodes, if any

Also adds them to the stack for processing by the sanitize() function.


</details>
<details>
<summary><code>remove_node</code></summary>

```php
private remove_node( \DOMElement $node )
```

Removes a node from its parent node.

If removing the node makes the parent node empty, then it will remove the parent too. It will Continue until a non-empty parent or the &#039;body&#039; element is reached.


</details>
<details>
<summary><code>supports_layout</code></summary>

```php
private supports_layout( $tag_spec, $layout, $fallback = false )
```

Check whether a given tag spec supports a layout.


</details>
