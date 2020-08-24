## Function `amp_prepare_render`

> :warning: This function is deprecated: This function is not used when &#039;amp&#039; theme support is added.

```php
function amp_prepare_render();
```

Add action to do post template rendering at template_redirect action.

### Source

:link: [includes/deprecated.php:103](../../includes/deprecated.php#L103-L106)

<details>
<summary>Show Code</summary>

```php
function amp_prepare_render() {
	_deprecated_function( __FUNCTION__, '1.5' );
	add_action( 'template_redirect', 'amp_render', 11 );
}
```

</details>
