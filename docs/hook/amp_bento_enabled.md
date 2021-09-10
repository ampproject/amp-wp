## Filter `amp_bento_enabled`

```php
apply_filters( 'amp_bento_enabled', $enabled );
```

Filters whether the use of Bento components is enabled.

When Bento is enabled, newer experimental versions of AMP components are used which incorporate the next generation of the component framework.

### Arguments

* `bool $enabled` - Enabled.

### Source

:link: [includes/amp-helper-functions.php:899](/includes/amp-helper-functions.php#L899)

<details>
<summary>Show Code</summary>

```php
return apply_filters( 'amp_bento_enabled', false );
```

</details>
