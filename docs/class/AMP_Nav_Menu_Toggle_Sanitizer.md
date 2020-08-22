## Class `AMP_Nav_Menu_Toggle_Sanitizer`

Class AMP_Nav_Menu_Toggle_Sanitizer

Handles state for navigation menu toggles, based on theme support.

### Methods
<details>
<summary>`__construct`</summary>

```php
public __construct( $dom, $args = array() )
```

AMP_Nav_Menu_Toggle_Sanitizer constructor.


</details>
<details>
<summary>`sanitize`</summary>

```php
public sanitize()
```

If supported per the constructor arguments, inject `amp-state` and bind dynamic classes accordingly.


</details>
<details>
<summary>`get_nav_container`</summary>

```php
protected get_nav_container()
```

Retrieves the navigation container element.


</details>
<details>
<summary>`get_menu_button`</summary>

```php
protected get_menu_button()
```

Retrieves the navigation menu button element.


</details>
