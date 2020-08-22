## Class `AMP_Customizer_Design_Settings`

Class AMP_Customizer_Design_Settings

### Methods
<details>
<summary><code>is_amp_customizer_enabled</code></summary>

```php
static public is_amp_customizer_enabled()
```

Returns whether the AMP design settings are enabled.


</details>
<details>
<summary><code>init</code></summary>

```php
static public init()
```

Init.


</details>
<details>
<summary><code>init_customizer</code></summary>

```php
static public init_customizer()
```

Init customizer.


</details>
<details>
<summary><code>register_customizer_settings</code></summary>

```php
static public register_customizer_settings( $wp_customize )
```

Register default Customizer settings for AMP.


</details>
<details>
<summary><code>register_customizer_ui</code></summary>

```php
static public register_customizer_ui( $wp_customize )
```

Register default Customizer sections and controls for AMP.


</details>
<details>
<summary><code>render_header_bar</code></summary>

```php
static public render_header_bar()
```

Render header bar template.


</details>
<details>
<summary><code>render_footer</code></summary>

```php
static public render_footer()
```

Render footer template.


</details>
<details>
<summary><code>enqueue_customizer_preview_scripts</code></summary>

```php
static public enqueue_customizer_preview_scripts()
```

Enqueue scripts for default AMP Customizer preview.


</details>
<details>
<summary><code>append_settings</code></summary>

```php
static public append_settings( $settings )
```

Merge default Customizer settings on top of settings for merging into AMP post template.


</details>
<details>
<summary><code>get_color_scheme_names</code></summary>

```php
static protected get_color_scheme_names()
```

Get color scheme names.


</details>
<details>
<summary><code>get_color_schemes</code></summary>

```php
static protected get_color_schemes()
```

Get color schemes.


</details>
<details>
<summary><code>get_colors_for_color_scheme</code></summary>

```php
static protected get_colors_for_color_scheme( $scheme )
```

Get colors for color scheme.


</details>
<details>
<summary><code>sanitize_color_scheme</code></summary>

```php
static public sanitize_color_scheme( $value )
```

Sanitize color scheme.


</details>
