## Method `AMP_Base_Sanitizer::parse_style_string()`

```php
protected function parse_style_string( $style_string );
```

Parse a style string into an associative array of style attributes.

### Arguments

* `string $style_string` - Style string to parse.

### Return value

`string[]` - Associative array of style attributes.

### Source

:link: [includes/sanitizers/class-amp-base-sanitizer.php:749](/includes/sanitizers/class-amp-base-sanitizer.php#L749-L763)

<details>
<summary>Show Code</summary>

```php
protected function parse_style_string( $style_string ) {
	// We need to turn the style string into an associative array of styles first.
	$style_string = trim( $style_string, " \t\n\r\0\x0B;" );
	$elements     = preg_split( '/(\s*:\s*|\s*;\s*)/', $style_string );
	if ( 0 !== count( $elements ) % 2 ) {
		// Style string was malformed, try to process as good as possible by stripping the last element.
		array_pop( $elements );
	}
	$chunks = array_chunk( $elements, 2 );
	// phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.array_columnFound -- WP Core provides a polyfill.
	return array_combine( array_column( $chunks, 0 ), array_column( $chunks, 1 ) );
}
```

</details>
