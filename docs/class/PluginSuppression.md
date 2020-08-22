## Class `AmpProject\AmpWP\PluginSuppression`

Suppress plugins from running by removing their hooks and nullifying their shortcodes, widgets, and blocks.

### Methods
* `__construct`

<details>

```php
public __construct( \AmpProject\AmpWP\PluginRegistry $plugin_registry )
```

Instantiate the plugin suppression service.


</details>
* `register`

<details>

```php
public register()
```

Register the service with the system.


</details>
* `is_reader_theme_request`

<details>

```php
public is_reader_theme_request()
```

Is reader theme request.


</details>
* `filter_default_options`

<details>

```php
public filter_default_options( $defaults )
```

Add default option.


</details>
* `maybe_suppress_plugins`

<details>

```php
public maybe_suppress_plugins()
```

Suppress plugins if on an AMP endpoint.


</details>
* `suppress_plugins`

<details>

```php
public suppress_plugins()
```

Suppress plugins.


</details>
* `sanitize_options`

<details>

```php
public sanitize_options( $options, $new_options )
```

Sanitize options.


</details>
* `get_sorted_plugin_validation_errors`

<details>

```php
private get_sorted_plugin_validation_errors( $plugin_slug )
```

Provides validation errors for a plugin specified by slug.


</details>
* `get_suppressible_plugins_with_details`

<details>

```php
public get_suppressible_plugins_with_details()
```

Provides a keyed array of active plugins with keys being slugs and values being plugin info plus validation error details.

Plugins are sorted by validation error count, in descending order.


</details>
* `prepare_suppressed_plugins_for_response`

<details>

```php
public prepare_suppressed_plugins_for_response( $suppressed_plugins )
```

Prepare suppressed plugins for response.

Augment the suppressed plugins data with additional information.


</details>
* `prepare_user_for_response`

<details>

```php
private prepare_user_for_response( $username )
```

Prepare user for response.


</details>
* `suppress_hooks`

<details>

```php
private suppress_hooks( $suppressed_plugins )
```

Suppress plugin hooks.


</details>
* `suppress_shortcodes`

<details>

```php
private suppress_shortcodes( $suppressed_plugins )
```

Suppress plugin shortcodes.


</details>
* `suppress_blocks`

<details>

```php
private suppress_blocks( $suppressed_plugins )
```

Suppress plugin blocks.


</details>
* `suppress_widgets`

<details>

```php
private suppress_widgets( $suppressed_plugins )
```

Suppress plugin widgets.


</details>
* `is_callback_plugin_suppressed`

<details>

```php
private is_callback_plugin_suppressed( $callback, $suppressed_plugins )
```

Determine whether callback is from a suppressed plugin.


</details>
