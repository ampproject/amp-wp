## Class `AMP_Imgur_Embed_Handler`

Class AMP_Imgur_Embed_Handler

### Methods
<details>
<summary><code>register_embed</code></summary>

```php
public register_embed()
```

Register embed.


</details>
<details>
<summary><code>unregister_embed</code></summary>

```php
public unregister_embed()
```

Unregister embed.


</details>
<details>
<summary><code>oembed</code></summary>

```php
public oembed( $matches, $attr, $url )
```

Oembed.


</details>
<details>
<summary><code>render</code></summary>

```php
public render( $args )
```

Render embed.


</details>
<details>
<summary><code>filter_embed_oembed_html</code></summary>

```php
public filter_embed_oembed_html( $return, $url, $attr )
```

Filter oEmbed HTML for Imgur to prepare it for AMP.


</details>
<details>
<summary><code>get_imgur_id_from_url</code></summary>

```php
protected get_imgur_id_from_url( $url )
```

Get Imgur ID from URL.


</details>
