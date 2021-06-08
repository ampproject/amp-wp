## Filter `amp_query_var`

```php
apply_filters( 'amp_query_var', $query_var );
```

Filter the AMP query variable.

Warning: This filter may become deprecated.

### Arguments

* `string $query_var` - The AMP query variable.

### Source

:link: [includes/amp-helper-functions.php:579](/includes/amp-helper-functions.php#L579)

<details>
<summary>Show Code</summary>

```php
return apply_filters( 'amp_query_var', defined( 'AMP_QUERY_VAR' ) ? AMP_QUERY_VAR : QueryVar::AMP );
```

</details>
