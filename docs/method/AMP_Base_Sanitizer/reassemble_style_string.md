## Method `AMP_Base_Sanitizer::reassemble_style_string()`

```php
protected function reassemble_style_string( $styles );
```

Reassemble a style string that can be used in a &#039;style&#039; attribute.

### Arguments

* `array $styles` - Associative array of styles to reassemble into a string.

### Source

[includes/sanitizers/class-amp-base-sanitizer.php:763](https://github.com/ampproject/amp-wp/blob/develop/includes/sanitizers/class-amp-base-sanitizer.php#L763-L782)

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
