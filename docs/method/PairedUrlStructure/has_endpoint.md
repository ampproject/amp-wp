## Method `PairedUrlStructure::has_endpoint()`

```php
public function has_endpoint( $url );
```

Determine a given URL is for a paired AMP request.

### Arguments

* `string $url` - URL (or REQUEST_URI).

### Return value

`bool` - True if the URL has the paired endpoint.

### Source

:link: [src/PairedUrlStructure.php:40](/src/PairedUrlStructure.php#L40-L42)

<details>
<summary>Show Code</summary>

```php
public function has_endpoint( $url ) {
	return $url !== $this->remove_endpoint( $url );
}
```

</details>
