## Class `AmpProject\AmpWP\Instrumentation\EventWithDuration`

A server-timing event with a duration.

### Methods
* `__construct`

	<details>

	```php
	public __construct( $name, $description = null, $properties = array(), $duration = 0.0 )
	```

	Event constructor.


	</details>
* `set_duration`

	<details>

	```php
	public set_duration( $duration )
	```

	Set the event duration.


	</details>
* `get_duration`

	<details>

	```php
	public get_duration()
	```

	Get the event duration.


	</details>
* `get_header_string`

	<details>

	```php
	public get_header_string()
	```

	Get the server timing header string.


	</details>
