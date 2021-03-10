## Method `AMP_Base_Embed_Handler::get_scripts()`

```php
public function get_scripts();
```

Get mapping of AMP component names to AMP script URLs.

This is normally no longer needed because the validating sanitizer will automatically detect the need for them via the spec.

### Return value

`array` - Scripts.

### Source

:link: [includes/embeds/class-amp-base-embed-handler.php:81](/includes/embeds/class-amp-base-embed-handler.php#L81-L83)

<details>
<summary>Show Code</summary>

```php
public function get_scripts() {
	return [];
}
```

</details>
