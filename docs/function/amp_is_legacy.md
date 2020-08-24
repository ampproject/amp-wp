## Function `amp_is_legacy`

```php
function amp_is_legacy();
```

Determines whether the legacy AMP post templates are being used.

### Source

:link: [includes/amp-helper-functions.php:373](../../includes/amp-helper-functions.php#L373-L384)

<details>
<summary>Show Code</summary>

```php
function amp_is_legacy() {
	if ( AMP_Theme_Support::READER_MODE_SLUG !== AMP_Options_Manager::get_option( Option::THEME_SUPPORT ) ) {
		return false;
	}

	$reader_theme = AMP_Options_Manager::get_option( Option::READER_THEME );
	if ( ReaderThemes::DEFAULT_READER_THEME === $reader_theme ) {
		return true;
	}

	return ! wp_get_theme( $reader_theme )->exists();
}
```

</details>
