## Hook `amp_query_var`

### Source

:link: [includes/amp-helper-functions.php:606](../../includes/amp-helper-functions.php#L606)

<details>
<summary>Show Code</summary>

```php
return apply_filters( 'amp_query_var', defined( 'AMP_QUERY_VAR' ) ? AMP_QUERY_VAR : QueryVar::AMP );
```

</details>
