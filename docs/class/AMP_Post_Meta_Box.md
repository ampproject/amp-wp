## Class `AMP_Post_Meta_Box`

Post meta box class.

### Methods
* `init`

	<details>

	```php
	public init()
	```

	Initialize.


	</details>
* `sanitize_status`

	<details>

	```php
	public sanitize_status( $status )
	```

	Sanitize status.


	</details>
* `enqueue_admin_assets`

	<details>

	```php
	public enqueue_admin_assets()
	```

	Enqueue admin assets.


	</details>
* `enqueue_block_assets`

	<details>

	```php
	public enqueue_block_assets()
	```

	Enqueues block assets.


	</details>
* `render_status`

	<details>

	```php
	public render_status( $post )
	```

	Render AMP status.


	</details>
* `get_status_and_errors`

	<details>

	```php
	static public get_status_and_errors( $post )
	```

	Gets the AMP enabled status and errors.


	</details>
* `get_error_messages`

	<details>

	```php
	public get_error_messages( $errors )
	```

	Gets the AMP enabled error message(s).


	</details>
* `save_amp_status`

	<details>

	```php
	public save_amp_status( $post_id )
	```

	Save AMP Status.


	</details>
* `preview_post_link`

	<details>

	```php
	public preview_post_link( $link )
	```

	Modify post preview link.

Add the AMP query var is the amp-preview flag is set.


	</details>
* `add_rest_api_fields`

	<details>

	```php
	public add_rest_api_fields()
	```

	Add a REST API field to display whether AMP is enabled on supported post types.


	</details>
* `get_amp_enabled_rest_field`

	<details>

	```php
	public get_amp_enabled_rest_field( $post_data )
	```

	Get the value of whether AMP is enabled for a REST API request.


	</details>
* `update_amp_enabled_rest_field`

	<details>

	```php
	public update_amp_enabled_rest_field( $is_enabled, $post )
	```

	Update whether AMP is enabled for a REST API request.


	</details>
