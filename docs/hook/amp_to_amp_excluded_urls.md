## Filter `amp_to_amp_excluded_urls`

```php
apply_filters( 'amp_to_amp_excluded_urls', $excluded_urls );
```

Filters the list of URLs which are excluded from being included in AMP-to-AMP linking.

This only applies when the amp_to_amp_linking_enabled filter returns true, which it does by default in Transitional mode. This filter can be used to opt-in when in Reader mode. This does not apply in Standard mode. Only frontend URLs on the frontend need be excluded, as all other URLs are never made into AMP links.

### Arguments

* `string[] $excluded_urls` - The URLs to exclude from having AMP-to-AMP links.

### Source

:link: [includes/amp-helper-functions.php:1538](/includes/amp-helper-functions.php#L1538)

<details>
<summary>Show Code</summary>

```php
$excluded_urls = apply_filters( 'amp_to_amp_excluded_urls', [] );
```

</details>
