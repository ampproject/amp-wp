## Function `amp_is_canonical`

```php
function amp_is_canonical();
```

Whether this is in 'canonical mode'.

Themes can register support for this with `add_theme_support( AMP_Theme_Support::SLUG )`:
 ```php
add_theme_support( AMP_Theme_Support::SLUG );
```

This will serve templates in AMP-first, allowing you to use AMP components in your theme templates.
If you want to make available in transitional mode, where templates are served in AMP or non-AMP documents, do:

```php
add_theme_support( AMP_Theme_Support::SLUG, array(
    'paired' => true,
) );
```

Transitional mode is also implied if you define a `template_dir`:

```php
add_theme_support( AMP_Theme_Support::SLUG, array(
    'template_dir' => 'amp',
) );
```

If you want to have AMP-specific templates in addition to serving AMP-first, do:

```php
add_theme_support( AMP_Theme_Support::SLUG, array(
    'paired'       => false,
    'template_dir' => 'amp',
) );
```

### Return value

`boolean` - Whether this is in AMP &#039;canonical&#039; mode, that is whether it is AMP-first and there is not a separate (paired) AMP URL.

### Source

:link: [includes/amp-helper-functions.php:294](/includes/amp-helper-functions.php#L294-L296)

<details>
<summary>Show Code</summary>

```php
function amp_is_canonical() {
	return AMP_Theme_Support::STANDARD_MODE_SLUG === AMP_Options_Manager::get_option( Option::THEME_SUPPORT );
}
```

</details>
