## Class `AMP_Scribd_Embed_Handler`

Class AMP_Scribd_Embed_Handler

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
* `filter_embed_oembed_html`

	<details>

	```php
	public filter_embed_oembed_html( $cache, $url )
	```

	Filter oEmbed HTML for Scribd to be AMP compatible.


	</details>
* `sanitize_iframe`

	<details>

	```php
	private sanitize_iframe( $html )
	```

	Retrieves iframe element from HTML string and amends or appends the correct sandbox permissions.


	</details>
