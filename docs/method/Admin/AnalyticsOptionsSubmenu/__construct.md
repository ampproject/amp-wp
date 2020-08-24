## Method `AnalyticsOptionsSubmenu::__construct()`

```php
public function __construct( \AmpProject\AmpWP\Admin\OptionsMenu $options_menu );
```

Class constructor.

### Arguments

* `\AmpProject\AmpWP\Admin\OptionsMenu $options_menu` - An instance of the class handling the parent menu.

### Source

:link: [src/Admin/AnalyticsOptionsSubmenu.php:30](https://github.com/ampproject/amp-wp/blob/develop/src/Admin/AnalyticsOptionsSubmenu.php#L30-L32)

<details>
<summary>Show Code</summary>

```php
public function __construct( OptionsMenu $options_menu ) {
	$this->parent_menu_slug = $options_menu->get_menu_slug();
}
```

</details>
