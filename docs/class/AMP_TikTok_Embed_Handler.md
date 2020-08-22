## Class `AMP_TikTok_Embed_Handler`

Class AMP_TikTok_Embed_Handler

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
<summary><code>sanitize_raw_embeds</code></summary>

```php
public sanitize_raw_embeds( Document $dom )
```

Sanitize TikTok embeds to be AMP compatible.


</details>
<details>
<summary><code>is_raw_embed</code></summary>

```php
protected is_raw_embed( \DOMElement $node )
```

Determine if the node has already been sanitized.


</details>
<details>
<summary><code>make_embed_amp_compatible</code></summary>

```php
protected make_embed_amp_compatible( \DOMElement $blockquote_node )
```

Make TikTok embed AMP compatible.


</details>
<details>
<summary><code>remove_embed_script</code></summary>

```php
protected remove_embed_script( \DOMElement $node )
```

Remove the TikTok embed script if it exists.


</details>
