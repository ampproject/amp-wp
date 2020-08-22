## Class `AMP_Post_Type_Support`

Class AMP_Post_Type_Support.

### Methods
<details>
<summary>`get_builtin_supported_post_types`</summary>

```php
static public get_builtin_supported_post_types()
```

Get post types that plugin supports out of the box (which cannot be disabled).


</details>
<details>
<summary>`get_eligible_post_types`</summary>

```php
static public get_eligible_post_types()
```

Get post types that are eligible for AMP support.


</details>
<details>
<summary>`get_post_types_for_rest_api`</summary>

```php
static public get_post_types_for_rest_api()
```

Get post types that can be shown in the REST API and supports AMP.


</details>
<details>
<summary>`get_supported_post_types`</summary>

```php
static public get_supported_post_types()
```

Get supported post types.


</details>
<details>
<summary>`add_post_type_support`</summary>

```php
static public add_post_type_support()
```

Declare support for post types.

This function should only be invoked through the &#039;after_setup_theme&#039; action to allow plugins/theme to overwrite the post types support.


</details>
<details>
<summary>`get_support_errors`</summary>

```php
static public get_support_errors( $post )
```

Return error codes for why a given post does not have AMP support.


</details>
