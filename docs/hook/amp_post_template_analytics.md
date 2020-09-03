## Filter `amp_post_template_analytics`


Add amp-analytics tags.

This filter allows you to easily insert any amp-analytics tags without needing much heavy lifting. This filter should be used to alter entries for legacy AMP templates.

### Source

:link: [includes/admin/functions.php:159](../../includes/admin/functions.php#L159)

<details>
<summary>Show Code</summary>

```php
$analytics = apply_filters( 'amp_post_template_analytics', $analytics, get_queried_object() );
```

</details>
