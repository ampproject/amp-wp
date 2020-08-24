## Method `AnalyticsOptionsSubmenu::get_registration_action()`

```php
static public function get_registration_action();
```

Get the action to use for registering the service.

### Source

[src/Admin/AnalyticsOptionsSubmenu.php:39](https://github.com/ampproject/amp-wp/blob/develop/src/Admin/AnalyticsOptionsSubmenu.php#L39-L41)

<details>
<summary>Show Code</summary>

```php
public static function get_registration_action() {
	return 'admin_init';
}
```

</details>
