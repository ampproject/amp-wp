## Function `amp_correct_query_when_is_front_page`

```php
function amp_correct_query_when_is_front_page( \WP_Query $query );
```

Fix up WP_Query for front page when amp query var is present.

Normally the front page would not get served if a query var is present other than preview, page, paged, and cpage.

### Arguments

* `\WP_Query $query` - Query.

### Source

:link: [includes/amp-helper-functions.php:289](../../includes/amp-helper-functions.php#L289-L316)

<details>
<summary>Show Code</summary>

```php
function amp_correct_query_when_is_front_page( WP_Query $query ) {
	$is_front_page_query = (
		$query->is_main_query()
		&&
		$query->is_home()
		&&
		// Is AMP endpoint.
		false !== $query->get( amp_get_slug(), false )
		&&
		// Is query not yet fixed uo up to be front page.
		! $query->is_front_page()
		&&
		// Is showing pages on front.
		'page' === get_option( 'show_on_front' )
		&&
		// Has page on front set.
		get_option( 'page_on_front' )
		&&
		// See line in WP_Query::parse_query() at <https://github.com/WordPress/wordpress-develop/blob/0baa8ae/src/wp-includes/class-wp-query.php#L961>.
		0 === count( array_diff( array_keys( wp_parse_args( $query->query ) ), [ amp_get_slug(), 'preview', 'page', 'paged', 'cpage' ] ) )
	);
	if ( $is_front_page_query ) {
		$query->is_home     = false;
		$query->is_page     = true;
		$query->is_singular = true;
		$query->set( 'page_id', get_option( 'page_on_front' ) );
	}
}
```

</details>
