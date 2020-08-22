## Class `AMP_Imgur_Embed_Handler`

Class AMP_Imgur_Embed_Handler

### Methods
* `register_embed`

<details>

```php
public register_embed()
```

Register embed.


</details>
* `unregister_embed`

<details>

```php
public unregister_embed()
```

Unregister embed.


</details>
* `oembed`

<details>

```php
public oembed( $matches, $attr, $url )
```

Oembed.


</details>
* `render`

<details>

```php
public render( $args )
```

Render embed.


</details>
* `filter_embed_oembed_html`

<details>

```php
public filter_embed_oembed_html( $return, $url, $attr )
```

Filter oEmbed HTML for Imgur to prepare it for AMP.


</details>
* `get_imgur_id_from_url`

<details>

```php
protected get_imgur_id_from_url( $url )
```

Get Imgur ID from URL.


</details>
