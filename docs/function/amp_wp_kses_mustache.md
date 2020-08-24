## Function `amp_wp_kses_mustache`

```php
function amp_wp_kses_mustache( $markup );
```

Filters content and keeps only allowable HTML elements by amp-mustache.

### Arguments

* `string $markup` - Markup to sanitize.

### Source

:link: [includes/amp-helper-functions.php:1841](https://github.com/ampproject/amp-wp/blob/develop/includes/amp-helper-functions.php#L1841-L1844)

<details>
<summary>Show Code</summary>

```php
function amp_wp_kses_mustache( $markup ) {
	$amp_mustache_allowed_html_tags = [ 'strong', 'b', 'em', 'i', 'u', 's', 'small', 'mark', 'del', 'ins', 'sup', 'sub' ];
	return wp_kses( $markup, array_fill_keys( $amp_mustache_allowed_html_tags, [] ) );
}
```

</details>
