## Class `AMP_Post_Template`

Class AMP_Post_Template

### Methods
* `__construct`

	<details>

	```php
	public __construct( $post )
	```

	AMP_Post_Template constructor.


	</details>
* `set_data`

	<details>

	```php
	private set_data()
	```

	Set data.

This is called in the get method the first time it is called.


	</details>
* `get_template_dir`

	<details>

	```php
	private get_template_dir()
	```

	Get template directory for Reader mode.


	</details>
* `get`

	<details>

	```php
	public get( $property, $default = null )
	```

	Getter.


	</details>
* `get_customizer_setting`

	<details>

	```php
	public get_customizer_setting( $name, $default = null )
	```

	Get customizer setting.


	</details>
* `load`

	<details>

	```php
	public load()
	```

	Load and print the template parts for the given post.


	</details>
* `load_parts`

	<details>

	```php
	public load_parts( $templates )
	```

	Load template parts.


	</details>
* `get_template_path`

	<details>

	```php
	private get_template_path( $template )
	```

	Get template path.


	</details>
* `add_data`

	<details>

	```php
	private add_data( $data )
	```

	Add data.


	</details>
* `add_data_by_key`

	<details>

	```php
	private add_data_by_key( $key, $value )
	```

	Add data by key.


	</details>
* `build_post_data`

	<details>

	```php
	private build_post_data()
	```

	Build post data.


	</details>
* `build_post_comments_data`

	<details>

	```php
	private build_post_comments_data()
	```

	Build post comments data.


	</details>
* `build_post_content`

	<details>

	```php
	private build_post_content()
	```

	Build post content.


	</details>
* `build_post_featured_image`

	<details>

	```php
	private build_post_featured_image()
	```

	Build post featured image.


	</details>
* `build_customizer_settings`

	<details>

	```php
	private build_customizer_settings()
	```

	Build customizer settings.


	</details>
* `build_html_tag_attributes`

	<details>

	```php
	private build_html_tag_attributes()
	```

	Build HTML tag attributes.


	</details>
* `verify_and_include`

	<details>

	```php
	private verify_and_include( $file, $template_type )
	```

	Verify and include.


	</details>
* `locate_template`

	<details>

	```php
	private locate_template( $file )
	```

	Locate template.


	</details>
* `is_valid_template`

	<details>

	```php
	private is_valid_template( $template )
	```

	Is valid template.


	</details>
