## Method `URLValidationProvider::get_number_validated()`

```php
public function get_number_validated();
```

Provides the number of URLs that have been checked.

### Return value

`int`

### Source

:link: [src/Validation/URLValidationProvider.php:152](/src/Validation/URLValidationProvider.php#L152-L154)

<details>
<summary>Show Code</summary>

```php
public function get_number_validated() {
	return $this->number_validated;
}
```

</details>
