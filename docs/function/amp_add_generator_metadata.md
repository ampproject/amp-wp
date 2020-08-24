## Function `amp_add_generator_metadata`

```php
function amp_add_generator_metadata();
```

Add generator metadata.

### Source

:link: [includes/amp-helper-functions.php:958](../../includes/amp-helper-functions.php#L958-L970)

<details>
<summary>Show Code</summary>

```php
function amp_add_generator_metadata() {
	$content = sprintf( 'AMP Plugin v%s', AMP__VERSION );

	$mode     = AMP_Options_Manager::get_option( Option::THEME_SUPPORT );
	$content .= sprintf( '; mode=%s', $mode );

	$reader_theme = AMP_Options_Manager::get_option( Option::READER_THEME );
	if ( AMP_Theme_Support::READER_MODE_SLUG === $mode ) {
		$content .= sprintf( '; theme=%s', $reader_theme );
	}

	printf( '<meta name="generator" content="%s">', esc_attr( $content ) );
}
```

</details>
