## Class `AmpProject\AmpWP\Admin\DevToolsUserAccess`

Class DevToolsUserAccess

### Methods
* `register`

	<details>

	```php
	public register()
	```

	Runs on instantiation.


	</details>
* `is_user_enabled`

	<details>

	```php
	public is_user_enabled( $user = null )
	```

	Determine whether developer tools are enabled for the a user and whether they can access them.


	</details>
* `get_user_enabled`

	<details>

	```php
	public get_user_enabled( $user )
	```

	Get user enabled (regardless of whether they have the required capability).


	</details>
* `set_user_enabled`

	<details>

	```php
	public set_user_enabled( $user, $enabled )
	```

	Set user enabled.


	</details>
* `register_rest_field`

	<details>

	```php
	public register_rest_field()
	```

	Register REST field.


	</details>
* `print_personal_options`

	<details>

	```php
	public print_personal_options( $profile_user )
	```

	Add the developer tools checkbox to the user edit screen.


	</details>
* `update_user_setting`

	<details>

	```php
	public update_user_setting( $user_id )
	```

	Update the user setting from the edit user screen).


	</details>
* `rest_get_dev_tools_enabled`

	<details>

	```php
	public rest_get_dev_tools_enabled( $user )
	```

	Provides the user&#039;s dev tools enabled setting.


	</details>
* `rest_update_dev_tools_enabled`

	<details>

	```php
	public rest_update_dev_tools_enabled( $new_value, WP_User $user )
	```

	Updates a user&#039;s dev tools enabled setting.


	</details>
