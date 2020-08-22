## Class `AmpProject\AmpWP\Admin\DevToolsUserAccess`

Class DevToolsUserAccess

### Methods
<details>
<summary>`register`</summary>

```php
public register()
```

Runs on instantiation.


</details>
<details>
<summary>`is_user_enabled`</summary>

```php
public is_user_enabled( $user = null )
```

Determine whether developer tools are enabled for the a user and whether they can access them.


</details>
<details>
<summary>`get_user_enabled`</summary>

```php
public get_user_enabled( $user )
```

Get user enabled (regardless of whether they have the required capability).


</details>
<details>
<summary>`set_user_enabled`</summary>

```php
public set_user_enabled( $user, $enabled )
```

Set user enabled.


</details>
<details>
<summary>`register_rest_field`</summary>

```php
public register_rest_field()
```

Register REST field.


</details>
<details>
<summary>`print_personal_options`</summary>

```php
public print_personal_options( $profile_user )
```

Add the developer tools checkbox to the user edit screen.


</details>
<details>
<summary>`update_user_setting`</summary>

```php
public update_user_setting( $user_id )
```

Update the user setting from the edit user screen).


</details>
<details>
<summary>`rest_get_dev_tools_enabled`</summary>

```php
public rest_get_dev_tools_enabled( $user )
```

Provides the user&#039;s dev tools enabled setting.


</details>
<details>
<summary>`rest_update_dev_tools_enabled`</summary>

```php
public rest_update_dev_tools_enabled( $new_value, WP_User $user )
```

Updates a user&#039;s dev tools enabled setting.


</details>
