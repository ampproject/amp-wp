## Class `AMP_TikTok_Embed_Handler`

Class AMP_TikTok_Embed_Handler

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

	Sanitize TikTok embeds to be AMP compatible.


	</details>
* `is_raw_embed`

	<details>

	```php
	protected is_raw_embed( \DOMElement $node )
	```

	Determine if the node has already been sanitized.


	</details>
* `make_embed_amp_compatible`

	<details>

	```php
	protected make_embed_amp_compatible( \DOMElement $blockquote_node )
	```

	Make TikTok embed AMP compatible.


	</details>
* `remove_embed_script`

	<details>

	```php
	protected remove_embed_script( \DOMElement $node )
	```

	Remove the TikTok embed script if it exists.


	</details>
