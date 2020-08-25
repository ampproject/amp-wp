## Function `_amp_backcompat_use_v03_templates_callback`

> :warning: This function is deprecated: 

```php
function _amp_backcompat_use_v03_templates_callback( $templates );
```

Callback for getting the legacy templates directory.

### Arguments

* `string $templates` - Template directory.

### Return value

`string` - Legacy template directory.

### Source

:link: [back-compat/back-compat.php:35](../../back-compat/back-compat.php#L35-L37)

<details>
<summary>Show Code</summary>

```php
function _amp_backcompat_use_v03_templates_callback( $templates ) {
	return AMP__DIR__ . '/back-compat/templates-v0-3';
}
```

</details>
