## Function `amp_get_slug`

```php
function amp_get_slug( $ignore_late_defined_slug = false );
```

Get the slug used in AMP for the query var, endpoint, and post type support.

The return value can be overridden by previously defining a AMP_QUERY_VAR constant or by adding a 'amp_query_var' filter, but *warning* this ability may be deprecated in the future. Normally the slug should be just 'amp'.

### Arguments

* `bool $ignore_late_defined_slug` - Whether to ignore the late defined slug.

### Return value

`string` - Slug used for query var, endpoint, and post type support.

### Source

:link: [includes/amp-helper-functions.php:558](/includes/amp-helper-functions.php#L558-L580)

<details>
<summary>Show Code</summary>

```php
function amp_get_slug( $ignore_late_defined_slug = false ) {

	// When a slug was defined late according to AmpSlugCustomizationWatcher, the slug will be stored in the
	// LATE_DEFINED_SLUG option by the PairedRouting service so that it can be used early. This is only needed until
	// the after_setup_theme action fires, because at that time the late-defined slug will have been established.
	if ( ! $ignore_late_defined_slug && ! did_action( AmpSlugCustomizationWatcher::LATE_DETERMINATION_ACTION ) ) {
		$slug = AMP_Options_Manager::get_option( Option::LATE_DEFINED_SLUG );
		if ( ! empty( $slug ) && is_string( $slug ) ) {
			return $slug;
		}
	}

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
