## Class `AMP_Instagram_Embed_Handler`

Class AMP_Instagram_Embed_Handler

Much of this class is borrowed from Jetpack embeds

### Methods
* `register_embed`

<details>

```php
public register_embed()
```

Registers embed.


</details>
* `unregister_embed`

<details>

```php
public unregister_embed()
```

Unregisters embed.


</details>
* `oembed`

<details>

```php
public oembed( $matches, $attr, $url )
```

WordPress OEmbed rendering callback.


</details>
* `render`

<details>

```php
public render( $args )
```

Gets the rendered embed markup.


</details>
* `get_instagram_id_from_url`

<details>

```php
private get_instagram_id_from_url( $url )
```

Get Instagram ID from URL.


</details>
* `sanitize_raw_embeds`

<details>

```php
public sanitize_raw_embeds( Document $dom )
```

Sanitized &lt;blockquote class=&quot;instagram-media&quot;&gt; tags to &lt;amp-instagram&gt;


</details>
* `create_amp_instagram_and_replace_node`

<details>

```php
private create_amp_instagram_and_replace_node( $dom, $node )
```

Make final modifications to DOMNode


</details>
* `sanitize_embed_script`

<details>

```php
private sanitize_embed_script( $node )
```

Removes Instagram&#039;s embed &lt;script&gt; tag.


</details>
