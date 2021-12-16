## Method `PairedUrl::add_query_var()`

```php
public function add_query_var( $url, $value = '1' );
```

Get paired AMP URL using query var (`?amp=1`).

### Arguments

* `string $url` - URL (or REQUEST_URI).
* `string $value` - Value. Defaults to 1.

### Return value

`string` - AMP URL.

### Source

:link: [src/PairedUrl.php:90](/src/PairedUrl.php#L90-L92)

<details>
<summary>Show Code</summary>

```php
public function add_query_var( $url, $value = '1' ) {
	return add_query_arg( amp_get_slug(), $value, $url );
}
```

</details>
