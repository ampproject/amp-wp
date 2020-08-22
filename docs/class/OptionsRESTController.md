## Class `AmpProject\AmpWP\OptionsRESTController`

OptionsRESTController class.

### Methods
<details>
<summary>`get_registration_action`</summary>

```php
static public get_registration_action()
```

Get the action to use for registering the service.


</details>
<details>
<summary>`__construct`</summary>

```php
public __construct( ReaderThemes $reader_themes, \AmpProject\AmpWP\PluginSuppression $plugin_suppression )
```

Constructor.


</details>
<details>
<summary>`register`</summary>

```php
public register()
```

Registers all routes for the controller.


</details>
<details>
<summary>`get_items_permissions_check`</summary>

```php
public get_items_permissions_check( $request )
```

Checks whether the current user has permission to manage options.


</details>
<details>
<summary>`get_items`</summary>

```php
public get_items( $request )
```

Retrieves all AMP plugin options.


</details>
<details>
<summary>`get_nested_supportable_templates`</summary>

```php
private get_nested_supportable_templates( $supportable_templates, $parent_template_id = null )
```

Provides a hierarchical array of supportable templates.


</details>
<details>
<summary>`update_items`</summary>

```php
public update_items( $request )
```

Updates AMP plugin options.


</details>
<details>
<summary>`get_item_schema`</summary>

```php
public get_item_schema()
```

Retrieves the schema for plugin options provided by the endpoint.


</details>
