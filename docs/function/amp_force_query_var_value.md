## Function `amp_force_query_var_value`

```php
function amp_force_query_var_value( $query_vars );
```

Make sure the `amp` query var has an explicit value.

This avoids issues when filtering the deprecated `query_string` hook.

### Arguments

* `array $query_vars` - Query vars.

### Return value

`array` - Query vars.

### Source

:link: [includes/amp-helper-functions.php:270](../../includes/amp-helper-functions.php#L270-L275)

<details>
<summary>Show Code</summary>

```php
function amp_force_query_var_value( $query_vars ) {
	if ( isset( $query_vars[ amp_get_slug() ] ) && '' === $query_vars[ amp_get_slug() ] ) {
		$query_vars[ amp_get_slug() ] = 1;
	}
	return $query_vars;
}
```

</details>
