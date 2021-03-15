## Method `PairedUrl::remove_query_var()`

```php
public function remove_query_var( $url );
```

Strip paired query var.

### Arguments

* `string $url` - URL (or REQUEST_URI).

### Return value

`string` - URL.

### Source

:link: [src/PairedUrl.php:26](/src/PairedUrl.php#L26-L28)

<details>
<summary>Show Code</summary>

```php
public function remove_query_var( $url ) {
	return remove_query_arg( amp_get_slug(), $url );
}
```

</details>
