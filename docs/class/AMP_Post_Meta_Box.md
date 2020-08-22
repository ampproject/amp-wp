## Class `AMP_Post_Meta_Box`

Post meta box class.

### Methods
<details>
<summary><code>init</code></summary>

```php
public init()
```

Initialize.


</details>
<details>
<summary><code>sanitize_status</code></summary>

```php
public sanitize_status( $status )
```

Sanitize status.


</details>
<details>
<summary><code>enqueue_admin_assets</code></summary>

```php
public enqueue_admin_assets()
```

Enqueue admin assets.


</details>
<details>
<summary><code>enqueue_block_assets</code></summary>

```php
public enqueue_block_assets()
```

Enqueues block assets.


</details>
<details>
<summary><code>render_status</code></summary>

```php
public render_status( $post )
```

Render AMP status.


</details>
<details>
<summary><code>get_status_and_errors</code></summary>

```php
static public get_status_and_errors( $post )
```

Gets the AMP enabled status and errors.


</details>
<details>
<summary><code>get_error_messages</code></summary>

```php
public get_error_messages( $errors )
```

Gets the AMP enabled error message(s).


</details>
<details>
<summary><code>save_amp_status</code></summary>

```php
public save_amp_status( $post_id )
```

Save AMP Status.


</details>
<details>
<summary><code>preview_post_link</code></summary>

```php
public preview_post_link( $link )
```

Modify post preview link.

Add the AMP query var is the amp-preview flag is set.


</details>
<details>
<summary><code>add_rest_api_fields</code></summary>

```php
public add_rest_api_fields()
```

Add a REST API field to display whether AMP is enabled on supported post types.


</details>
<details>
<summary><code>get_amp_enabled_rest_field</code></summary>

```php
public get_amp_enabled_rest_field( $post_data )
```

Get the value of whether AMP is enabled for a REST API request.


</details>
<details>
<summary><code>update_amp_enabled_rest_field</code></summary>

```php
public update_amp_enabled_rest_field( $is_enabled, $post )
```

Update whether AMP is enabled for a REST API request.


</details>
