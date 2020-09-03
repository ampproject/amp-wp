## Filter `amp_site_icon_url`


Filters the publisher logo URL in the schema.org data.

Previously, this only filtered the Site Icon, as that was the only possible schema.org publisher logo. But the Custom Logo is now the preferred publisher logo, if it exists and its dimensions aren&#039;t too big.

### Source

:link: [includes/amp-helper-functions.php:1732](../../includes/amp-helper-functions.php#L1732)

<details>
<summary>Show Code</summary>

```php
$logo_image_url = apply_filters( 'amp_site_icon_url', $logo_image_url );
```

</details>
