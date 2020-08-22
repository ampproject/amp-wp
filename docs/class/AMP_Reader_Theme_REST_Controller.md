## Class `AMP_Reader_Theme_REST_Controller`

AMP reader theme REST controller.

### Methods
<details>
<summary><code>__construct</code></summary>

```php
public __construct( ReaderThemes $reader_themes )
```

Constructor.


</details>
<details>
<summary><code>register_routes</code></summary>

```php
public register_routes()
```

Registers routes for the controller.


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

Retrieves all AMP plugin options specified in the endpoint schema.


</details>
