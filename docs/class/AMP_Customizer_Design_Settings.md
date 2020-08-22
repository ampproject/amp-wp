## Class `AMP_Customizer_Design_Settings`

Class AMP_Customizer_Design_Settings

### Methods
<details>
<summary>`is_amp_customizer_enabled`</summary>

```php
static public is_amp_customizer_enabled()
```

Returns whether the AMP design settings are enabled.


</details>
<details>
<summary>`init`</summary>

```php
static public init()
```

Init.


</details>
<details>
<summary>`init_customizer`</summary>

```php
static public init_customizer()
```

Init customizer.


</details>
<details>
<summary>`register_customizer_settings`</summary>

```php
static public register_customizer_settings( $wp_customize )
```

Register default Customizer settings for AMP.


</details>
<details>
<summary>`register_customizer_ui`</summary>

```php
static public register_customizer_ui( $wp_customize )
```

Register default Customizer sections and controls for AMP.


</details>
<details>
<summary>`render_header_bar`</summary>

```php
static public render_header_bar()
```

Render header bar template.


</details>
<details>
<summary>`render_footer`</summary>

```php
static public render_footer()
```

Render footer template.


</details>
<details>
<summary>`enqueue_customizer_preview_scripts`</summary>

```php
static public enqueue_customizer_preview_scripts()
```

Enqueue scripts for default AMP Customizer preview.


</details>
<details>
<summary>`append_settings`</summary>

```php
static public append_settings( $settings )
```

Merge default Customizer settings on top of settings for merging into AMP post template.


</details>
<details>
<summary>`get_color_scheme_names`</summary>

```php
static protected get_color_scheme_names()
```

Get color scheme names.


</details>
<details>
<summary>`get_color_schemes`</summary>

```php
static protected get_color_schemes()
```

Get color schemes.


</details>
<details>
<summary>`get_colors_for_color_scheme`</summary>

```php
static protected get_colors_for_color_scheme( $scheme )
```

Get colors for color scheme.


</details>
<details>
<summary>`sanitize_color_scheme`</summary>

```php
static public sanitize_color_scheme( $value )
```

Sanitize color scheme.


</details>
