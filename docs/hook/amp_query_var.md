## Hook `amp_query_var`


Filter the AMP query variable.

Warning: This filter may become deprecated.

### Source

:link: [includes/amp-helper-functions.php:609](../../includes/amp-helper-functions.php#L609)

<details>
<summary>Show Code</summary>

```php
return apply_filters( 'amp_query_var', defined( 'AMP_QUERY_VAR' ) ? AMP_QUERY_VAR : QueryVar::AMP );
```

</details>
