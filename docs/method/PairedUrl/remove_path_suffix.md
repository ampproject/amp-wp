## Method `PairedUrl::remove_path_suffix()`

```php
public function remove_path_suffix( $url );
```

Strip paired endpoint suffix.

### Arguments

* `string $url` - URL (or REQUEST_URI).

### Return value

`string` - URL.

### Source

:link: [src/PairedUrl.php:52](/src/PairedUrl.php#L52-L61)

<details>
<summary>Show Code</summary>

```php
public function remove_path_suffix( $url ) {
	return preg_replace(
		sprintf(
			':/%s(?=/?(\?|#|$)):',
			preg_quote( amp_get_slug(), ':' )
		),
		'',
		$url
	);
}
```

</details>
