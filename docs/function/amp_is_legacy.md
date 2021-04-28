## Function `amp_is_legacy`

```php
function amp_is_legacy();
```

Determines whether the legacy AMP post templates are being used.

### Return value

`bool`

### Source

:link: [includes/amp-helper-functions.php:304](/includes/amp-helper-functions.php#L304-L315)

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
