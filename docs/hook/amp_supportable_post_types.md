## Hook `amp_supportable_post_types`


Filters the list of post types which may be supported for AMP.

By default the list includes those which are public.

### Source

:link: [includes/class-amp-post-type-support.php:63](../../includes/class-amp-post-type-support.php#L63)

<details>
<summary>Show Code</summary>

```php
return array_values( (array) apply_filters( 'amp_supportable_post_types', $post_types ) );
```

</details>
