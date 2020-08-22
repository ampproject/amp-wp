## Class `AMP_Style_Sanitizer`

Class AMP_Style_Sanitizer

Collects inline styles and outputs them in the amp-custom stylesheet.

### Methods
* `get_css_parser_validation_error_codes`

	<details>

	```php
	static public get_css_parser_validation_error_codes()
	```

	Get error codes that can be raised during parsing of CSS.

This is used to determine which validation errors should be taken into account when determining which validation errors should vary the parse cache.


	</details>
* `has_required_php_css_parser`

	<details>

	```php
	static public has_required_php_css_parser()
	```

	Determine whether the version of PHP-CSS-Parser loaded has all required features for tree shaking and CSS processing.


	</details>
* `__construct`

	<details>

	```php
	public __construct( $dom, array $args = array() )
	```

	AMP_Base_Sanitizer constructor.


	</details>
* `get_styles`

	<details>

	```php
	public get_styles()
	```

	Get list of CSS styles in HTML content of Dom\Document ($this-&gt;dom).


	</details>
* `get_stylesheets`

	<details>

	```php
	public get_stylesheets()
	```

	Get stylesheets for amp-custom.


	</details>
* `get_used_class_names`

	<details>

	```php
	private get_used_class_names()
	```

	Get list of all the class names used in the document, including those used in [class] attributes.


	</details>
* `has_used_class_name`

	<details>

	```php
	private has_used_class_name( $class_names )
	```

	Determine if all the supplied class names are used.


	</details>
* `get_used_tag_names`

	<details>

	```php
	private get_used_tag_names()
	```

	Get list of all the tag names used in the document.


	</details>
* `has_used_tag_names`

	<details>

	```php
	private has_used_tag_names( $tag_names )
	```

	Determine if all the supplied tag names are used.


	</details>
* `has_used_attributes`

	<details>

	```php
	private has_used_attributes( $attribute_names )
	```

	Check whether the attributes exist.


	</details>
* `is_class_allowed_in_amp_date_picker`

	<details>

	```php
	private is_class_allowed_in_amp_date_picker( $class )
	```

	Whether a given class is allowed to be styled in &lt;amp-date-picker&gt;.

