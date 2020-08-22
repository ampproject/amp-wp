## Class `AMP_Iframe_Sanitizer`

Class AMP_Iframe_Sanitizer

Converts &lt;iframe&gt; tags to &lt;amp-iframe&gt;

### Methods
* `get_selector_conversion_mapping`

	<details>

	```php
	public get_selector_conversion_mapping()
	```

	Get mapping of HTML selectors to the AMP component selectors which they may be converted into.


	</details>
* `sanitize`

	<details>

	```php
	public sanitize()
	```

	Sanitize the &lt;iframe&gt; elements from the HTML contained in this instance&#039;s Dom\Document.


	</details>
* `normalize_attributes`

	<details>

	```php
	private normalize_attributes( $attributes )
	```

	Normalize HTML attributes for &lt;amp-iframe&gt; elements.


	</details>
* `get_origin_from_url`

	<details>

	```php
	private get_origin_from_url( $url )
	```

	Obtain the origin part of a given URL (scheme, host, port).


	</details>
* `build_placeholder`

	<details>

	```php
	private build_placeholder()
	```

	Builds a DOMElement to use as a placeholder for an &lt;iframe&gt;.

Important: The element returned must not be block-level (e.g. div) as the PHP DOM parser will move it out from inside any containing paragraph. So this is why a span is used.


	</details>
* `sanitize_boolean_digit`

	<details>

	```php
	private sanitize_boolean_digit( $value )
	```

	Sanitizes a boolean character (or string) into a &#039;0&#039; or &#039;1&#039; character.


	</details>
