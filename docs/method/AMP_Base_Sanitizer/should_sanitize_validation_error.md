## Method `AMP_Base_Sanitizer::should_sanitize_validation_error()`

```php
public function should_sanitize_validation_error( $validation_error, $data = array() );
```

Check whether or not sanitization should occur in response to validation error.

### Arguments

* `array $validation_error` - Validation error.
* `array $data` - Data including the node.

### Return value

`bool` - Whether to sanitize.

### Source

:link: [includes/sanitizers/class-amp-base-sanitizer.php:555](/includes/sanitizers/class-amp-base-sanitizer.php#L555-L561)

<details>
<summary>Show Code</summary>

```php
public function should_sanitize_validation_error( $validation_error, $data = [] ) {
	if ( empty( $this->args['validation_error_callback'] ) || ! is_callable( $this->args['validation_error_callback'] ) ) {
		return true;
	}
	$validation_error = $this->prepare_validation_error( $validation_error, $data );
	return false !== call_user_func( $this->args['validation_error_callback'], $validation_error, $data );
}
```

</details>
