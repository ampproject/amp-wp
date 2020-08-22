## Class `AMP_Post_Template`

Class AMP_Post_Template

### Methods
<details>
<summary>`__construct`</summary>

```php
public __construct( $post )
```

AMP_Post_Template constructor.


</details>
<details>
<summary>`set_data`</summary>

```php
private set_data()
```

Set data.

This is called in the get method the first time it is called.


</details>
<details>
<summary>`get_template_dir`</summary>

```php
private get_template_dir()
```

Get template directory for Reader mode.


</details>
<details>
<summary>`get`</summary>

```php
public get( $property, $default = null )
```

Getter.


</details>
<details>
<summary>`get_customizer_setting`</summary>

```php
public get_customizer_setting( $name, $default = null )
```

Get customizer setting.


</details>
<details>
<summary>`load`</summary>

```php
public load()
```

Load and print the template parts for the given post.


</details>
<details>
<summary>`load_parts`</summary>

```php
public load_parts( $templates )
```

Load template parts.


</details>
<details>
<summary>`get_template_path`</summary>

```php
private get_template_path( $template )
```

Get template path.


</details>
<details>
<summary>`add_data`</summary>

```php
private add_data( $data )
```

Add data.


</details>
<details>
<summary>`add_data_by_key`</summary>

```php
private add_data_by_key( $key, $value )
```

Add data by key.


</details>
<details>
<summary>`build_post_data`</summary>

```php
private build_post_data()
```

Build post data.


</details>
<details>
<summary>`build_post_comments_data`</summary>

```php
private build_post_comments_data()
```

Build post comments data.


</details>
<details>
<summary>`build_post_content`</summary>

```php
private build_post_content()
```

Build post content.


</details>
<details>
<summary>`build_post_featured_image`</summary>

```php
private build_post_featured_image()
```

Build post featured image.


</details>
<details>
<summary>`build_customizer_settings`</summary>

```php
private build_customizer_settings()
```

Build customizer settings.


</details>
<details>
<summary>`build_html_tag_attributes`</summary>

```php
private build_html_tag_attributes()
```

Build HTML tag attributes.


</details>
<details>
<summary>`verify_and_include`</summary>

```php
private verify_and_include( $file, $template_type )
```

Verify and include.


</details>
<details>
<summary>`locate_template`</summary>

```php
private locate_template( $file )
```

Locate template.


</details>
<details>
<summary>`is_valid_template`</summary>

```php
private is_valid_template( $template )
```

Is valid template.


</details>
