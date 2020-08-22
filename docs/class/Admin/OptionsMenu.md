## Class `AmpProject\AmpWP\Admin\OptionsMenu`

OptionsMenu class.

### Methods
* `is_needed`

<details>

```php
static public is_needed()
```

Check whether the conditional object is currently needed.


</details>
* `__construct`

<details>

```php
public __construct( \AmpProject\AmpWP\Admin\GoogleFonts $google_fonts, \AmpProject\AmpWP\Admin\ReaderThemes $reader_themes, \AmpProject\AmpWP\Admin\RESTPreloader $rest_preloader )
```

OptionsMenu constructor.


</details>
* `register`

<details>

```php
public register()
```

Adds hooks.


</details>
* `add_plugin_action_links`

<details>

```php
public add_plugin_action_links( $links )
```

Add plugin action links.


</details>
* `add_menu_items`

<details>

```php
public add_menu_items()
```

Add menu.


</details>
* `screen_handle`

<details>

```php
public screen_handle()
```

Provides the settings screen handle.


</details>
* `enqueue_assets`

<details>

```php
public enqueue_assets( $hook_suffix )
```

Enqueues settings page assets.


</details>
* `render_screen`

<details>

```php
public render_screen()
```

Display Settings.


</details>
* `add_preload_rest_paths`

<details>

```php
protected add_preload_rest_paths()
```

Adds REST paths to preload.


</details>
