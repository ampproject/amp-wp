## Class `AmpProject\AmpWP\OptionsRESTController`

OptionsRESTController class.

### Methods
<details>
<summary><code>get_registration_action</code></summary>

```php
static public get_registration_action()
```

Get the action to use for registering the service.


</details>
<details>
<summary><code>__construct</code></summary>

```php
public __construct( ReaderThemes $reader_themes, \AmpProject\AmpWP\PluginSuppression $plugin_suppression )
```

Constructor.


</details>
<details>
<summary><code>register</code></summary>

```php
public register()
```

Registers all routes for the controller.


</details>
<details>
<summary><code>get_items_permissions_check</code></summary>

```php
public get_items_permissions_check( $request )
```

Checks whether the current user has permission to manage options.


</details>
<details>
<summary><code>get_items</code></summary>

```php
public get_items( $request )
```

Retrieves all AMP plugin options.


</details>
<details>
<summary><code>get_nested_supportable_templates</code></summary>

```php
private get_nested_supportable_templates( $supportable_templates, $parent_template_id = null )
```

Provides a hierarchical array of supportable templates.


</details>
<details>
<summary><code>update_items</code></summary>

```php
public update_items( $request )
```

Updates AMP plugin options.


</details>
<details>
<summary><code>get_item_schema</code></summary>

```php
public get_item_schema()
```

Retrieves the schema for plugin options provided by the endpoint.


</details>
