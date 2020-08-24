## Function `amp_register_default_styles`

```php
function amp_register_default_styles( \WP_Styles $styles );
```

Register default styles.

### Arguments

* `\WP_Styles $styles` - Styles.

### Source

[includes/amp-helper-functions.php:1045](https://github.com/ampproject/amp-wp/blob/develop/includes/amp-helper-functions.php#L1045-L1053)

<details>
<summary>Show Code</summary>

```php
function amp_register_default_styles( WP_Styles $styles ) {
	$styles->add(
		'amp-icons',
		amp_get_asset_url( 'css/amp-icons.css' ),
		[ 'dashicons' ],
		AMP__VERSION
	);
	$styles->add_data( 'amp-icons', 'rtl', 'replace' );
}
```

</details>
