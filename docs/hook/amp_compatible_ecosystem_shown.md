## Filter `amp_compatible_ecosystem_shown`

```php
apply_filters( 'amp_compatible_ecosystem_shown', $shown, $type );
```

Filters whether to show AMP compatible ecosystem in the admin.

### Arguments

* `bool $shown` - Whether to show AMP-compatible themes and plugins in the admin.
* `string $type` - The type of ecosystem component being shown. May be either &#039;themes&#039; or &#039;plugins&#039;.

### Source

:link: [src/Admin/AmpThemes.php:71](/src/Admin/AmpThemes.php#L71)

<details>
<summary>Show Code</summary>

```php
return is_admin() && apply_filters( 'amp_compatible_ecosystem_shown', true, 'themes' );
```

</details>
