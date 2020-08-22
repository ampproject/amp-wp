## Class `AMP_Imgur_Embed_Handler`

Class AMP_Imgur_Embed_Handler

### Methods
<details>
<summary>`register_embed`</summary>

```php
public register_embed()
```

Register embed.


</details>
<details>
<summary>`unregister_embed`</summary>

```php
public unregister_embed()
```

Unregister embed.


</details>
<details>
<summary>`oembed`</summary>

```php
public oembed( $matches, $attr, $url )
```

Oembed.


</details>
<details>
<summary>`render`</summary>

```php
public render( $args )
```

Render embed.


</details>
<details>
<summary>`filter_embed_oembed_html`</summary>

```php
public filter_embed_oembed_html( $return, $url, $attr )
```

Filter oEmbed HTML for Imgur to prepare it for AMP.


</details>
<details>
<summary>`get_imgur_id_from_url`</summary>

```php
protected get_imgur_id_from_url( $url )
```

Get Imgur ID from URL.


</details>
