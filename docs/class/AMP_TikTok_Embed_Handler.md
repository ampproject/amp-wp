## Class `AMP_TikTok_Embed_Handler`

Class AMP_TikTok_Embed_Handler

### Methods
<details>
<summary>`register_embed`</summary>

```php
public register_embed()
```

Registers embed.


</details>
<details>
<summary>`unregister_embed`</summary>

```php
public unregister_embed()
```

Unregisters embed.


</details>
<details>
<summary>`sanitize_raw_embeds`</summary>

```php
public sanitize_raw_embeds( Document $dom )
```

Sanitize TikTok embeds to be AMP compatible.


</details>
<details>
<summary>`is_raw_embed`</summary>

```php
protected is_raw_embed( \DOMElement $node )
```

Determine if the node has already been sanitized.


</details>
<details>
<summary>`make_embed_amp_compatible`</summary>

```php
protected make_embed_amp_compatible( \DOMElement $blockquote_node )
```

Make TikTok embed AMP compatible.


</details>
<details>
<summary>`remove_embed_script`</summary>

```php
protected remove_embed_script( \DOMElement $node )
```

Remove the TikTok embed script if it exists.


</details>
