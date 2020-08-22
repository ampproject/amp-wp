## Class `AMP_Post_Template`

Class AMP_Post_Template

### Methods
<details>
<summary><code>__construct</code></summary>

```php
public __construct( $post )
```

AMP_Post_Template constructor.


</details>
<details>
<summary><code>set_data</code></summary>

```php
private set_data()
```

Set data.

This is called in the get method the first time it is called.


</details>
<details>
<summary><code>get_template_dir</code></summary>

```php
private get_template_dir()
```

Get template directory for Reader mode.


</details>
<details>
<summary><code>get</code></summary>

```php
public get( $property, $default = null )
```

Getter.


</details>
<details>
<summary><code>get_customizer_setting</code></summary>

```php
public get_customizer_setting( $name, $default = null )
```

Get customizer setting.


</details>
<details>
<summary><code>load</code></summary>

```php
public load()
```

Load and print the template parts for the given post.


</details>
<details>
<summary><code>load_parts</code></summary>

```php
public load_parts( $templates )
```

Load template parts.


</details>
<details>
<summary><code>get_template_path</code></summary>

```php
private get_template_path( $template )
```

Get template path.


</details>
<details>
<summary><code>add_data</code></summary>

```php
private add_data( $data )
```

Add data.


</details>
<details>
<summary><code>add_data_by_key</code></summary>

```php
private add_data_by_key( $key, $value )
```

Add data by key.


</details>
<details>
<summary><code>build_post_data</code></summary>

```php
private build_post_data()
```

Build post data.


</details>
<details>
<summary><code>build_post_comments_data</code></summary>

```php
private build_post_comments_data()
```

Build post comments data.


</details>
<details>
<summary><code>build_post_content</code></summary>

```php
private build_post_content()
```

Build post content.


</details>
<details>
<summary><code>build_post_featured_image</code></summary>

```php
private build_post_featured_image()
```

Build post featured image.


</details>
<details>
<summary><code>build_customizer_settings</code></summary>

```php
private build_customizer_settings()
```

Build customizer settings.


</details>
<details>
<summary><code>build_html_tag_attributes</code></summary>

```php
private build_html_tag_attributes()
```

Build HTML tag attributes.


</details>
<details>
<summary><code>verify_and_include</code></summary>

```php
private verify_and_include( $file, $template_type )
```

Verify and include.


</details>
<details>
<summary><code>locate_template</code></summary>

```php
private locate_template( $file )
```

Locate template.


</details>
<details>
<summary><code>is_valid_template</code></summary>

```php
private is_valid_template( $template )
```

Is valid template.


</details>
