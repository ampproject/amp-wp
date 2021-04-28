## Function `amp_add_paired_endpoint`

```php
function amp_add_paired_endpoint( $url );
```

Turn a given URL into a paired AMP URL.

### Arguments

* `string $url` - URL.

### Return value

`string` - AMP URL.

### Source

:link: [includes/amp-helper-functions.php:1847](/includes/amp-helper-functions.php#L1847-L1849)

<details>
<summary>Show Code</summary>

```php
function amp_add_paired_endpoint( $url ) {
	return Services::get( 'paired_routing' )->add_endpoint( $url );
}
```

</details>
