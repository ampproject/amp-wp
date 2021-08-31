## Method `AMP_DOM_Utils::add_attributes_to_node()`

```php
static public function add_attributes_to_node( $node, $attributes );
```

Add one or more HTML element attributes to a node&#039;s DOMElement.

### Arguments

* `\DOMElement $node` - Represents an HTML element.
* `string[] $attributes` - One or more attributes for the node&#039;s HTML element.

### Source

:link: [includes/utils/class-amp-dom-utils.php:201](/includes/utils/class-amp-dom-utils.php#L201-L214)

<details>
<summary>Show Code</summary>

```php
public static function add_attributes_to_node( $node, $attributes ) {
	foreach ( $attributes as $name => $value ) {
		try {
			$node->setAttribute( $name, $value );
		} catch ( DOMException $e ) {
			/*
			 * Catch a "Invalid Character Error" when libxml is able to parse attributes with invalid characters,
			 * but it throws error when attempting to set them via DOM methods. For example, '...this' can be parsed
			 * as an attribute but it will throw an exception when attempting to setAttribute().
			 */
			continue;
		}
	}
}
```

</details>
