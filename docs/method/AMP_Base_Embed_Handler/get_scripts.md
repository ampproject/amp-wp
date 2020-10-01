## Method `AMP_Base_Embed_Handler::get_scripts()`

```php
public function get_scripts();
```

Get mapping of AMP component names to AMP script URLs.

This is normally no longer needed because the validating sanitizer will automatically detect the need for them via the spec.

### Return value

`array` - Scripts.

### Source

:link: [includes/embeds/class-amp-base-embed-handler.php:79](/includes/embeds/class-amp-base-embed-handler.php#L79-L81)

<details>
<summary>Show Code</summary>

```php
public function get_scripts() {
	return [];
}
```

</details>
