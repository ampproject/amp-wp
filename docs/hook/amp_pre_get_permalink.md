## Filter `amp_pre_get_permalink`


Filters the AMP permalink to short-circuit normal generation.

Returning a non-false value in this filter will cause the `get_permalink()` to get called and the `amp_get_permalink` filter to not apply.

### Source

:link: [includes/amp-helper-functions.php:687](../../includes/amp-helper-functions.php#L687)

<details>
<summary>Show Code</summary>

```php
$pre_url = apply_filters( 'amp_pre_get_permalink', false, $post_id );
```

</details>
