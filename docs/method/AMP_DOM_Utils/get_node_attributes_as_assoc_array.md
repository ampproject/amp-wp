## Method `AMP_DOM_Utils::get_node_attributes_as_assoc_array()`

```php
static public function get_node_attributes_as_assoc_array( $node );
```

Extract a DOMElement node&#039;s HTML element attributes and return as an array.

### Arguments

* `\DOMElement $node` - Represents an HTML element for which to extract attributes.

### Return value

`string[]` - The attributes for the passed node, or an                  empty array if it has no attributes.

### Source

:link: [includes/utils/class-amp-dom-utils.php:241](/includes/utils/class-amp-dom-utils.php#L241-L252)

<details>
<summary>Show Code</summary>

```php
public static function get_node_attributes_as_assoc_array( $node ) {
	$attributes = [];
	if ( ! $node->hasAttributes() ) {
		return $attributes;
	}
	foreach ( $node->attributes as $attribute ) {
		$attributes[ $attribute->nodeName ] = $attribute->nodeValue;
	}
	return $attributes;
}
```

</details>
