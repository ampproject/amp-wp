## Filter `amp_enable_ssr`

```php
apply_filters( 'amp_enable_ssr', $enable_ssr );
```

Filter whether the AMP Optimizer should use server-side rendering or not.

### Arguments

* `bool $enable_ssr` - Whether the AMP Optimizer should use server-side rendering or not.

### Source

:link: [includes/class-amp-theme-support.php:2107](/includes/class-amp-theme-support.php#L2107)

<details>
<summary>Show Code</summary>

```php
$enable_ssr = apply_filters( 'amp_enable_ssr', $enable_ssr );
```

</details>
