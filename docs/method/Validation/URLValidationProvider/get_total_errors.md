## Method `URLValidationProvider::get_total_errors()`

```php
public function get_total_errors();
```

Provides the total number of validation errors found.

### Return value

`int`

### Source

:link: [src/Validation/URLValidationProvider.php:134](/src/Validation/URLValidationProvider.php#L134-L136)

<details>
<summary>Show Code</summary>

```php
public function get_total_errors() {
	return $this->total_errors;
}
```

</details>
