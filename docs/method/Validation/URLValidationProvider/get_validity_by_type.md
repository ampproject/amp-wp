## Method `URLValidationProvider::get_validity_by_type()`

```php
public function get_validity_by_type();
```

Provides the validity counts by type.

### Return value

`array[]`

### Source

:link: [src/Validation/URLValidationProvider.php:161](/src/Validation/URLValidationProvider.php#L161-L163)

<details>
<summary>Show Code</summary>

```php
public function get_validity_by_type() {
	return $this->validity_by_type;
}
```

</details>
