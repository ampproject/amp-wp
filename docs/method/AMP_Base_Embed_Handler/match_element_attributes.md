## Method `AMP_Base_Embed_Handler::match_element_attributes()`

```php
protected function match_element_attributes( $html, $tag_name, $attribute_names );
```

Get regex pattern for matching HTML attributes from a given tag name.

### Arguments

* `string $html` - HTML source haystack.
* `string $tag_name` - Tag name.
* `string[] $attribute_names` - Attribute names.

### Source

:link: [includes/embeds/class-amp-base-embed-handler.php:93](https://github.com/ampproject/amp-wp/blob/develop/includes/embeds/class-amp-base-embed-handler.php#L93-L111)

<details>
<summary>Show Code</summary>

```php
protected function match_element_attributes( $html, $tag_name, $attribute_names ) {
	$pattern = sprintf(
		'/<%s%s/',
		preg_quote( $tag_name, '/' ),
		implode(
			'',
			array_map(
				function ( $attr_name ) {
					return sprintf( '(?=[^>]*?%1$s="(?P<%1$s>[^"]+)")?', preg_quote( $attr_name, '/' ) );
				},
				$attribute_names
			)
		)
	);
	if ( ! preg_match( $pattern, $html, $matches ) ) {
		return null;
	}
	return wp_array_slice_assoc( $matches, $attribute_names );
}
```

</details>
