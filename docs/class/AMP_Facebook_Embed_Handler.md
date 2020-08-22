## Class `AMP_Facebook_Embed_Handler`

Class AMP_Facebook_Embed_Handler

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

Sanitized &lt;div class=&quot;fb-video&quot; data-href=&gt; tags to &lt;amp-facebook&gt;.


</details>
<details>
<summary>`get_embed_type`</summary>

```php
private get_embed_type( \DOMElement $node )
```

Get embed type.


</details>
<details>
<summary>`create_amp_facebook_and_replace_node`</summary>

```php
private create_amp_facebook_and_replace_node( Document $dom, \DOMElement $node, $embed_type )
```

Create amp-facebook and replace node.


</details>
