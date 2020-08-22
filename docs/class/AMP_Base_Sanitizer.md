## Class `AMP_Base_Sanitizer`

Class AMP_Base_Sanitizer

### Methods
* `__construct`

	<details>

	```php
	public __construct( $dom, $args = array() )
	```

	AMP_Base_Sanitizer constructor.


	</details>
* `add_buffering_hooks`

	<details>

	```php
	static public add_buffering_hooks( $args = array() )
	```

	Add filters to manipulate output during output buffering before the DOM is constructed.

Add actions and filters before the page is rendered so that the sanitizer can fix issues during output buffering. This provides an alternative to manipulating the DOM in the sanitize method. This is a static function because it is invoked before the class is instantiated, as the DOM is not available yet. This method is only called when &#039;amp&#039; theme support is present. It is conceptually similar to the AMP_Base_Embed_Handler class&#039;s register_embed method.


	</details>
* `get_selector_conversion_mapping`

	<details>

	```php
	public get_selector_conversion_mapping()
	```

	Get mapping of HTML selectors to the AMP component selectors which they may be converted into.


	</details>
* `init`

	<details>

	```php
	public init( $sanitizers )
	```

	Run logic before any sanitizers are run.

After the sanitizers are instantiated but before calling sanitize on each of them, this method is called with list of all the instantiated sanitizers.


	</details>
* `sanitize`

	<details>

	```php
	abstract public sanitize()
	```

	Sanitize the HTML contained in the DOMDocument received by the constructor


	</details>
* `get_scripts`

	<details>

	```php
	public get_scripts()
	```

	Return array of values that would be valid as an HTML `script` element.

Array keys are AMP element names and array values are their respective Javascript URLs from https://cdn.ampproject.org


	</details>
* `get_styles`

	<details>

	```php
	public get_styles()
	```

	Return array of values that would be valid as an HTML `style` attribute.


	</details>
* `get_stylesheets`

	<details>

	```php
	public get_stylesheets()
	```

	Get stylesheets.


	</details>
* `get_body_node`

	<details>

	```php
	protected get_body_node()
	```

	Get HTML body as DOMElement from Dom\Document received by the constructor.


	</details>
* `sanitize_dimension`

	<details>

	```php
	public sanitize_dimension( $value, $dimension )
	```

	Sanitizes a CSS dimension specifier while being sensitive to dimension context.


	</details>
* `is_empty_attribute_value`

	<details>

	```php
	public is_empty_attribute_value( $value )
	```

	Determine if an attribute value is empty.


	</details>
* `set_layout`

	<details>

	```php
	public set_layout( $attributes )
	```

	Sets the layout, and possibly the &#039;height&#039; and &#039;width&#039; attributes.


	</details>
* `add_or_append_attribute`

	<details>

	```php
	public add_or_append_attribute( $attributes, $key, $value, $separator = ' ' )
	```

	Adds or appends key and value to list of attributes

Adds key and value to list of attributes, or if the key already exists in the array it concatenates to existing attribute separator by a space or other supplied separator.


	</details>
* `maybe_enforce_https_src`

	<details>

	```php
	public maybe_enforce_https_src( $src, $force_https = false )
	```

	Decide if we should remove a src attribute if https is required.

If not required, the implementing class may want to try and force https instead.


	</details>
* `is_document_in_dev_mode`

	<details>

	```php
	protected is_document_in_dev_mode()
	```

	Check whether the document of a given node is in dev mode.


	</details>
* `has_dev_mode_exemption`

	<details>

	```php
	protected has_dev_mode_exemption( \DOMNode $node )
	```

	Check whether a node is exempt from validation during dev mode.


	</details>
* `is_exempt_from_validation`

	<details>

	```php
	protected is_exempt_from_validation( \DOMNode $node )
	```

	Check whether a certain node should be exempt from validation.


	</details>
* `remove_invalid_child`

	<details>

	```php
	public remove_invalid_child( $node, $validation_error = array() )
	```

	Removes an invalid child of a node.

Also, calls the mutation callback for it. This tracks all the nodes that were removed.


	</details>
* `remove_invalid_attribute`

	<details>

	```php
	public remove_invalid_attribute( $element, $attribute, $validation_error = array(), $attr_spec = array() )
	```

	Removes an invalid attribute of a node.

Also, calls the mutation callback for it. This tracks all the attributes that were removed.


	</details>
* `should_sanitize_validation_error`

	<details>

	```php
	public should_sanitize_validation_error( $validation_error, $data = array() )
	```

	Check whether or not sanitization should occur in response to validation error.


	</details>
* `prepare_validation_error`

	<details>

	```php
	public prepare_validation_error( array $error = array(), array $data = array() )
	```

	Prepare validation error.


	</details>
* `clean_up_after_attribute_removal`

	<details>

	```php
	protected clean_up_after_attribute_removal( $element, $attribute )
	```

	Cleans up artifacts after the removal of an attribute node.


	</details>
* `get_data_amp_attributes`

	<details>

	```php
	public get_data_amp_attributes( $node )
	```

	Get data-amp-* values from the parent node &#039;figure&#039; added by editor block.


	</details>
* `filter_data_amp_attributes`

	<details>

	```php
	public filter_data_amp_attributes( $attributes, $amp_data )
	```

	Set AMP attributes.


	</details>
* `filter_attachment_layout_attributes`

	<details>

	```php
	public filter_attachment_layout_attributes( $node, $new_attributes, $layout )
	```

	Set attributes to node&#039;s parent element according to layout.


	</details>
* `parse_style_string`

	<details>

	```php
	protected parse_style_string( $style_string )
	```

	Parse a style string into an associative array of style attributes.


	</details>
* `reassemble_style_string`

	<details>

	```php
	protected reassemble_style_string( $styles )
	```

	Reassemble a style string that can be used in a &#039;style&#039; attribute.


	</details>
* `get_validate_response_data`

	<details>

	```php
	public get_validate_response_data()
	```

	Get data that is returned in validate responses.

The array returned is merged with the overall validate response data.


	</details>
