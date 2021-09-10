## Method `AMP_Base_Sanitizer::get_data_amp_attributes()`

```php
public function get_data_amp_attributes( $node );
```

Get data-amp-* values from the parent node &#039;figure&#039; added by editor block.

### Arguments

* `\DOMElement $node` - Base node.

### Return value

`array` - AMP data array.

### Source

:link: [includes/sanitizers/class-amp-base-sanitizer.php:703](/includes/sanitizers/class-amp-base-sanitizer.php#L703-L719)

<details>
<summary>Show Code</summary>

```php
public function get_data_amp_attributes( $node ) {
	$attributes = [];
	// Editor blocks add 'figure' as the parent node for images. If this node has data-amp-layout then we should add this as the layout attribute.
	$parent_node = $node->parentNode;
	if ( $parent_node instanceof DOMELement && 'figure' === $parent_node->tagName ) {
		$parent_attributes = AMP_DOM_Utils::get_node_attributes_as_assoc_array( $parent_node );
		if ( isset( $parent_attributes['data-amp-layout'] ) ) {
			$attributes['layout'] = $parent_attributes['data-amp-layout'];
		}
		if ( isset( $parent_attributes['data-amp-noloading'] ) && true === filter_var( $parent_attributes['data-amp-noloading'], FILTER_VALIDATE_BOOLEAN ) ) {
			$attributes['noloading'] = $parent_attributes['data-amp-noloading'];
		}
	}
	return $attributes;
}
```

</details>
