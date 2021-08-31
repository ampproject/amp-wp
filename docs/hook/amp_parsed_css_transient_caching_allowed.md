## Filter `amp_parsed_css_transient_caching_allowed`

```php
apply_filters( 'amp_parsed_css_transient_caching_allowed', $transient_caching_allowed );
```

Filters whether parsed CSS is allowed to be cached in transients.

When this is filtered to be false, parsed CSS will not be stored in transients. This is important when there is highly-variable CSS content in order to prevent filling up the wp_options table with an endless number of entries.

### Arguments

* `bool $transient_caching_allowed` - Transient caching allowed.

### Source

:link: [includes/amp-helper-functions.php:1627](/includes/amp-helper-functions.php#L1627)

<details>
<summary>Show Code</summary>

```php
$sanitizers[ AMP_Style_Sanitizer::class ]['allow_transient_caching'] = apply_filters( 'amp_parsed_css_transient_caching_allowed', true );
```

</details>
