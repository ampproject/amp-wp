## Class `AMP_Instagram_Embed_Handler`

Class AMP_Instagram_Embed_Handler

Much of this class is borrowed from Jetpack embeds

### Methods
<details>
<summary><code>register_embed</code></summary>

```php
public register_embed()
```

Registers embed.


</details>
<details>
<summary><code>unregister_embed</code></summary>

```php
public unregister_embed()
```

Unregisters embed.


</details>
<details>
<summary><code>oembed</code></summary>

```php
public oembed( $matches, $attr, $url )
```

WordPress OEmbed rendering callback.


</details>
<details>
<summary><code>render</code></summary>

```php
public render( $args )
```

Gets the rendered embed markup.


</details>
<details>
<summary><code>get_instagram_id_from_url</code></summary>

```php
private get_instagram_id_from_url( $url )
```

Get Instagram ID from URL.


</details>
<details>
<summary><code>sanitize_raw_embeds</code></summary>

```php
public sanitize_raw_embeds( Document $dom )
```

Sanitized &lt;blockquote class=&quot;instagram-media&quot;&gt; tags to &lt;amp-instagram&gt;


</details>
<details>
<summary><code>create_amp_instagram_and_replace_node</code></summary>

```php
private create_amp_instagram_and_replace_node( $dom, $node )
```

Make final modifications to DOMNode


</details>
<details>
<summary><code>sanitize_embed_script</code></summary>

```php
private sanitize_embed_script( $node )
```

Removes Instagram&#039;s embed &lt;script&gt; tag.


</details>