That component has child classes that won&#039;t be present in the document yet. So get whether a class is an allowed child.


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
	public sanitize()
	```

	Sanitize CSS styles within the HTML contained in this instance&#039;s Dom\Document.


	</details>
* `get_stylesheet_priority`

	<details>

	```php
	private get_stylesheet_priority( \DOMNode $node )
	```

	Get the priority of the stylesheet associated with the given element.

As with hooks, lower priorities mean they should be included first. The higher the priority value, the more likely it will be that the stylesheet will be among those excluded due to STYLESHEET_TOO_LONG when concatenated CSS reaches 75KB.


	</details>
* `unrelativize_path`

	<details>

	```php
	private unrelativize_path( $path )
	```

	Eliminate relative segments (.

./ and ./) from a path.


	</details>
* `reconstruct_url`

	<details>

	```php
	private reconstruct_url( $parsed_url )
	```

	Construct a URL from a parsed one.


	</details>
* `get_validated_url_file_path`

	<details>

	```php
	public get_validated_url_file_path( $url, $allowed_extensions = array() )
	```

	Generate a URL&#039;s fully-qualified file path.


	</details>
* `set_current_node`

	<details>

	```php
	private set_current_node( $node )
	```

	Set the current node (and its sources when required).


	</details>
* `process_style_element`

	<details>

	```php
	private process_style_element( \DOMElement $element )
	```

	Process style element.


	</details>
* `process_link_element`

	<details>

	```php
	private process_link_element( \DOMElement $element )
	```

	Process link element.


	</details>
* `get_stylesheet_from_url`

	<details>

	```php
	private get_stylesheet_from_url( $stylesheet_url )
	```

	Get stylesheet from URL.


	</details>
* `fetch_external_stylesheet`

	<details>

	```php
	private fetch_external_stylesheet( $url )
	```

	Fetch external stylesheet.


	</details>
* `get_parsed_stylesheet`

	<details>

	```php
	private get_parsed_stylesheet( $stylesheet, $options = array() )
	```

	Get parsed stylesheet (from cache).

If the sanitization status has changed for the validation errors in the cached stylesheet since it was cached, then the cache is invalidated, as the parsed stylesheet needs to be re-constructed.


	</details>
* `should_use_transient_caching`

	<details>

	```php
	private should_use_transient_caching()
	```

	Check whether transient caching for stylesheets should be used.


	</details>
* `splice_imported_stylesheet`

	<details>

	```php
	private splice_imported_stylesheet( Import $item, CSSList $css_list, $options )
	```

	Parse imported stylesheet and replace the `@import` rule with the imported rules in the provided CSS list (in place).


	</details>
* `create_validated_css_document`

	<details>

	```php
	private create_validated_css_document( $stylesheet_string, $options )
	```

	Create validated CSS document.


	</details>
* `parse_stylesheet`

	<details>

	```php
	private parse_stylesheet( $stylesheet_string, $options = array() )
	```

	Parse stylesheet.

Sanitizes invalid CSS properties and rules, compresses the CSS to remove whitespace and comments, and parses declaration blocks to allow selectors to later be evaluated for whether they apply to the current document during tree-shaking.


	</details>
* `should_sanitize_validation_error`

	<details>

	```php
	public should_sanitize_validation_error( $validation_error, $data = array() )
	```

	Check whether or not sanitization should occur in response to validation error.

Supply sources to the error and the current node to data.


	</details>
* `remove_spaces_from_url_values`

	<details>

	```php
	private remove_spaces_from_url_values( $css )
	```

	Remove spaces from CSS URL values which PHP-CSS-Parser doesn&#039;t handle.


	</details>
* `process_css_list`

	<details>

	```php
	private process_css_list( CSSList $css_list, $options )
	```

	Process CSS list.


	</details>
* `real_path_urls`

	<details>

	```php
	private real_path_urls( $urls, $stylesheet_url )
	```

	Convert URLs in to non-relative real-paths.


	</details>
* `process_css_declaration_block`

	<details>

	```php
	private process_css_declaration_block( RuleSet $ruleset, CSSList $css_list, $options )
	```

	Process CSS rule set.


	</details>
* `process_font_face_at_rule`

	<details>

	```php
	private process_font_face_at_rule( AtRuleSet $ruleset, $options )
	```

	Process @font-face by making src URLs non-relative and converting data: URLs into file URLs (with educated guessing).


	</details>
* `process_css_keyframes`

	<details>

	```php
	private process_css_keyframes( KeyFrame $css_list, $options )
	```

	Process CSS keyframes.


	</details>
* `transform_important_qualifiers`

	<details>

	```php
	private transform_important_qualifiers( RuleSet $ruleset, CSSList $css_list, $options )
	```

	Replace !important qualifiers with more specific rules.


	</details>
* `collect_inline_styles`

	<details>

	```php
	private collect_inline_styles( \DOMElement $element )
	```

	Collect and store all CSS style attributes.

Collects the CSS styles from within the HTML contained in this instance&#039;s Dom\Document.


	</details>
* `finalize_styles`

	<details>

	```php
	private finalize_styles()
	```

	Finalize stylesheets for style[amp-custom] and style[amp-keyframes] elements.

Concatenate all pending stylesheets, remove unused rules, and add to AMP style elements in document. Combine all amp-keyframe styles and add them to the end of the body.


	</details>
* `remove_admin_bar_if_css_excluded`

	<details>

	```php
	private remove_admin_bar_if_css_excluded()
	```

	Remove admin bar if its CSS was excluded.


	</details>
* `get_validate_response_data`

	<details>

	```php
	public get_validate_response_data()
	```

	Get data to amend to the validate response.


	</details>
* `add_css_budget_to_admin_bar`

	<details>

	```php
	public add_css_budget_to_admin_bar()
	```

	Update admin bar.


	</details>
* `ampify_ruleset_selectors`

	<details>

	```php
	private ampify_ruleset_selectors( $ruleset )
	```

	Convert CSS selectors and remove obsolete selector hacks for IE.


	</details>
* `get_class_name_selector_pattern`

	<details>

	```php
	static private get_class_name_selector_pattern( $class_names )
	```

	Given a list of class names, create a regular expression pattern to match them in a selector.


	</details>
* `finalize_stylesheet_group`

	<details>

	```php
	private finalize_stylesheet_group( $group, $group_config )
	```

	Finalize a stylesheet group (amp-custom or amp-keyframes).


	</details>
* `create_meta_viewport`

	<details>

	```php
	private create_meta_viewport( \DOMElement $element, $viewport_rules )
	```

	Creates and inserts a meta[name=&quot;viewport&quot;] tag if there are @viewport style rules.

These rules aren&#039;t valid in CSS, but they might be valid in that meta tag. So this adds them to the content attribute of a new meta tag. These are later processed, to merge the content values into a single meta tag.


	</details>
