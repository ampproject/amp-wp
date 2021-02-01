## Method `AMP_Base_Embed_Handler::get_child_elements()`

```php
protected function get_child_elements( \DOMElement $node );
```

Get all child elements of the specified element.

### Arguments

* `\DOMElement $node` - Element.

### Return value

`\DOMElement[]` - Array of child elements for specified element.

### Source

:link: [includes/embeds/class-amp-base-embed-handler.php:121](/includes/embeds/class-amp-base-embed-handler.php#L121-L128)

<details>
<summary>Show Code</summary>

```php
protected function get_child_elements( DOMElement $node ) {
	return array_filter(
		iterator_to_array( $node->childNodes ),
		static function ( DOMNode $child ) {
			return $child instanceof DOMElement;
		}
	);
}
```

</details>
