## Class `AMP_Facebook_Embed_Handler`

Class AMP_Facebook_Embed_Handler

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
* `sanitize_raw_embeds`

	<details>

	```php
	public sanitize_raw_embeds( Document $dom )
	```

	Sanitized &lt;div class=&quot;fb-video&quot; data-href=&gt; tags to &lt;amp-facebook&gt;.


	</details>
* `get_embed_type`

	<details>

	```php
	private get_embed_type( \DOMElement $node )
	```

	Get embed type.


	</details>
* `create_amp_facebook_and_replace_node`

	<details>

	```php
	private create_amp_facebook_and_replace_node( Document $dom, \DOMElement $node, $embed_type )
	```

	Create amp-facebook and replace node.


	</details>
