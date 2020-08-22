## Class `AMP_Admin_Pointer`

Class representing a single admin pointer.

### Methods
<details>
<summary>`__construct`</summary>

```php
public __construct( $slug, array $args )
```

Constructor.


</details>
<details>
<summary>`get_slug`</summary>

```php
public get_slug()
```

Gets the pointer slug.


</details>
<details>
<summary>`is_active`</summary>

```php
public is_active( $hook_suffix )
```

Checks whether the pointer is active.

This method executes the active callback and looks at whether the pointer has been dismissed in order to determine whether the pointer should be active or not.


</details>
<details>
<summary>`enqueue`</summary>

```php
public enqueue()
```

Enqueues the script for the pointer.


</details>
<details>
<summary>`print_js`</summary>

```php
private print_js()
```

Prints the script for the pointer inline.

Requires the &#039;wp-pointer&#039; script to be loaded.


</details>
