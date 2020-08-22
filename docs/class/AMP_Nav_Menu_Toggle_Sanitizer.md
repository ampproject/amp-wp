## Class `AMP_Nav_Menu_Toggle_Sanitizer`

Class AMP_Nav_Menu_Toggle_Sanitizer

Handles state for navigation menu toggles, based on theme support.

### Methods
* `__construct`

<details>

```php
public __construct( $dom, $args = array() )
```

AMP_Nav_Menu_Toggle_Sanitizer constructor.


</details>
* `sanitize`

<details>

```php
public sanitize()
```

If supported per the constructor arguments, inject `amp-state` and bind dynamic classes accordingly.


</details>
* `get_nav_container`

<details>

```php
protected get_nav_container()
```

Retrieves the navigation container element.


</details>
* `get_menu_button`

<details>

```php
protected get_menu_button()
```

Retrieves the navigation menu button element.


</details>
