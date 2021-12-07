## Method `AMP_Base_Sanitizer::get_arg()`

```php
public function get_arg( $key );
```

Get arg.

### Arguments

* `string $key` - Arg key.

### Return value

`mixed` - Args.

### Source

:link: [includes/sanitizers/class-amp-base-sanitizer.php:180](/includes/sanitizers/class-amp-base-sanitizer.php#L180-L185)

<details>
<summary>Show Code</summary>

```php
public function get_arg( $key ) {
	if ( array_key_exists( $key, $this->args ) ) {
		return $this->args[ $key ];
	}
	return null;
}
```

</details>
