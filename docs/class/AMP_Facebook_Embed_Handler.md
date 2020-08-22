## Class `AMP_Facebook_Embed_Handler`

Class AMP_Facebook_Embed_Handler

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

Sanitized &lt;div class=&quot;fb-video&quot; data-href=&gt; tags to &lt;amp-facebook&gt;.


</details>
<details>
<summary><code>get_embed_type</code></summary>

```php
private get_embed_type( \DOMElement $node )
```

Get embed type.


</details>
<details>
<summary><code>create_amp_facebook_and_replace_node</code></summary>

```php
private create_amp_facebook_and_replace_node( Document $dom, \DOMElement $node, $embed_type )
```

Create amp-facebook and replace node.


</details>
