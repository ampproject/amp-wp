## Filter `amp_native_img_used`

```php
apply_filters( 'amp_native_img_used', $use_native );
```

Filters whether to use the native `img` element rather than convert to `amp-img`.

This filter is a feature flag to opt-in to discontinue using `amp-img` (and `amp-anim`) which will be deprecated in AMP in the near future. Once this lands in AMP, this filter will switch to defaulting to true instead of false.

### Arguments

* `bool $use_native` - Whether to use `img`.

### Source

:link: [includes/amp-helper-functions.php:1410](/includes/amp-helper-functions.php#L1410)

<details>
<summary>Show Code</summary>

```php
return (bool) apply_filters( 'amp_native_img_used', false );
```

</details>
