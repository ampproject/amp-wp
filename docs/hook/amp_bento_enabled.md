## Filter `amp_bento_enabled`

> :warning: This filter is deprecated: 2.5.0

```php
apply_filters( 'amp_bento_enabled', $enabled );
```

Filters whether the use of Bento components is enabled.

When Bento is enabled, newer experimental versions of AMP components are used which incorporate the next generation of the component framework.

### Arguments

* `bool $enabled` - Enabled.

### Source

:link: [includes/deprecated.php:384](/includes/deprecated.php#L384)

<details>
<summary>Show Code</summary>

```php
return apply_filters_deprecated( 'amp_bento_enabled', [ false ], 'AMP 2.5.0', 'Remove bento support', 'Bento support has been removed.' );
```

</details>
