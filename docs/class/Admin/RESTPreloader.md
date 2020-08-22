## Class `AmpProject\AmpWP\Admin\RESTPreloader`

Preloads REST responses for client-side applications to prevent having to call fetch on page load.

### Methods
* `add_preloaded_path`

	<details>

	```php
	public add_preloaded_path( $path )
	```

	Adds a REST path to be preloaded.


	</details>
* `preload_data`

	<details>

	```php
	public preload_data()
	```

	Preloads data using apiFetch preloading middleware.


	</details>
