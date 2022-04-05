## Filter `amp_auto_lightbox_disabled`

```php
apply_filters( 'amp_auto_lightbox_disabled', $disabled );
```

Filters whether AMP auto-lightbox is disabled.

When disabled, the data-amp-auto-lightbox-disable attribute is added to the body.

### Arguments

* `bool $disabled` - Whether disabled.

### Source

:link: [includes/amp-helper-functions.php:1622](/includes/amp-helper-functions.php#L1622)

<details>
<summary>Show Code</summary>

```php
$is_auto_lightbox_disabled = apply_filters( 'amp_auto_lightbox_disabled', true );
```

</details>
