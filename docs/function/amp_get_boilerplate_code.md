## Function `amp_get_boilerplate_code`

```php
function amp_get_boilerplate_code();
```

Get AMP boilerplate code.

### Return value

`string` - Boilerplate code.

### Source

:link: [includes/amp-helper-functions.php:929](../../includes/amp-helper-functions.php#L929-L932)

<details>
<summary>Show Code</summary>

```php
function amp_get_boilerplate_code() {
	$stylesheets = amp_get_boilerplate_stylesheets();
	return sprintf( '<style amp-boilerplate>%s</style><noscript><style amp-boilerplate>%s</style></noscript>', $stylesheets[0], $stylesheets[1] );
}
```

</details>
