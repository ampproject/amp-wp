## Method `AMP_Base_Sanitizer::is_empty_attribute_value()`

```php
public function is_empty_attribute_value( $value );
```

Determine if an attribute value is empty.

### Arguments

* `string|null $value` - Attribute value.

### Return value

`bool` - True if empty, false if not.

### Source

:link: [includes/sanitizers/class-amp-base-sanitizer.php:333](/includes/sanitizers/class-amp-base-sanitizer.php#L333-L335)

<details>
<summary>Show Code</summary>

```php
public function is_empty_attribute_value( $value ) {
	return ! isset( $value ) || '' === $value;
}
```

</details>
