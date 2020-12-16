## Method `URLValidationProvider::get_unaccepted_errors()`

```php
public function get_unaccepted_errors();
```

Provides the total number of unaccepted errors.

### Return value

`int`

### Source

:link: [src/Validation/URLValidationProvider.php:143](/src/Validation/URLValidationProvider.php#L143-L145)

<details>
<summary>Show Code</summary>

```php
public function get_unaccepted_errors() {
	return $this->unaccepted_errors;
}
```

</details>
