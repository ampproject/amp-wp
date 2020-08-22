## Class `AMP_Customizer_Design_Settings`

Class AMP_Customizer_Design_Settings

### Methods
* `is_amp_customizer_enabled`

<details>

```php
static public is_amp_customizer_enabled()
```

Returns whether the AMP design settings are enabled.


</details>
* `init`

<details>

```php
static public init()
```

Init.


</details>
* `init_customizer`

<details>

```php
static public init_customizer()
```

Init customizer.


</details>
* `register_customizer_settings`

<details>

```php
static public register_customizer_settings( $wp_customize )
```

Register default Customizer settings for AMP.


</details>
* `register_customizer_ui`

<details>

```php
static public register_customizer_ui( $wp_customize )
```

Register default Customizer sections and controls for AMP.


</details>
* `render_header_bar`

<details>

```php
static public render_header_bar()
```

Render header bar template.


</details>
* `render_footer`

<details>

```php
static public render_footer()
```

Render footer template.


</details>
* `enqueue_customizer_preview_scripts`

<details>

```php
static public enqueue_customizer_preview_scripts()
```

Enqueue scripts for default AMP Customizer preview.


</details>
* `append_settings`

<details>

```php
static public append_settings( $settings )
```

Merge default Customizer settings on top of settings for merging into AMP post template.


</details>
* `get_color_scheme_names`

<details>

```php
static protected get_color_scheme_names()
```

Get color scheme names.


</details>
* `get_color_schemes`

<details>

```php
static protected get_color_schemes()
```

Get color schemes.


</details>
* `get_colors_for_color_scheme`

<details>

```php
static protected get_colors_for_color_scheme( $scheme )
```

Get colors for color scheme.


</details>
* `sanitize_color_scheme`

<details>

```php
static public sanitize_color_scheme( $value )
```

Sanitize color scheme.


</details>
