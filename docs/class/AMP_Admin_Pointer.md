## Class `AMP_Admin_Pointer`

Class representing a single admin pointer.

### Methods
* `__construct`

	<details>

	```php
	public __construct( $slug, array $args )
	```

	Constructor.


	</details>
* `get_slug`

	<details>

	```php
	public get_slug()
	```

	Gets the pointer slug.


	</details>
* `is_active`

	<details>

	```php
	public is_active( $hook_suffix )
	```

	Checks whether the pointer is active.

This method executes the active callback and looks at whether the pointer has been dismissed in order to determine whether the pointer should be active or not.


	</details>
* `enqueue`

	<details>

	```php
	public enqueue()
	```

	Enqueues the script for the pointer.


	</details>
* `print_js`

	<details>

	```php
	private print_js()
	```

	Prints the script for the pointer inline.

Requires the &#039;wp-pointer&#039; script to be loaded.


	</details>
