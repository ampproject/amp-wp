## Filter `amp_enable_ssr`

```php
apply_filters( 'amp_enable_ssr', $enable_ssr );
```

Filter whether the AMP Optimizer should use server-side rendering or not.

### Arguments

* `bool $enable_ssr` - Whether the AMP Optimizer should use server-side rendering or not.

### Source

:link: [src/Optimizer/AmpWPConfiguration.php:52](/src/Optimizer/AmpWPConfiguration.php#L52)

<details>
<summary>Show Code</summary>

```php
$enable_ssr = apply_filters( 'amp_enable_ssr', true );
```

</details>
