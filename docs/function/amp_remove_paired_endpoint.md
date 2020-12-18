## Function `amp_remove_paired_endpoint`

```php
function amp_remove_paired_endpoint( $url );
```

Remove the paired AMP endpoint from a given URL.

### Arguments

* `string $url` - URL.

### Return value

`string` - URL with AMP stripped.

### Source

:link: [includes/amp-helper-functions.php:2027](/includes/amp-helper-functions.php#L2027-L2044)

<details>
<summary>Show Code</summary>

```php
function amp_remove_paired_endpoint( $url ) {
	$slug = amp_get_slug();

	// Strip endpoint, including /amp/, /amp/amp/, /amp/foo/.
	$url = preg_replace(
		sprintf(
			':(/%s(/[^/?#]+)?)+(?=/?(\?|#|$)):',
			preg_quote( $slug, ':' )
		),
		'',
		$url
	);

	// Strip query var, including ?amp, ?amp=1, etc.
	$url = remove_query_arg( $slug, $url );

	return $url;
}
```

</details>
