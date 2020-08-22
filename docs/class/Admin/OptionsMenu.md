## Class `AmpProject\AmpWP\Admin\OptionsMenu`

OptionsMenu class.

### Methods
<details>
<summary><code>is_needed</code></summary>

```php
static public is_needed()
```

Check whether the conditional object is currently needed.


</details>
<details>
<summary><code>__construct</code></summary>

```php
public __construct( \AmpProject\AmpWP\Admin\GoogleFonts $google_fonts, \AmpProject\AmpWP\Admin\ReaderThemes $reader_themes, \AmpProject\AmpWP\Admin\RESTPreloader $rest_preloader )
```

OptionsMenu constructor.


</details>
<details>
<summary><code>register</code></summary>

```php
public register()
```

Adds hooks.


</details>
<details>
<summary><code>add_plugin_action_links</code></summary>

```php
public add_plugin_action_links( $links )
```

Add plugin action links.


</details>
<details>
<summary><code>add_menu_items</code></summary>

```php
public add_menu_items()
```

Add menu.


</details>
<details>
<summary><code>screen_handle</code></summary>

```php
public screen_handle()
```

Provides the settings screen handle.


</details>
<details>
<summary><code>enqueue_assets</code></summary>

```php
public enqueue_assets( $hook_suffix )
```

Enqueues settings page assets.


</details>
<details>
<summary><code>render_screen</code></summary>

```php
public render_screen()
```

Display Settings.


</details>
<details>
<summary><code>add_preload_rest_paths</code></summary>

```php
protected add_preload_rest_paths()
```

Adds REST paths to preload.


</details>
