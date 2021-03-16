## Filter `amp_site_icon_url`

```php
apply_filters( 'amp_site_icon_url', $schema_img_url );
```

Filters the publisher logo URL in the schema.org data.

Previously, this only filtered the Site Icon, as that was the only possible schema.org publisher logo. But the Custom Logo is now the preferred publisher logo, if it exists and its dimensions aren&#039;t too big.

### Arguments

* `string $schema_img_url` - URL of the publisher logo, either the Custom Logo or the Site Icon.

### Source

:link: [includes/amp-helper-functions.php:1608](/includes/amp-helper-functions.php#L1608)

<details>
<summary>Show Code</summary>

```php
$logo_image_url = apply_filters( 'amp_site_icon_url', $logo_image_url );
```

</details>
