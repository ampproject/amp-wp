## Method `AMP_Base_Sanitizer::reassemble_style_string()`

```php
protected function reassemble_style_string( $styles );
```

Reassemble a style string that can be used in a &#039;style&#039; attribute.

### Arguments

* `array $styles` - Associative array of styles to reassemble into a string.

### Return value

`string` - Reassembled style string.

### Source

:link: [includes/sanitizers/class-amp-base-sanitizer.php:791](/includes/sanitizers/class-amp-base-sanitizer.php#L791-L810)

<details>
<summary>Show Code</summary>

```php
protected function reassemble_style_string( $styles ) {
	if ( ! is_array( $styles ) ) {
		return '';
	}
	// Discard empty values first.
	$styles = array_filter( $styles );
	return array_reduce(
		array_keys( $styles ),
		static function ( $style_string, $style_name ) use ( $styles ) {
			if ( ! empty( $style_string ) ) {
				$style_string .= ';';
			}
			return $style_string . "{$style_name}:{$styles[ $style_name ]}";
		},
		''
	);
}
```

</details>
