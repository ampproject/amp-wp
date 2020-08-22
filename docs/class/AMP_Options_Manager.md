## Class `AMP_Options_Manager`

Class AMP_Options_Manager

### Methods
* `init`

<details>

```php
static public init()
```

Sets up hooks.


</details>
* `register_settings`

<details>

```php
static public register_settings()
```

Register settings.


</details>
* `maybe_flush_rewrite_rules`

<details>

```php
static public maybe_flush_rewrite_rules( $old_options, $new_options )
```

Flush rewrite rules if the supported_post_types have changed.


</details>
* `get_options`

<details>

```php
static public get_options()
```

Get plugin options.


</details>
* `get_option`

<details>

```php
static public get_option( $option, $default = false )
```

Get plugin option.


</details>
* `validate_options`

<details>

```php
static public validate_options( $new_options )
```

Validate options.


</details>
* `update_option`

<details>

```php
static public update_option( $option, $value )
```

Update plugin option.


</details>
* `update_options`

<details>

```php
static public update_options( $options )
```

Update plugin options.


</details>
* `render_php_css_parser_conflict_notice`

<details>

```php
static public render_php_css_parser_conflict_notice()
```

Render PHP-CSS-Parser conflict notice.


</details>
* `insecure_connection_notice`

<details>

```php
static public insecure_connection_notice()
```

Outputs an admin notice if the site is not served over HTTPS.


</details>
* `reader_theme_fallback_notice`

<details>

```php
static public reader_theme_fallback_notice()
```

Outputs an admin notice if the AMP Legacy Reader theme is used as a fallback.


</details>
