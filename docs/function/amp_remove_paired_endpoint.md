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

:link: [includes/amp-helper-functions.php:1871](/includes/amp-helper-functions.php#L1871-L1873)

<details>
<summary>Show Code</summary>

```php
function amp_remove_paired_endpoint( $url ) {
	return Services::get( 'paired_routing' )->remove_endpoint( $url );
}
```

</details>
