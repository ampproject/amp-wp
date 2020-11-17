## Method `AMP_Base_Sanitizer::add_or_append_attribute()`

```php
public function add_or_append_attribute( $attributes, $key, $value, $separator = ' ' );
```

Adds or appends key and value to list of attributes

Adds key and value to list of attributes, or if the key already exists in the array it concatenates to existing attribute separator by a space or other supplied separator.

### Arguments

* `string[] $attributes` - {      Attributes.      @type int $height      @type int $width      @type string $sizes      @type string $class      @type string $layout }
* `string $key` - Valid associative array index to add.
* `string $value` - Value to add or append to array indexed at the key.
* `string $separator` - Optional; defaults to space but some other separator if needed.

### Source

:link: [includes/sanitizers/class-amp-base-sanitizer.php:379](/includes/sanitizers/class-amp-base-sanitizer.php#L379-L385)

<details>
<summary>Show Code</summary>

```php
public function add_or_append_attribute( &$attributes, $key, $value, $separator = ' ' ) {
	if ( isset( $attributes[ $key ] ) ) {
		$attributes[ $key ] = trim( $attributes[ $key ] . $separator . $value );
	} else {
		$attributes[ $key ] = $value;
	}
}
```

</details>
