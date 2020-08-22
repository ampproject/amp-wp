## Class `AMP_Reader_Theme_REST_Controller`

AMP reader theme REST controller.

### Methods
* `__construct`

<details>

```php
public __construct( ReaderThemes $reader_themes )
```

Constructor.


</details>
* `register_routes`

<details>

```php
public register_routes()
```

Registers routes for the controller.


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

Retrieves all AMP plugin options specified in the endpoint schema.


</details>
