## Method `AMP_Base_Sanitizer::sanitize_dimension()`

```php
public function sanitize_dimension( $value, $dimension );
```

Sanitizes a CSS dimension specifier while being sensitive to dimension context.

### Arguments

* `string $value` - A valid CSS dimension specifier; e.g. 50, 50px, 50%. Can be &#039;auto&#039; for width.
* `string $dimension` - Dimension, either &#039;width&#039; or &#039;height&#039;.

### Return value

`float|int|string` - Returns a numeric dimension value, &#039;auto&#039;, or an empty string.

### Source

:link: [includes/sanitizers/class-amp-base-sanitizer.php:226](/includes/sanitizers/class-amp-base-sanitizer.php#L226-L253)

<details>
<summary>Show Code</summary>

```php
public function sanitize_dimension( $value, $dimension ) {
	// Allows 0 to be used as valid dimension.
	if ( null === $value ) {
		return '';
	}
	// Allow special 'auto' value for fixed-height layout.
	if ( 'width' === $dimension && 'auto' === $value ) {
		return $value;
	}
	// Accepts both integers and floats & prevents negative values.
	if ( is_numeric( $value ) ) {
		return max( 0, (float) $value );
	}
	if ( AMP_String_Utils::endswith( $value, 'px' ) ) {
		return absint( $value );
	}
	if ( AMP_String_Utils::endswith( $value, '%' ) && 'width' === $dimension && isset( $this->args['content_max_width'] ) ) {
		$percentage = absint( $value ) / 100;
		return round( $percentage * $this->args['content_max_width'] );
	}
	return '';
}
```

</details>
