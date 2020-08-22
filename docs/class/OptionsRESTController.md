## Class `AmpProject\AmpWP\OptionsRESTController`

OptionsRESTController class.

### Methods
* `get_registration_action`

<details>

```php
static public get_registration_action()
```

Get the action to use for registering the service.


</details>
* `__construct`

<details>

```php
public __construct( ReaderThemes $reader_themes, \AmpProject\AmpWP\PluginSuppression $plugin_suppression )
```

Constructor.


</details>
* `register`

<details>

```php
public register()
```

Registers all routes for the controller.


</details>
* `get_items_permissions_check`

<details>

```php
public get_items_permissions_check( $request )
```

Checks whether the current user has permission to manage options.


</details>
* `get_items`

<details>

```php
public get_items( $request )
```

Retrieves all AMP plugin options.


</details>
* `get_nested_supportable_templates`

<details>

```php
private get_nested_supportable_templates( $supportable_templates, $parent_template_id = null )
```

Provides a hierarchical array of supportable templates.


</details>
* `update_items`

<details>

```php
public update_items( $request )
```

Updates AMP plugin options.


</details>
* `get_item_schema`

<details>

```php
public get_item_schema()
```

Retrieves the schema for plugin options provided by the endpoint.


</details>
