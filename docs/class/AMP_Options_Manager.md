## Class `AMP_Options_Manager`

Class AMP_Options_Manager

### Methods
<details>
<summary><code>init</code></summary>

```php
static public init()
```

Sets up hooks.


</details>
<details>
<summary><code>register_settings</code></summary>

```php
static public register_settings()
```

Register settings.


</details>
<details>
<summary><code>maybe_flush_rewrite_rules</code></summary>

```php
static public maybe_flush_rewrite_rules( $old_options, $new_options )
```

Flush rewrite rules if the supported_post_types have changed.


</details>
<details>
<summary><code>get_options</code></summary>

```php
static public get_options()
```

Get plugin options.


</details>
<details>
<summary><code>get_option</code></summary>

```php
static public get_option( $option, $default = false )
```

Get plugin option.


</details>
<details>
<summary><code>validate_options</code></summary>

```php
static public validate_options( $new_options )
```

Validate options.


</details>
<details>
<summary><code>update_option</code></summary>

```php
static public update_option( $option, $value )
```

Update plugin option.


</details>
<details>
<summary><code>update_options</code></summary>

```php
static public update_options( $options )
```

Update plugin options.


</details>
<details>
<summary><code>render_php_css_parser_conflict_notice</code></summary>

```php
static public render_php_css_parser_conflict_notice()
```

Render PHP-CSS-Parser conflict notice.


</details>
<details>
<summary><code>insecure_connection_notice</code></summary>

```php
static public insecure_connection_notice()
```

Outputs an admin notice if the site is not served over HTTPS.


</details>
<details>
<summary><code>reader_theme_fallback_notice</code></summary>

```php
static public reader_theme_fallback_notice()
```

Outputs an admin notice if the AMP Legacy Reader theme is used as a fallback.


</details>
