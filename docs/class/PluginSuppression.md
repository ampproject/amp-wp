## Class `AmpProject\AmpWP\PluginSuppression`

Suppress plugins from running by removing their hooks and nullifying their shortcodes, widgets, and blocks.

### Methods
<details>
<summary>`__construct`</summary>

```php
public __construct( \AmpProject\AmpWP\PluginRegistry $plugin_registry )
```

Instantiate the plugin suppression service.


</details>
<details>
<summary>`register`</summary>

```php
public register()
```

Register the service with the system.


</details>
<details>
<summary>`is_reader_theme_request`</summary>

```php
public is_reader_theme_request()
```

Is reader theme request.


</details>
<details>
<summary>`filter_default_options`</summary>

```php
public filter_default_options( $defaults )
```

Add default option.


</details>
<details>
<summary>`maybe_suppress_plugins`</summary>

```php
public maybe_suppress_plugins()
```

Suppress plugins if on an AMP endpoint.


</details>
<details>
<summary>`suppress_plugins`</summary>

```php
public suppress_plugins()
```

Suppress plugins.


</details>
<details>
<summary>`sanitize_options`</summary>

```php
public sanitize_options( $options, $new_options )
```

Sanitize options.


</details>
<details>
<summary>`get_sorted_plugin_validation_errors`</summary>

```php
private get_sorted_plugin_validation_errors( $plugin_slug )
```

Provides validation errors for a plugin specified by slug.


</details>
<details>
<summary>`get_suppressible_plugins_with_details`</summary>

```php
public get_suppressible_plugins_with_details()
```

Provides a keyed array of active plugins with keys being slugs and values being plugin info plus validation error details.

Plugins are sorted by validation error count, in descending order.


</details>
<details>
<summary>`prepare_suppressed_plugins_for_response`</summary>

```php
public prepare_suppressed_plugins_for_response( $suppressed_plugins )
```

Prepare suppressed plugins for response.

Augment the suppressed plugins data with additional information.


</details>
<details>
<summary>`prepare_user_for_response`</summary>

```php
private prepare_user_for_response( $username )
```

Prepare user for response.


</details>
<details>
<summary>`suppress_hooks`</summary>

```php
private suppress_hooks( $suppressed_plugins )
```

Suppress plugin hooks.


</details>
<details>
<summary>`suppress_shortcodes`</summary>

```php
private suppress_shortcodes( $suppressed_plugins )
```

Suppress plugin shortcodes.


</details>
<details>
<summary>`suppress_blocks`</summary>

```php
private suppress_blocks( $suppressed_plugins )
```

Suppress plugin blocks.


</details>
<details>
<summary>`suppress_widgets`</summary>

```php
private suppress_widgets( $suppressed_plugins )
```

Suppress plugin widgets.


</details>
<details>
<summary>`is_callback_plugin_suppressed`</summary>

```php
private is_callback_plugin_suppressed( $callback, $suppressed_plugins )
```

Determine whether callback is from a suppressed plugin.


</details>
