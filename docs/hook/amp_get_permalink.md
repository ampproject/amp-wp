## Filter `amp_get_permalink`

```php
apply_filters( 'amp_get_permalink', $amp_url, $post_id );
```

Filters AMP permalink.

### Arguments

* `false $amp_url` - AMP URL.
* `int $post_id` - Post ID.

### Source

:link: [includes/amp-helper-functions.php:747](/includes/amp-helper-functions.php#L747)

<details>
<summary>Show Code</summary>

```php
return apply_filters( 'amp_get_permalink', $amp_url, $post_id );
```

</details>
