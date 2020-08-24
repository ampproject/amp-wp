## Method `AnalyticsOptionsSubmenu::register()`

```php
public function register();
```

Adds hooks.

### Source

[src/Admin/AnalyticsOptionsSubmenu.php:46](https://github.com/ampproject/amp-wp/blob/develop/src/Admin/AnalyticsOptionsSubmenu.php#L46-L48)

<details>
<summary>Show Code</summary>

```php
public function register() {
	add_action( 'admin_menu', [ $this, 'add_submenu_link' ], 99 );
}
```

</details>
