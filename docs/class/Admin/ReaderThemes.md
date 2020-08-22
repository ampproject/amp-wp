## Class `AmpProject\AmpWP\Admin\ReaderThemes`

Handles reader themes.

### Methods
<details>
<summary><code>get_themes</code></summary>

```php
public get_themes()
```

Retrieves all AMP plugin options specified in the endpoint schema.


</details>
<details>
<summary><code>get_reader_theme_by_slug</code></summary>

```php
public get_reader_theme_by_slug( $slug )
```

Gets a reader theme by slug.


</details>
<details>
<summary><code>get_default_reader_themes</code></summary>

```php
public get_default_reader_themes()
```

Retrieves theme data.


</details>
<details>
<summary><code>normalize_theme_data</code></summary>

```php
private normalize_theme_data( $theme )
```

Normalize the specified theme data.


</details>
<details>
<summary><code>can_install_theme</code></summary>

```php
public can_install_theme( $theme )
```

Returns whether a theme can be installed on the system.


</details>
<details>
<summary><code>get_theme_availability</code></summary>

```php
public get_theme_availability( $theme )
```

Returns reader theme availability status.


</details>
<details>
<summary><code>theme_data_exists</code></summary>

```php
public theme_data_exists( $theme_slug )
```

Determine if the data for the specified Reader theme exists.


</details>
<details>
<summary><code>using_fallback_theme</code></summary>

```php
public using_fallback_theme()
```

Determine if the AMP legacy Reader theme is being used as a fallback.


</details>
<details>
<summary><code>get_legacy_theme</code></summary>

```php
private get_legacy_theme()
```

Provides details for the legacy theme included with the plugin.


</details>
