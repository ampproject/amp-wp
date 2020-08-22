## Class `AmpProject\AmpWP\Admin\ReaderThemes`

Handles reader themes.

### Methods
* `get_themes`

	<details>

	```php
	public get_themes()
	```

	Retrieves all AMP plugin options specified in the endpoint schema.


	</details>
* `get_reader_theme_by_slug`

	<details>

	```php
	public get_reader_theme_by_slug( $slug )
	```

	Gets a reader theme by slug.


	</details>
* `get_default_reader_themes`

	<details>

	```php
	public get_default_reader_themes()
	```

	Retrieves theme data.


	</details>
* `normalize_theme_data`

	<details>

	```php
	private normalize_theme_data( $theme )
	```

	Normalize the specified theme data.


	</details>
* `can_install_theme`

	<details>

	```php
	public can_install_theme( $theme )
	```

	Returns whether a theme can be installed on the system.


	</details>
* `get_theme_availability`

	<details>

	```php
	public get_theme_availability( $theme )
	```

	Returns reader theme availability status.


	</details>
* `theme_data_exists`

	<details>

	```php
	public theme_data_exists( $theme_slug )
	```

	Determine if the data for the specified Reader theme exists.


	</details>
* `using_fallback_theme`

	<details>

	```php
	public using_fallback_theme()
	```

	Determine if the AMP legacy Reader theme is being used as a fallback.


	</details>
* `get_legacy_theme`

	<details>

	```php
	private get_legacy_theme()
	```

	Provides details for the legacy theme included with the plugin.


	</details>
