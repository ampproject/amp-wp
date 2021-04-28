## Filter `amp_is_enabled`

```php
apply_filters( 'amp_is_enabled' );
```

Filters whether AMP is enabled on the current site.

Useful if the plugin is network activated and you want to turn it off on select sites.

### Source

:link: [includes/amp-helper-functions.php:56](/includes/amp-helper-functions.php#L56)

<details>
<summary>Show Code</summary>

```php
if ( false === apply_filters( 'amp_is_enabled', true ) ) {
```

</details>
