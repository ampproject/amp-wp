## Filter `amp_content_sanitizers`

```php
apply_filters( 'amp_content_sanitizers', $handlers, $post );
```

Filters the content sanitizers.

### Arguments

* `array $handlers` - Handlers.
* `\WP_Post $post` - Post. Deprecated.

### Source

:link: [includes/amp-helper-functions.php:1602](/includes/amp-helper-functions.php#L1602)

<details>
<summary>Show Code</summary>

```php
$sanitizers = apply_filters( 'amp_content_sanitizers', $sanitizers, $post );
```

</details>
