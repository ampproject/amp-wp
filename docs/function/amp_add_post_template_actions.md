## Function `amp_add_post_template_actions`

> :warning: This function is deprecated: This function is not used when &#039;amp&#039; theme support is added.

```php
function amp_add_post_template_actions();
```

Add post template actions.

### Source

:link: [includes/deprecated.php:90](/includes/deprecated.php#L90-L94)

<details>
<summary>Show Code</summary>

```php
function amp_add_post_template_actions() {
	_deprecated_function( __FUNCTION__, '1.5' );
	require_once AMP__DIR__ . '/includes/amp-post-template-functions.php';
	amp_post_template_init_hooks();
}
```

</details>
