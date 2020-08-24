## Method `AMP_Base_Sanitizer::is_empty_attribute_value()`

```php
public function is_empty_attribute_value( $value );
```

Determine if an attribute value is empty.

### Arguments

* `string|null $value` - Attribute value.

### Source

:link: [includes/sanitizers/class-amp-base-sanitizer.php:260](https://github.com/ampproject/amp-wp/blob/develop/includes/sanitizers/class-amp-base-sanitizer.php#L260-L262)

<details>
<summary>Show Code</summary>

```php
public function is_empty_attribute_value( $value ) {
	return ! isset( $value ) || '' === $value;
}
```

</details>
