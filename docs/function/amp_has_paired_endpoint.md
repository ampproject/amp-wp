## Function `amp_has_paired_endpoint`

```php
function amp_has_paired_endpoint( $url = '' );
```

Determine a given URL is for a paired AMP request.

### Arguments

* `string $url` - URL to examine. If empty, will use the current URL.

### Return value

`bool` - True if the AMP query parameter is set with the required value, false if not.

### Source

:link: [includes/amp-helper-functions.php:1860](/includes/amp-helper-functions.php#L1860-L1862)

<details>
<summary>Show Code</summary>

```php
function amp_has_paired_endpoint( $url = '' ) {
	return Services::get( 'paired_routing' )->has_endpoint( $url );
}
```

</details>
