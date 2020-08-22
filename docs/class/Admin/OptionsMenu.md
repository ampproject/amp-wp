## Class `AmpProject\AmpWP\Admin\OptionsMenu`

OptionsMenu class.

### Methods
<details>
<summary>`is_needed`</summary>

```php
static public is_needed()
```

Check whether the conditional object is currently needed.


</details>
<details>
<summary>`__construct`</summary>

```php
public __construct( \AmpProject\AmpWP\Admin\GoogleFonts $google_fonts, \AmpProject\AmpWP\Admin\ReaderThemes $reader_themes, \AmpProject\AmpWP\Admin\RESTPreloader $rest_preloader )
```

OptionsMenu constructor.


</details>
<details>
<summary>`register`</summary>

```php
public register()
```

Adds hooks.


</details>
<details>
<summary>`add_plugin_action_links`</summary>

```php
public add_plugin_action_links( $links )
```

Add plugin action links.


</details>
<details>
<summary>`add_menu_items`</summary>

```php
public add_menu_items()
```

Add menu.


</details>
<details>
<summary>`screen_handle`</summary>

```php
public screen_handle()
```

Provides the settings screen handle.


</details>
<details>
<summary>`enqueue_assets`</summary>

```php
public enqueue_assets( $hook_suffix )
```

Enqueues settings page assets.


</details>
<details>
<summary>`render_screen`</summary>

```php
public render_screen()
```

Display Settings.


</details>
<details>
<summary>`add_preload_rest_paths`</summary>

```php
protected add_preload_rest_paths()
```

Adds REST paths to preload.


</details>
