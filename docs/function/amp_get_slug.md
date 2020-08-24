## Function `amp_get_slug`

```php
function amp_get_slug();
```

Get the slug used in AMP for the query var, endpoint, and post type support.

The return value can be overridden by previously defining a AMP_QUERY_VAR constant or by adding a &#039;amp_query_var&#039; filter, but *warning* this ability may be deprecated in the future. Normally the slug should be just &#039;amp&#039;.

### Source

[includes/amp-helper-functions.php:596](https://github.com/ampproject/amp-wp/blob/develop/includes/amp-helper-functions.php#L596-L607)

<details>
<summary>Show Code</summary>

```php
function amp_get_slug() {
	/**
	 * Filter the AMP query variable.
	 *
	 * Warning: This filter may become deprecated.
	 *
	 * @since 0.3.2
	 *
	 * @param string $query_var The AMP query variable.
	 */
	return apply_filters( 'amp_query_var', defined( 'AMP_QUERY_VAR' ) ? AMP_QUERY_VAR : QueryVar::AMP );
}
```

</details>
