## Method `AnalyticsOptionsSubmenu::add_submenu_link()`

```php
public function add_submenu_link();
```

Adds a submenu link to the AMP options submenu.

### Source

[src/Admin/AnalyticsOptionsSubmenu.php:53](https://github.com/ampproject/amp-wp/blob/develop/src/Admin/AnalyticsOptionsSubmenu.php#L53-L63)

<details>
<summary>Show Code</summary>

```php
public function add_submenu_link() {
	add_submenu_page(
		$this->parent_menu_slug,
		__( 'Analytics', 'amp' ),
		__( 'Analytics', 'amp' ),
		'manage_options',
		$this->parent_menu_slug . '#analytics-options',
		'__return_false',
		1
	);
}
```

</details>
