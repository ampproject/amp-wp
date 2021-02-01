## Filter `amp_mobile_version_switcher_styles_used`

```php
apply_filters( 'amp_mobile_version_switcher_styles_used', $used );
```

Filters whether the default mobile version switcher styles are printed.

### Arguments

* `bool $used` - Whether the styles are printed.

### Source

:link: [src/MobileRedirection.php:420](/src/MobileRedirection.php#L420)

<details>
<summary>Show Code</summary>

```php
if ( ! apply_filters( 'amp_mobile_version_switcher_styles_used', true ) ) {
```

</details>
