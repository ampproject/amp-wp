## Class `AMP_Link_Sanitizer`

Class AMP_Link_Sanitizer.

Adapts links for AMP-to-AMP navigation:  - In paired AMP (Transitional and Reader modes), internal links get &#039;?amp&#039; added to them.  - Internal links on AMP pages get rel=amphtml added to them.  - Forms with internal actions get a hidden &#039;amp&#039; input added to them.  - AMP pages get meta[amp-to-amp-navigation] added to them.  - Any elements in the admin bar are excluded.
 Adapted from https://gist.github.com/westonruter/f9ee9ea717d52471bae092879e3d52b0

### Methods
* `__construct`

	<details>

	```php
	public __construct( $dom, array $args = array() )
	```

	Sanitizer constructor.


	</details>
* `sanitize`

	<details>

	```php
	public sanitize()
	```

	Sanitize.


	</details>
* `add_meta_tag`

	<details>

	```php
	public add_meta_tag( $content = self::DEFAULT_META_CONTENT )
	```

	Add the amp-to-amp-navigation meta tag.


	</details>
* `process_links`

	<details>

	```php
	public process_links()
	```

	Process links by adding adding AMP query var to links in paired mode and adding rel=amphtml.


	</details>
* `process_element`

	<details>

	```php
	private process_element( \DOMElement $element, $attribute_name )
	```

	Process element.


	</details>
* `is_frontend_url`

	<details>

	```php
	public is_frontend_url( $url )
	```

	Determine whether a URL is for the frontend.


	</details>
