## Method `AMP_Base_Sanitizer::get_styles()`

> :warning: This function is deprecated: As of 1.0, use get_stylesheets().

```php
public function get_styles();
```

Return array of values that would be valid as an HTML `style` attribute.

### Return value

`array[][]` - Mapping of CSS selectors to arrays of properties.

### Source

:link: [includes/sanitizers/class-amp-base-sanitizer.php:184](../../includes/sanitizers/class-amp-base-sanitizer.php#L184-L186)

<details>
<summary>Show Code</summary>

```php
public function get_styles() {
	return [];
}
```

</details>
