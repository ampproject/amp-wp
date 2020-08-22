## Class `AMP_Options_Manager`

Class AMP_Options_Manager

### Methods
<details>
<summary>`init`</summary>

```php
static public init()
```

Sets up hooks.


</details>
<details>
<summary>`register_settings`</summary>

```php
static public register_settings()
```

Register settings.


</details>
<details>
<summary>`maybe_flush_rewrite_rules`</summary>

```php
static public maybe_flush_rewrite_rules( $old_options, $new_options )
```

Flush rewrite rules if the supported_post_types have changed.


</details>
<details>
<summary>`get_options`</summary>

```php
static public get_options()
```

Get plugin options.


</details>
<details>
<summary>`get_option`</summary>

```php
static public get_option( $option, $default = false )
```

Get plugin option.


</details>
<details>
<summary>`validate_options`</summary>

```php
static public validate_options( $new_options )
```

Validate options.


</details>
<details>
<summary>`update_option`</summary>

```php
static public update_option( $option, $value )
```

Update plugin option.


</details>
<details>
<summary>`update_options`</summary>

```php
static public update_options( $options )
```

Update plugin options.


</details>
<details>
<summary>`render_php_css_parser_conflict_notice`</summary>

```php
static public render_php_css_parser_conflict_notice()
```

Render PHP-CSS-Parser conflict notice.


</details>
<details>
<summary>`insecure_connection_notice`</summary>

```php
static public insecure_connection_notice()
```

Outputs an admin notice if the site is not served over HTTPS.


</details>
<details>
<summary>`reader_theme_fallback_notice`</summary>

```php
static public reader_theme_fallback_notice()
```

Outputs an admin notice if the AMP Legacy Reader theme is used as a fallback.


</details>
