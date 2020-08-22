## Class `AmpProject\AmpWP\Admin\ReaderThemes`

Handles reader themes.

### Methods
<details>
<summary>`get_themes`</summary>

```php
public get_themes()
```

Retrieves all AMP plugin options specified in the endpoint schema.


</details>
<details>
<summary>`get_reader_theme_by_slug`</summary>

```php
public get_reader_theme_by_slug( $slug )
```

Gets a reader theme by slug.


</details>
<details>
<summary>`get_default_reader_themes`</summary>

```php
public get_default_reader_themes()
```

Retrieves theme data.


</details>
<details>
<summary>`normalize_theme_data`</summary>

```php
private normalize_theme_data( $theme )
```

Normalize the specified theme data.


</details>
<details>
<summary>`can_install_theme`</summary>

```php
public can_install_theme( $theme )
```

Returns whether a theme can be installed on the system.


</details>
<details>
<summary>`get_theme_availability`</summary>

```php
public get_theme_availability( $theme )
```

Returns reader theme availability status.


</details>
<details>
<summary>`theme_data_exists`</summary>

```php
public theme_data_exists( $theme_slug )
```

Determine if the data for the specified Reader theme exists.


</details>
<details>
<summary>`using_fallback_theme`</summary>

```php
public using_fallback_theme()
```

Determine if the AMP legacy Reader theme is being used as a fallback.


</details>
<details>
<summary>`get_legacy_theme`</summary>

```php
private get_legacy_theme()
```

Provides details for the legacy theme included with the plugin.


</details>
