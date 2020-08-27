## Function `amp_get_publisher_logo`

```php
function amp_get_publisher_logo();
```

Get the publisher logo.

The following guidelines apply to logos used for general AMP pages.
 &quot;The logo should be a rectangle, not a square. The logo should fit in a 60x600px rectangle., and either be exactly 60px high (preferred), or exactly 600px wide. For example, 450x45px would not be acceptable, even though it fits in the 600x60px rectangle.&quot;

### Return value

`string` - Publisher logo image URL. WordPress logo if no site icon or custom logo defined, and no logo provided via &#039;amp_site_icon_url&#039; filter.

### Source

:link: [includes/amp-helper-functions.php:1681](../../includes/amp-helper-functions.php#L1681-L1729)

<details>
<summary>Show Code</summary>

```php
function amp_get_publisher_logo() {
	$logo_image_url = null;

	/*
	 * This should be 60x600px rectangle. It *can* be larger than this, contrary to the current documentation.
	 * Only minimum size and ratio matters. So height should be at least 60px and width a minimum of 200px.
	 * An aspect ratio between 200/60 (10/3) and 600:60 (10/1) should be used. A square image still be used,
	 * but it is not preferred; a landscape logo should be provided if possible.
	 */
	$logo_width  = 600;
	$logo_height = 60;

	// Use the Custom Logo if set.
	$custom_logo_id = get_theme_mod( 'custom_logo' );
	if ( has_custom_logo() && $custom_logo_id ) {
		$custom_logo_img = wp_get_attachment_image_src( $custom_logo_id, [ $logo_width, $logo_height ], false );
		if ( ! empty( $custom_logo_img[0] ) ) {
			$logo_image_url = $custom_logo_img[0];
		}
	}

	// Try Site Icon if a custom logo is not set.
	$site_icon_id = get_option( 'site_icon' );
	if ( empty( $logo_image_url ) && $site_icon_id ) {
		$site_icon_src = wp_get_attachment_image_src( $site_icon_id, [ $logo_width, $logo_height ], false );
		if ( ! empty( $site_icon_src ) ) {
			$logo_image_url = $site_icon_src[0];
		}
	}

	/**
	 * Filters the publisher logo URL in the schema.org data.
	 *
	 * Previously, this only filtered the Site Icon, as that was the only possible schema.org publisher logo.
	 * But the Custom Logo is now the preferred publisher logo, if it exists and its dimensions aren't too big.
	 *
	 * @since 0.3
	 *
	 * @param string $schema_img_url URL of the publisher logo, either the Custom Logo or the Site Icon.
	 */
	$logo_image_url = apply_filters( 'amp_site_icon_url', $logo_image_url );

	// Fallback to serving the WordPress logo.
	if ( empty( $logo_image_url ) ) {
		$logo_image_url = amp_get_asset_url( 'images/amp-page-fallback-wordpress-publisher-logo.png' );
	}

	return $logo_image_url;
}
```

</details>
